<?php

namespace ParagonIE\Gossamer\Release\Bundler;

use Exception;
use ParagonIE\Gossamer\Interfaces\ReleaseBundlerInterface;
use ParagonIE\Gossamer\TypeHelperTrait;

abstract class AbstractBundler implements ReleaseBundlerInterface
{
    use TypeHelperTrait;


    /** @var string $directory */
    protected $directory = '';

    /**
     * @param string $directory
     * @return self
     * @throws Exception
     */
    public function setWorkDirectory($directory)
    {
        $this->assert(is_string($directory), "Argument 1 must be a string");
        $this->assert(is_dir($directory), "Given path is not a directory: {$directory}");
        $this->directory = $directory;
        return $this;
    }
}