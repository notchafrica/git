<?php

namespace Songshenzong\Git;

use Stringy\Stringy;
use RuntimeException;
use InvalidArgumentException;
use Songshenzong\Command\Output;
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
     * @param string $clone_url
     * @param string $target_dir
     *
     * @return Git
     */
    public static function cloneAndOpen($clone_url, $target_dir)
    {
        Command::exec("rm -rf $target_dir && git clone $clone_url $target_dir");

        return new self($target_dir);
    }

    /**
     * @param string $path
     *
     * @return Git
     */
    public static function open($path)
    {
        return new self($path);
    }

    /**
     * @param string $path
     *
     * @return Git
     */
    public static function init($path)
    {
        if (!file_exists($path)) {
            Command::exec("mkdir -p $path");
        }

        $exec = Command::exec("cd $path && git init");
        if (Stringy::create($exec)->contains('existing')) {
            throw new RuntimeException($exec);
        }

        return new self($path);
    }

    /**
     * Git constructor.
     *
     * @param string $path
     */
    protected function __construct($path)
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
     * @return Output
     */
    public function exec($shell)
    {
        return Command::exec($shell, $this->path);
    }

    /**
     * @return Output
     */
    public function reset()
    {
        return $this->exec('git fetch --all && git reset --hard origin/master');
    }

    /**
     * @return Output
     */
    public function status()
    {
        return $this->exec('git status');
    }

    /**
     * @return array
     */
    public function getTags()
    {
        $tags = $this->exec('git tag')->all();
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
     * @return Output
     */
    public function addTag($tag)
    {
        return $this->exec("git tag $tag && git push origin --tags");
    }

    /**
     * @param string $tag
     *
     * @return Output
     */
    public function deleteTag($tag)
    {
        $result = $this->exec("git tag -d $tag");
        $result = implode("\n", $result->all());
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
     * @param string $shell
     * @param string $string
     *
     * @return bool
     */
    public function resultContains($shell, $string)
    {
        $output = $this->exec($shell);
        $result = implode("\n", $output->all());

        return Stringy::create($result)->contains($string);
    }

    /**
     * @param string $files
     *
     * @return Output
     */
    public function add($files = '.')
    {
        return $this->exec("git add $files");
    }

    /**
     * @param string $message
     *
     * @return Output
     */
    public function commit($message)
    {
        return $this->exec("git commit -m '$message'");
    }

    /**
     * @return Output
     */
    public function amend()
    {
        return $this->exec('git commit --amend');
    }

    /**
     * @return Output
     */
    public function push()
    {
        return $this->exec('git push');
    }

    /**
     * @return Output
     */
    public function pushUOriginMaster()
    {
        return $this->exec('git push -u origin master');
    }

    /**
     * @param string $source
     * @param string $dir
     *
     * @return Output
     */
    public function updateFiles($source, $dir = '')
    {
        $target_dir = "{$this->path}{$dir}";

        return Command::exec("rm -rf {$target_dir}/* && cp -R -f $source/* {$target_dir}/");
    }
}
