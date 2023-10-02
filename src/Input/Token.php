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

use Ahc\Cli\Input\Option;

use function \array_map;
use function \is_null;

/**
 * Token.
 * Represents a token in the input.
 *
 * @author  shlomo hassid <shlomohassid@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Token
{

    public const TOKEN_LITERAL      = '--';
    public const TOKEN_OPTION_LONG  = Option::SIGN_LONG;
    public const TOKEN_OPTION_SHORT = Option::SIGN_SHORT;
    public const TOKEN_OPTION_EQ    = '=';
    public const TOKEN_VARIADIC_O   = '[';
    public const TOKEN_VARIADIC_C   = ']';

    public const TYPE_LITERAL       = 'literal';
    public const TYPE_SHORT         = 'short';
    public const TYPE_LONG          = 'long';
    public const TYPE_CONSTANT      = 'constant';
    public const TYPE_VARIADIC      = 'variadic';

    private string $type;

    private string $value;

    /** @var Token[] */
    public array $nested = [];

    /**
     * @param string $type the type of the token
     * @param string $value the value of the token
     */
    public function __construct(string $type, string $value)
    {
        $this->type  = $type;
        $this->value = $value;
    }

    /**
     * Add a nested token.
     *
     * @param Token $token
     *
     * @return self
     */
    public function addNested(Token $token): self
    {
        $this->nested[] = $token;

        return $this;
    }

    /**
     * Get or Check the type of the token.
     *
     * @param string|null $type the type to check
     *
     * @return bool|string if $type is null returns the type of the token, 
     *                     otherwise returns true if the type matches
     */
    public function type(?string $type = null): bool|string
    {
        return is_null($type)
                ? $this->type
                : $this->type === $type;
    }

    /**
     * Check if the token is a literal group.
     *
     * @return bool
     */
    public function isLiteral(): bool
    {
        return $this->type(self::TYPE_LITERAL);
    }

    /**
     * Check if the token is a variadic group symbol.
     *
     * @param string|null $side the side to check
     *
     * @return bool
     */
    public function isVariadic(?string $side = null): bool
    {
        if ($side === 'open') {
            return $this->type(self::TYPE_VARIADIC) && $this->value === self::TOKEN_VARIADIC_O;
        }
        if ($side === 'close') {
            return $this->type(self::TYPE_VARIADIC) && $this->value === self::TOKEN_VARIADIC_C;
        }
        
        return $this->type(self::TYPE_VARIADIC);
    }

    /**
     * Check if the token is a constant value.
     *
     * @return bool
     */
    public function isConstant(): bool
    {
        return $this->type(self::TYPE_CONSTANT);
    }

    /**
     * Check if the token is an option (Short or Long)
     *
     * @return bool
     */
    public function isOption(): bool
    {
        return $this->type(self::TYPE_SHORT) || $this->type(self::TYPE_LONG);
    }

    /**
     * Get the values of the nested tokens.
     *
     * @return array
     */
    public function nestedValues(): array
    {
        return array_map(fn($token) => $token->value, $this->nested);
    }

    /**
     * Get the value of the token.
     * If has nested tokens, returns an array of the nested values.
     *
     * @return string|array
     */
    public function value(): string|array
    {
        return $this->type === self::TYPE_VARIADIC
            ? $this->nestedValues()
            : $this->value;
    }
    
    /**
     * Get the string representation of the token.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->type}:{$this->value}";
    }
}