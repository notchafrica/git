<?php

namespace Songshenzong\Ding\Tests\Feature;

use Exception;
use Stringy\Stringy;
use Songshenzong\Git\Git;
use PHPUnit\Framework\TestCase;

/**
 * Class GitTest
 *
 * @package Songshenzong\Ding\Tests\Feature
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
        new Git($path);
    }

    public static function testCloneAndInstance()
    {
        $git = Git::cloneAndInstance('https://github.com/songshenzong/git.git', '/tmp/git');
        self::assertTrue($git->hasTag('1.0.0'));
        try {
            $git->addTag('1.0.0');
        } catch (Exception $exception) {
            self::assertTrue(Stringy::create($exception->getMessage())->contains('already exists'));
        }
    }
}
