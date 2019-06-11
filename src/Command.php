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
     * @var array
     */
    protected static $observe = [];

    /**
     * @param array|string $shell
     * @param string       $path
     *
     * @return array
     */
    public static function exec($shell, $path = '')
    {
        if ($path !== '') {
            $shell = "cd $path && $shell";
        }

        $command = new \mikehaertl\shellcommand\Command($shell);

        self::transfer($command, 'before');

        if ($command->execute()) {
            self::transfer($command, 'succeed');
            if ($command->getOutput()) {
                return explode("\n", $command->getOutput());
            }

            return explode("\n", $command->getStdErr());
        }
        self::transfer($command, 'executed');
        self::transfer($command, 'failed');
        $error = $command->getError();
        throw new RuntimeException("[$shell] $error", $command->getExitCode());
    }

    /**
     * @param \mikehaertl\shellcommand\Command $command
     * @param                                  $event
     */
    private static function transfer(\mikehaertl\shellcommand\Command $command, $event)
    {
        foreach (self::$observe as $observe) {
            if (method_exists($observe, $event)) {
                $observe->$event($command);
            }
        }
    }

    /**
     * @param string $class
     */
    public static function observe($class)
    {
        if (!class_exists($class)) {
            throw new RuntimeException("class not found: $class");
        }

        if (!in_array($class, self::$observe, true)) {
            self::$observe[] = new $class;
        }
    }
}
