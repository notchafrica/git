<?php

namespace Songshenzong\Git\Tests\Feature;

use Exception;
use Stringy\Stringy;
use Songshenzong\Git\Git;
use PHPUnit\Framework\TestCase;
use Songshenzong\Command\Command;

/**
 * Class GitTest
 *
 * @package Songshenzong\Git\Tests\Feature
 */
class GitTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage /tmp is not a git repository directory.
     */
    public static function testReset()
    {
        $path = '/tmp';
        Git::open($path);
    }

    public static function testCloneAndOpen()
    {
        $git = Git::cloneAndOpen('https://github.com/songshenzong/git.git', '/tmp/git');
        self::assertTrue($git->hasTag('1.0.0'));
        try {
            $git->addTag('1.0.0');
        } catch (Exception $exception) {
            self::assertTrue(Stringy::create($exception->getMessage())->contains('already exists'));
        }
    }

    public static function testInit()
    {
        $dir = __DIR__ . '/repo';
        Command::exec("rm -rf $dir");
        $git = Git::init($dir);
        self::assertEquals($dir, $git->getPath());
        Command::exec("rm -rf $dir");
    }
}
