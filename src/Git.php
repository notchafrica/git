<?php

namespace Songshenzong\Git;

use Stringy\Stringy;
use InvalidArgumentException;

/**
 * Class Git
 *
 * @package Songshenzong\Git
 */
class Git
{

    /**
     * @var string
     */
    protected $path = '';

    /**
     * Git constructor.
     *
     * @param $path
     */
    public function __construct($path)
    {
        if (!file_exists("$path/.git")) {
            throw new InvalidArgumentException("$path is not a git repository directory.");
        }

        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return bool|string
     */
    public function reset()
    {
        $cmd = "cd {$this->path} && git fetch --all && git reset --hard origin/master";

        return system($cmd);
    }

    /**
     * @return string
     */
    public function getLastTag()
    {
        $tags = $this->getTags();

        return array_pop($tags);
    }

    /**
     * @return array
     */
    public function getTags()
    {
        $cmd  = "cd {$this->path} && git tag";
        $tags = shell_exec($cmd);
        $tags = explode("\n", $tags);
        usort($tags, 'version_compare');

        return $tags;
    }

    /**
     * @param string $tag
     *
     * @return bool
     */
    public function hasTag($tag)
    {
        return in_array($tag, $this->getTags(), true);
    }

    /**
     * @param string $tag
     *
     * @return bool
     */
    public function addTag($tag)
    {
        if ($this->hasTag($tag)) {
            return false;
        }

        exec("cd {$this->path} && git tag $tag && git push origin --tags");

        return true;
    }

    /**
     * @param string $tag
     */
    public function deleteTag($tag)
    {
        $result = exec("cd {$this->path} && git tag -d $tag");
        if (Stringy::create($result)->contains('Deleted')) {
            exec("cd {$this->path} && git push origin :refs/tags/$tag");
        }
    }

    /**
     * @return bool
     */
    public function isNothingToCommit()
    {
        $res = exec("cd {$this->path} && git status");

        return Stringy::create($res)->contains('nothing to commit');
    }

    /**
     * @param string $files
     *
     * @return string
     */
    public function addFiles($files = '.')
    {
        return exec("cd {$this->path} && git add $files");
    }

    /**
     * @param string $shell
     *
     * @return string
     */
    public function shell($shell)
    {
        return exec("cd {$this->path} && $shell");
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function commitMessage($message)
    {
        return exec("cd {$this->path} && git commit -m '$message'");
    }

    /**
     * @return string
     */
    public function push()
    {
        return exec("cd {$this->path} && git push");
    }

    /**
     * @return string
     */
    public function pushUOriginMaster()
    {
        return exec("cd {$this->path} && git push -u origin master");
    }

    /**
     * @param $clone_url
     * @param $target_dir
     *
     * @return Git
     */
    public static function cloneAndInstance($clone_url, $target_dir)
    {
        exec("rm -rf $target_dir && git clone $clone_url $target_dir 1>/dev/null 2>/dev/null", $output);

        return new self($target_dir);
    }
}
