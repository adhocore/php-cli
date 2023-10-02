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

use Ahc\Cli\Helper\Normalizer;
use Ahc\Cli\Input\Tokenizer;
use Ahc\Cli\Exception\InvalidParameterException;
use Ahc\Cli\Exception\InvalidArgumentException;
use Ahc\Cli\Exception\RuntimeException;

use function \array_diff_key;
use function \array_filter;
use function \array_key_exists;
use function \array_shift;
use function \in_array;
use function \is_null;
use function \sprintf;

/**
 * Argv parser for the cli.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
abstract class Parser
{

    protected Normalizer $_normalizer;

    protected Tokenizer $_tokenizer;

    private array $_options = [];

    private array $_arguments = [];

    /** @var array Parsed values indexed by option name */
    private array $_values = [];

    /**
     * Parse the argv input.
     *
     * @param array $argv The first item is ignored.
     *
     * @throws RuntimeException When unknown argument is given and not handled.
     * @throws InvalidParameterException When parameter is invalid and cannot be parsed.
     * @throws InvalidArgumentException When argument is invalid and cannot be parsed.
     * @return self
     */
    public function parse(array $argv): self
    {
        // Ignore the first arg (command name)
        array_shift($argv);

        $this->_normalizer = new Normalizer();
        $this->_tokenizer  = new Tokenizer($argv);

        foreach ($this->_tokenizer as $token) {
            // Its a constant value to be assigned to an argument:
            if ($token->isConstant() || $token->isVariadic()) {
                $this->parseArgs($token, $this->_tokenizer);
                continue;
            }
            // Its an option parse it and its value/s:
            if ($token->isOption()) {
                $this->parseOptions($token, $this->_tokenizer);
            }
        }

        $this->validate();

        return $this;
    }

    /**
     * Parse a single arg.
     *
     * @param Token $arg The current token
     * @param Tokenizer $queue The queue of tokens to be consumed
     *
     * @return void
     */
    protected function parseArgs(Token $arg, Tokenizer $queue): void
    {
        // Handle this argument:
        $argument = array_shift($this->_arguments);
        
        // No argument defined, so its an indexed arg:
        if (is_null($argument)) {
            // Its a single constant value arg:
            if ($arg->isConstant()) {
                $this->set(null, $arg->value());
            } else {
                // Its a variadic arg, so we need to collect all the remaining args:
                foreach ($arg->nested as $token) {
                    if ($token->isConstant()) {
                        $this->set(null, $token->value(), true);
                    } else {
                        throw new InvalidParameterException(
                            "Only constant parameters are allowed in variadic arguments"
                        );
                    }
                }
            }

            return;
        }

        // Its variadic, so we need to collect all the remaining args:
        if ($argument->variadic() && $arg->isConstant()) {
            // Consume all the remaining tokens
            // If an option is found, treat it as well
            while ($queue->valid()) {
                if ($queue->current()->isConstant()) {
                    $this->setValue($argument, $queue->current()->value());
                    $queue->next();
                } elseif ($queue->current()->isOption()) {
                    if ($consumed = $this->parseOptions($queue->current(), $queue, false)) {
                        for ($i = 0; $i < $consumed; $i++) {
                            $queue->next();
                        }
                    } else {
                        $queue->next();
                    }
                } else {
                    throw new InvalidParameterException(
                        "Only constant parameters are allowed in variadic arguments"
                    );
                }
            }

            return;
        }

        // Its variadic, and we have a variadic grouped arg:
        if ($argument->variadic() && $arg->isVariadic()) {
            // Consume all the nested tokens:
            foreach ($arg->nested as $token) {
                if ($token->isConstant()) {
                    $this->setValue($argument, $token->value());
                } else {
                    throw new InvalidParameterException(
                        "Only constant parameters are allowed in variadic arguments"
                    );
                }
            }

            return;
        }

        // Its not variadic, and we have a constant arg:
        if ($arg->isConstant()) {
            $this->setValue($argument, $arg->value());

            return;
        }

        // Its not variadic, and we have a variadic arg:
        if ($arg->isVariadic()) {
            throw new InvalidArgumentException(
                sprintf("Argument '%s' is not variadic", $argument->name())
            );
        }
    }

    /**
     * Parse an option, emit its event and set value.
     *
     * @param Token      $opt
     * @param Tokenizer  $tokens
     *
     * @return int Number of extra tokens consumed
     */
    protected function parseOptions(Token $opt, Tokenizer $tokens): int
    {
        // Look ahead for next token:
        $next = $tokens->offset(1);

        // Get the option:
        $option = $this->optionFor($opt->value());

        // Consumed:
        $consumed = 0;

        // Unknown option handle it:
        if (!$option) {
            // Single value just in case the value is a variadic group:
            $value = $next ? $next->value() : null;
            $used  = $this->handleUnknown(
                $opt->value(),
                is_array($value) ? $value[0] ?? null : $value
            );

            return $used ? ++$consumed : $consumed;
        }

        // Early out if its just a flag
        if (!$next) {
            $this->setValue($option);

            return $consumed;
        }

        // If option is variadic, and next is constant, then we need to collect all the remaining args:
        if ($option->variadic() && $next->isConstant()) {
            $tokens->next();
            while ($tokens->valid()) {
                $consumed++;
                if ($tokens->current()->isConstant()) {
                    $this->setValue($option, $tokens->current()->value());
                } else {
                    throw new InvalidParameterException(
                        "Only constants are allowed in variadic arguments"
                    );
                }
                $tokens->next();
            }

            return $consumed;
        }

        // If option is variadic, and next is a variadic group, then we need to collect all the nested values:
        if ($option->variadic() && $next->isVariadic()) {
            foreach ($next->nested as $token) {
                if ($token->isConstant()) {
                    $this->setValue($option, $token->value());
                } else {
                    throw new InvalidParameterException(
                        "Only constants are allowed in variadic arguments"
                    );
                }
            }
            // consume the next token:
            $tokens->next();

            return ++$consumed;
        }

        // If option is not variadic, and next is constant its a simple value assignment:
        if ($next->isConstant()) {
            $tokens->next(); // consume the next token
            $this->setValue($option, $next->value());

            return ++$consumed;
        }

        // anything else its just a flag:
        $this->setValue($option);

        return $consumed;
    }

    /**
     * Get matching option by arg (name) or null.
     *
     * @param string $name The name of the option
     *
     * @return Option|null
     */
    protected function optionFor(string $name): ?Option
    {
        foreach ($this->_options as $option) {
            if ($option->is($name)) {

                return $option;
            }
        }

        return null;
    }

    /**
     * Handle Unknown option.
     *
     * @param string $arg Option name
     * @param ?string $value Option value
     *
     * @throws RuntimeException When given arg is not registered and allow unknown flag is not set.
     * @return mixed If true it will indicate that value has been eaten.
     */
    abstract protected function handleUnknown(string $arg, ?string $value = null): mixed;

    /**
     * Emit the event with value.
     *
     * @param string $event Event name (is option name technically)
     * @param mixed  $value Value (is option value technically)
     *
     * @return mixed
     */
    abstract protected function emit(string $event, mixed $value = null): mixed;

    /**
     * Sets value of an option.
     *
     * @param Parameter   $parameter
     * @param string|null $value
     *
     * @return bool Indicating whether it has set a value or not.
     */
    protected function setValue(Parameter $parameter, ?string $value = null): bool
    {
        $name  = $parameter->attributeName();
        $value = $this->_normalizer->normalizeValue($parameter, $value);
        $emit  = $this->emit($parameter->attributeName(), $value) !== false;

        return $emit ? $this->set($name, $value, $parameter->variadic()) : false;
    }

    /**
     * Set a raw value.
     *
     * @param string|null $key
     * @param mixed $value
     * @param bool $variadic 
     *
     * @return bool Indicating whether it has set a value or not.
     */
    protected function set(?string $key, mixed $value, bool $variadic = false): bool
    {
        if (null === $key) {
            $this->_values[] = $value;
        } elseif ($variadic) {
            $this->_values[$key][] = $value;
        } else {
            $this->_values[$key] = $value;
        }

        return !in_array($value, [true, false, null], true);
    }

    /**
     * Validate if all required arguments/options have proper values.
     *
     * @throws RuntimeException If value missing for required ones.
     * @return void
     */
    protected function validate(): void
    {
        /** @var Parameter[] $missingItems */
        /** @var Parameter $item */
        $missingItems = array_filter(
            $this->_options + $this->_arguments,
            fn ($item) => $item->required() && in_array($this->_values[$item->attributeName()], [null, []])
        );

        foreach ($missingItems as $item) {
            [$name, $label] = [$item->name(), 'Argument'];
            if ($item instanceof Option) {
                [$name, $label] = [$item->long(), 'Option'];
            }
            throw new RuntimeException(
                sprintf('%s "%s" is required', $label, $name)
            );
        }
    }

    /**
     * Register a new argument/option.
     *
     * @param Parameter $param
     *
     * @return void
     */
    protected function register(Parameter $param): void
    {
        $this->ifAlreadyRegistered($param);

        $name = $param->attributeName();
        if ($param instanceof Option) {
            $this->_options[$name] = $param;
        } else {
            $this->_arguments[$name] = $param;
        }

        $this->set($name, $param->default());
    }

    /**
     * Unset a registered argument/option.
     *
     * @param string $name
     *
     * @return self
     */
    public function unset(string $name): self
    {
        unset($this->_values[$name], $this->_arguments[$name], $this->_options[$name]);

        return $this;
    }

    /**
     * What if the given name is already registered.
     *
     * @param Parameter $param
     *
     * @throws InvalidParameterException If given param name is already registered.
     * @return void
     */
    protected function ifAlreadyRegistered(Parameter $param): void
    {
        if ($this->registered($param->attributeName())) {
            throw new InvalidParameterException(sprintf(
                'The parameter "%s" is already registered',
                $param instanceof Option ? $param->long() : $param->name()
            ));
        }
    }

    /**
     * Check if either argument/option with given name is registered. 
     *
     * @param int|string $attribute
     *
     * @return bool
     */
    public function registered(int|string $attribute): bool
    {
        return array_key_exists($attribute, $this->_values);
    }

    /**
     * Get all options.
     *
     * @return Option[]
     */
    public function allOptions(): array
    {
        return $this->_options;
    }

    /**
     * Get all arguments.
     *
     * @return Argument[]
     */
    public function allArguments(): array
    {
        return $this->_arguments;
    }

    /**
     * Magic getter for specific value by its key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->_values[$key] ?? null;
    }

    /**
     * Get the command arguments i.e which is not an option.
     *
     * @return array
     */
    public function args(): array
    {
        return array_diff_key($this->_values, $this->_options);
    }

    /**
     * Get values indexed by camelized attribute name.
     *
     * @param bool $withDefaults Whether to include default values or not
     *
     * @return array
     */
    public function values(bool $withDefaults = true): array
    {
        $values            = $this->_values;
        $values['version'] = $this->_version ?? null;

        if (!$withDefaults) {
            unset($values['help'], $values['version'], $values['verbosity']);
        }

        return $values;
    }
}
