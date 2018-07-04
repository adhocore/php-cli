<?php

namespace Ahc\Cli\Helper;

use Ahc\Cli\Input\Argument;
use Ahc\Cli\Input\Parser as Command;
use Ahc\Cli\Input\Option;
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
        $this->showHelp('Arguments', $arguments, 4, $header, $footer);

        return $this;
    }

    /**
     * @param Option[] $options
     *
     * @return self
     */
    public function showOptionsHelp(array $options, string $header = '', string $footer = ''): self
    {
        $this->showHelp('Options', $options, 9, $header, $footer);

        return $this;
    }

    /**
     * @param Command[] $options
     *
     * @return self
     */
    public function showCommandsHelp(array $commands, string $header = '', string $footer = ''): self
    {
        $this->showHelp('Commands', $commands, 2, $header, $footer);

        return $this;
    }

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

    protected function sortItems(array $items, &$offset = 0)
    {
        $offset = 0;

        uasort($items, function ($a, $b) use (&$offset) {
            $offset = \max(\strlen($a->name()), \strlen($b->name()), $offset);

            return $a->name() <=> $b->name();
        });

        return $items;
    }

    protected function getName($item)
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
