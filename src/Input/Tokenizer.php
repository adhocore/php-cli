<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Input;

use Ahc\Cli\Input\Token;
use \Iterator;

use function \explode;
use function \array_map;
use function \array_push;
use function \ltrim;
use function \rtrim;
use function \preg_match;
use function \preg_quote;
use function \sprintf;
use function \str_split;
use function \strlen;

/**
 * Tokenizer.
 * A tokenizer is a class that takes an array of arguments and
 * converts them into pre-defined tokens.
 *
 * @author  Shlomo Hassid <shlomohassid@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Tokenizer implements Iterator
{

    /** @var Token[]  */
    private array $tokens = [];

    private int $index = 0;

    /**
     * @param array $args The arguments to tokenize.
     */
    public function __construct(array $args) 
    {
        $variadic = false;
        $literal  = false;

        // Process args:
        foreach ($args as $arg) {

            // Tokenize this arg:
            $tokens = [];
            if (
                // Not a literal token:
                !$literal ||
                // Or a literal token with variadic close, Which is a special case:
                ($variadic && ($arg[0] ?? '') === Token::TOKEN_VARIADIC_C)
            ) {

                $tokens = $this->tokenize($arg);
                $literal = false;

            } else {
                // Literal token treat all as constant:
                $tokens[] = new Token(Token::TYPE_CONSTANT, $arg);
            }

            // Process detected token/s:
            foreach ($tokens as $token) {

                switch ($token->type()) {

                    case Token::TYPE_VARIADIC:
                        $variadic = $token->isVariadic("open");
                        if ($variadic) {
                            $this->tokens[] = $token;
                        }
                        break;

                    case Token::TYPE_LITERAL:
                        // Add all remaining tokens as nested of a literal token:
                        $literal = true;
                        break;

                    case Token::TYPE_SHORT:
                    case Token::TYPE_LONG:
                    case Token::TYPE_CONSTANT:
                        if ($variadic) {
                            $this->tokens[count($this->tokens) - 1]->addNested($token);
                        } else {
                            $this->tokens[] = $token;
                        }
                        break;
                }
            }
        }
    }

    /**
     * Get the detected tokens.
     *
     * @return Token[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * Detect constants: strings, numbers, negative numbers.
     * e.g. string, 123, -123, 1.23, -1.23
     *
     * @param string $arg
     *
     * @return bool
     */
    private function isConstant(string $arg): bool
    {
        // Early return for non-option args its a constant:
        if (!$this->isOption($arg)) {

            return true;
        }
        // If its a single hyphen, maybe its a negative number:
        if (
            ($arg[0] ?? '') === '-'
            &&
            ($arg === (string)(int)$arg || $arg === (string)(float)$arg)
        ) {

            return true;
        }

        return false;
    }

    /**
     * Detect variadic symbol.
     * e.g. [ or ]
     *
     * @param string $arg
     *
     * @return bool
     */
    private function isVariadicSymbol(string $arg): bool
    {
        return  ($arg[0] ?? '') === Token::TOKEN_VARIADIC_O
                ||
                ($arg[0] ?? '') === Token::TOKEN_VARIADIC_C;
    }

    /**
     * Detect options: short, long
     * e.g. -l, --long
     *
     * @param string $arg
     *
     * @return bool
     */
    private function isOption(string $arg): bool
    {
        return strlen($arg) > 1 && ($arg[0] ?? '')
               ===
               Token::TOKEN_OPTION_SHORT;
    }

    /**
     * Detect literal token.
     * e.g. --
     *
     * @param string $arg
     *
     * @return bool
     */
    private function isLiteralSymbol(string $arg): bool
    {
        return $arg === Token::TOKEN_LITERAL;
    }

    /**
     * Detect short option.
     * e.g. -a
     *
     * @param string $arg
     *
     * @return bool
     */
    private function isShortOption(string $arg): bool
    {
        return (bool)preg_match(
            '/^'.preg_quote(Token::TOKEN_OPTION_SHORT).'\w$/',
            $arg
        );
    }

    /**
     * Detect packed short options.
     * e.g. -abc
     *
     * @param string $arg
     *
     * @return bool
     */
    private function isPackedOptions(string $arg): bool
    {
        return (bool)preg_match(
            '/^'.preg_quote(Token::TOKEN_OPTION_SHORT).'\w{2,}$/',
            $arg
        );
    }

    /**
     * Detect short options with value.
     * e.g. -a=value
     *
     * @param string $arg
     *
     * @return bool
     */
    private function isShortEqOptions(string $arg): bool
    {
        return (bool)preg_match(
            sprintf(
                '/^%s\w%s/',
                preg_quote(Token::TOKEN_OPTION_SHORT),
                preg_quote(Token::TOKEN_OPTION_EQ)
            ),
            $arg
        );
    }

    /**
     * Detect long option.
     * e.g. --long
     *
     * @param string $arg
     *
     * @return bool
     */
    private function isLongOption(string $arg): bool
    {
        return (bool)preg_match(
            '/^'.preg_quote(Token::TOKEN_OPTION_LONG).'\w[\w\-]{0,}\w$/',
            $arg
        );
    }

    /**
     * Detect long option with value.
     * e.g. --long=value
     *
     * @param string $arg
     *
     * @return bool
     */
    private function isLongEqOption(string $arg): bool
    {
        return (bool)preg_match(
            sprintf(
                '/^%s([^\s\=]+)%s/',
                preg_quote(Token::TOKEN_OPTION_LONG),
                preg_quote(Token::TOKEN_OPTION_EQ)
            ),
            $arg
        );
    }

    /**
     * Tokenize an argument.
     * A single argument can be a combination of multiple tokens.
     *
     * @param string $arg
     *
     * @return Token[]
     */
    private function tokenize(string $arg): array
    {
        $tokens = [];

        if ($this->isVariadicSymbol($arg[0] ?? '')) {
            $tokens[] = new Token(
                Token::TYPE_VARIADIC,
                strlen($arg) === 1 ? $arg : Token::TOKEN_VARIADIC_O
            );
            if (strlen($arg) > 1) {
                $arg = ltrim($arg, Token::TOKEN_VARIADIC_O);
            } else {
                return $tokens;
            }
        }

        if ($this->isConstant($arg)) {
            if ($this->isVariadicSymbol($arg[strlen($arg) - 1] ?? '')) {
                $tokens[] = new Token(Token::TYPE_CONSTANT, rtrim($arg, Token::TOKEN_VARIADIC_C));
                $tokens[] = new Token(Token::TYPE_VARIADIC, Token::TOKEN_VARIADIC_C);
            } else {
                $tokens[] = new Token(Token::TYPE_CONSTANT, $arg);
            }

            return $tokens;
        }

        if ($this->isLiteralSymbol($arg)) {
            $tokens[] = new Token(Token::TYPE_LITERAL, $arg);

            return $tokens;
        }

        if ($this->isShortOption($arg)) {
            $tokens[] = new Token(Token::TYPE_SHORT, $arg);

            return $tokens;
        }

        if ($this->isPackedOptions($arg)) {
            $t = array_map(function ($arg) {
                return new Token(Token::TYPE_SHORT, Token::TOKEN_OPTION_SHORT . $arg);
            }, str_split(ltrim($arg, Token::TOKEN_OPTION_SHORT)));

            array_push($tokens, ...$t);

            return $tokens;
        }

        if ($this->isShortEqOptions($arg)) {
            $parts = explode(Token::TOKEN_OPTION_EQ, $arg, 2);
            $tokens[] = new Token(Token::TYPE_SHORT, $parts[0]);
            if (!empty($parts[1])) {
                $t = $this->tokenize($parts[1]);
                array_push($tokens, ...$t);
            }

            return $tokens;
        }

        if ($this->isLongOption($arg)) {
            $tokens[] = new Token(Token::TYPE_LONG, $arg);

            return $tokens;
        }

        if ($this->isLongEqOption($arg)) {
            $parts = explode(Token::TOKEN_OPTION_EQ, $arg, 2);
            $tokens[] = new Token(Token::TYPE_LONG, $parts[0]);

            if (!empty($parts[1])) {
                $t = $this->tokenize($parts[1]);
                array_push($tokens, ...$t);
            }

            return $tokens;
        }

        // Unclassified, treat as constant:
        return [new Token(Token::TYPE_CONSTANT, $arg)];
    }

    /**
     * Get the current token - Iterator interface.
     *
     * @return Token
     */
    public function current(): Token
    {
        return $this->tokens[$this->index];
    }

    /**
     * Get the next token without moving the pointer.
     *
     * @param int $offset
     *
     * @return Token|null
     */
    public function offset(int $offset): ?Token
    {
        if (isset($this->tokens[$this->index + $offset])) {
            return $this->tokens[$this->index + $offset];
        }

        return null;
    }

    /**
     * Move the pointer to the next token - Iterator interface.
     *
     * @return void
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * Get the current token index - Iterator interface.
     *
     * @return int
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * Check if the current token is valid - Iterator interface.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->tokens[$this->index]);
    }

    /**
     * Rewind the pointer to the first token - Iterator interface.
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * Get the current token if valid.
     *
     * @return Token|null
     */
    public function validCurrent(): ?Token
    {
        if ($this->valid()) {
            return $this->current();
        }

        return null;
    }

    /**
     * toString magic method for debugging.
     */
    public function __toString(): string
    {
        $str = PHP_EOL;
        foreach ($this->tokens as $token) {
            $str .= " - ".$token . PHP_EOL;
            if (!empty($token->nested)) {
                foreach ($token->nested as $nested) {
                    $str .= "     - " . $nested . PHP_EOL;
                }
            }
        }
        
        return $str;
    }
}