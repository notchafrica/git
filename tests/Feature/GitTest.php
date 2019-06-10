<?php

namespace Songshenzong\Ding\Tests\Feature;

use Songshenzong\Git\Git;
use PHPUnit\Framework\TestCase;

class GitTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage /temp is not a git repository directory.
     */
    public static function testReset()
    {
        $path = '/temp';
        new Git($path);
    }
}
