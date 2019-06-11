<?php

namespace Songshenzong\Git;

use Stringy\Stringy;
use InvalidArgumentException;
use Songshenzong\Command\Command;

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
     * @param string $shell
     *
     * @return array
     */
    public function exec($shell)
    {
        return Command::exec($shell, $this->path);
    }

    /**
     * @return array
     */
    public function reset()
    {
        return $this->exec('git fetch --all && git reset --hard origin/master');
    }

    /**
     * @return array
     */
    public function getTags()
    {
        $tags = $this->exec('git tag');
        usort($tags, 'version_compare');

        return $tags;
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
     * @return array
     */
    public function addTag($tag)
    {
        return $this->exec("git tag $tag && git push origin --tags");
    }

    /**
     * @param string $tag
     *
     * @return array
     */
    public function deleteTag($tag)
    {
        $result = $this->exec("git tag -d $tag");
        $result = implode("\n", $result);
        if (Stringy::create($result)->contains('Deleted')) {
            return $this->exec("git push origin :refs/tags/$tag");
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isNothingToCommit()
    {
        return $this->resultContains('git status', 'nothing to commit');
    }

    /**
     * @param $shell
     * @param $string
     *
     * @return bool
     */
    public function resultContains($shell, $string)
    {
        $output = $this->exec($shell);
        $result = implode("\n", $output);

        return Stringy::create($result)->contains($string);
    }

    /**
     * @param string $files
     *
     * @return array
     */
    public function addFiles($files = '.')
    {
        return $this->exec("git add $files");
    }

    /**
     * @param string $message
     *
     * @return array
     */
    public function commitMessage($message)
    {
        return $this->exec("git commit -m '$message'");
    }

    /**
     * @return array
     */
    public function push()
    {
        return $this->exec('git push');
    }

    /**
     * @return array
     */
    public function pushUOriginMaster()
    {
        return $this->exec('git push -u origin master');
    }

    /**
     * @param string $source
     * @param string $dir
     *
     * @return array
     */
    public function updateFiles($source, $dir = '')
    {
        $target_dir = "{$this->path}{$dir}";

        return Command::exec("rm -rf {$target_dir}/* && cp -R -f $source/* {$target_dir}/");
    }

    /**
     * @param $clone_url
     * @param $target_dir
     *
     * @return Git
     */
    public static function cloneAndInstance($clone_url, $target_dir)
    {
        Command::exec("rm -rf $target_dir && git clone $clone_url $target_dir");

        return new self($target_dir);
    }
}
