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
    public function getPath()
    {
        return $this->path;
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
     * @param $clone_url
     * @param $target_dir
     *
     * @return Git
     */
    public function cloneAndInstance($clone_url, $target_dir)
    {
        exec("rm -rf $target_dir && git clone $clone_url $target_dir 1>/dev/null 2>/dev/null", $output);

        return new self($target_dir);
    }
}
