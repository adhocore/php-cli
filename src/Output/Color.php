<?php

namespace Ahc\Cli\Output;

/**
 * Cli Colstatic ostatic rizer.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link   static  https://github.com/adhocore/cli
 */
class Color
{
    const BLACK  = 30;
    const RED    = 31;
    const GREEN  = 32;
    const YELLOW = 33;
    const BLUE   = 34;
    const PURPLE = 35;
    const CYAN   = 36;
    const WHITE  = 37;

    /** @var string Cli format */
    protected $format = "\033[:bold:;:fg:;:bg:m:text:\033[0m";

    /** @vstatic ar array Custom styles */
    protected static $styles = [];

    public function comment(string $text, array $style = [], bool $eol = false)
    {
        return $this->line($text, ['fg' => static::BLACK, 'bold' => 1] + $style, $eol);
    }

    public function error(string $text, array $style = [], bool $eol = false)
    {
        return $this->line($text, ['fg' => static::RED] + $style, $eol);
    }

    public function ok(string $text, array $style = [], bool $eol = false)
    {
        return $this->line($text, ['fg' => static::GREEN] + $style, $eol);
    }

    public function warn(string $text, array $style = [], bool $eol = false)
    {
        return $this->line($text, ['fg' => static::YELLOW] + $style, $eol);
    }

    public function info(string $text, array $style = [], bool $eol = false)
    {
        return $this->line($text, ['fg' => static::BLUE] + $style, $eol);
    }

    /**
     * Returns a formatted/colored line.
     *
     * @param string $text
     * @param array  $style
     * @param bool   $eol   End of line
     *
     * @return string
     */
    public function line(string $text, array $style = [], bool $eol = false)
    {
        $style += ['bg' => null, 'fg' => static::WHITE, 'bold' => false];

        $format = $style['bg'] === null
            ? \str_replace(';:bg:', '', $this->format)
            : $this->format;

        $line = \strtr($format, [
            ':bold:' => (int) $style['bold'],
            ':fg:'   => (int) $style['fg'],
            ':bg:'   => (int) $style['bg'] + 10,
            ':text:' => (string) $text,
        ]);

        // Allow `Color::line('msg', [true])` instead of `Color::line('msg', [], true)`
        if ($eol || !empty($style[0])) {
            $line .= $this->eol();
        }

        return $line;
    }

    public function eol()
    {
        return \PHP_EOL;
    }

    /**
     * Register a custom style.
     *
     * @param string $name  Example: 'alert'
     * @param array  $style Example: ['fg' => Color::RED, 'bg' => Color::YELLOW, 'bold' => true]
     *
     * @return void
     */
    public static function style(string $name, array $style)
    {
        $allow = ['fg' => true, 'bg' => true, 'bold' => true];
        $style = \array_intersect_key($style, $allow);

        if (empty($style)) {
            throw new \InvalidArgumentException('Trying to set empty or invalid style');
        }

        if (isset(static::$styles[$name]) || \method_exists(static::class, $name)) {
            throw new \InvalidArgumentException('Trying to define existing style');
        }

        static::$styles[$name] = $style;
    }

    /**
     * Magically build styles.
     *
     * @param string $name      Example: 'boldError', 'bgGreenBold' etc
     * @param array  $arguments
     *
     * @return string
     */
    public function __call(string $name, array $arguments)
    {
        if (!isset($arguments[0])) {
            throw new \InvalidArgumentException('Text required');
        }

        list($name, $text, $style, $eol) = $this->parseCall($name, $arguments);

        if (isset(static::$styles[$name])) {
            return $this->line($text, $style + static::$styles[$name], $eol);
        }

        if (\defined($color = static::class . '::' . \strtoupper($name))) {
            $name   = 'line';
            $style += ['fg' => \constant($color)];
        }

        if (!\method_exists($this, $name)) {
            throw new \InvalidArgumentException(\sprintf('Style "%s" not defined', $name));
        }

        return $this->{$name}($text, $style, $eol);
    }

    /**
     * Parse the name argument pairs to determine callable method and style params.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return array
     */
    protected function parseCall(string $name, array $arguments)
    {
        list($text, $style, $eol) = $arguments + ['', [], false];

        if (\stripos($name, 'bold') !== false) {
            $name   = \str_ireplace('bold', '', $name);
            $style += ['bold' => 1];
        }

        if (!\preg_match_all('/([b|B|f|F]g)?([A-Z][a-z]+)([^A-Z])?/', $name, $matches)) {
            return [\lcfirst($name) ?: 'line', $text, $style, $eol];
        }

        list($name, $style) = $this->buildStyle($name, $style, $matches);

        return [$name, $text, $style, $eol];
    }

    /**
     * Build style parameter from matching combination.
     *
     * @param string $name
     * @param array  $style
     * @param array  $matches
     *
     * @return arrsy
     */
    protected function buildStyle(string $name, array $style, array $matches)
    {
        foreach ($matches[0] as $i => $match) {
            $name  = \str_replace($match, '', $name);
            $type  = \strtolower($matches[1][$i]) ?: 'fg';

            if (\defined($color = static::class . '::' . \strtoupper($matches[2][$i]))) {
                $style += [$type => \constant($color)];
            }
        }

        return [\lcfirst($name) ?: 'line', $style];
    }
}
