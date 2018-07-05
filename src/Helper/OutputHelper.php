<?php

namespace Ahc\Cli\Helper;

use Ahc\Cli\Input\Argument;
use Ahc\Cli\Input\Option;
use Ahc\Cli\Input\Parser as Command;
use Ahc\Cli\Output\Writer;

/**
 * This helper helps you by showing you help information :).
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class OutputHelper
{
    protected $writer;

    public function __construct(Writer $writer = null)
    {
        $this->writer = $writer ?? new Writer;
    }

    /**
     * @param Argument[] $arguments
     *
     * @return self
     */
    public function showArgumentsHelp(array $arguments, string $header = '', string $footer = ''): self
    {
        $this->showHelp('Arguments', $arguments, 6, $header, $footer);

        return $this;
    }

    /**
     * @param Option[] $options
     *
     * @return self
     */
    public function showOptionsHelp(array $options, string $header = '', string $footer = ''): self
    {
        $this->showHelp('Options', $options, 13, $header, $footer);

        return $this;
    }

    /**
     * @param Command[] $options
     *
     * @return self
     */
    public function showCommandsHelp(array $commands, string $header = '', string $footer = ''): self
    {
        $this->showHelp('Commands', $commands, 4, $header, $footer);

        return $this;
    }

    /**
     * Show help with headers and footers.
     */
    protected function showHelp(string $for, array $items, int $space, string $header = '', string $footer = '')
    {
        if ($header) {
            $this->writer->bold($header, true);
        }

        $this->writer->eol()->boldGreen($for . ':', true);

        if (empty($items)) {
            $this->writer->bold('  (n/a)', true);

            return;
        }

        foreach ($this->sortItems($items, $padLen) as $item) {
            $name = $this->getName($item);
            $this->writer->bold('  ' . \str_pad($name, $padLen + $space))->comment($item->desc(), true);
        }

        if ($footer) {
            $this->writer->eol()->yellow($footer, true);
        }
    }

    /**
     * Sort items by name. As a side effect sets max length of all names.
     */
    protected function sortItems(array $items, &$max = 0): array
    {
        $max = 0;

        uasort($items, function ($a, $b) use (&$max) {
            $max = \max(\strlen($a->name()), \strlen($b->name()), $max);

            return $a->name() <=> $b->name();
        });

        return $items;
    }

    /**
     * Prepare name for different items.
     */
    protected function getName($item): string
    {
        $name = $item->name();

        if ($item instanceof Command) {
            return $name;
        }

        if ($item instanceof Option) {
            $name = $item->short() . '|' . $item->long();
        }

        if ($item->required()) {
            return "<$name>";
        }

        return "[$name]";
    }
}
