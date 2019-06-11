<?php

namespace Songshenzong\Git;

use RuntimeException;

/**
 * Class Command
 *
 * @package Songshenzong\Git
 */
class Command
{
    /**
     * @param string $shell
     * @param string $path
     *
     * @return array
     */
    public static function exec($shell, $path = '')
    {
        if ($path !== '') {
            $shell = "cd $path && $shell";
        }

        $command = new \mikehaertl\shellcommand\Command($shell);
        if ($command->execute()) {
            if ($command->getOutput()) {
                return explode("\n", $command->getOutput());
            }

            return explode("\n", $command->getStdErr());
        }

        $error = $command->getError();
        throw new RuntimeException("[$shell] $error", $command->getExitCode());
    }
}
