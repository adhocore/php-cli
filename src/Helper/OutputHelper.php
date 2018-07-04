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
        $w = new Writer;

        if ($header) {
            $w->bold($header, true);
        }

        $w->eol()->boldGreen($for . ':', true);

        if (empty($items)) {
            $w->bold('  (n/a)', true);

            return;
        }

        ksort($items);

        $maxLen = \max(\array_map('strlen', \array_keys($items)));

        foreach ($items as $item) {
            $name = $this->getName($item);
            $w->bold('  ' . \str_pad($name, $maxLen + $space))->comment($item->desc(), true);
        }

        if ($footer) {
            $w->eol()->yellow($footer, true);
        }
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
