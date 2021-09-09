<?php
namespace ParagonIE\Gossamer\Release\Bundler;

use ParagonIE\Gossamer\GossamerException;
use Phar;

class PharBundler extends AbstractBundler
{
    /** @var string $defaultStubFilename */
    protected $defaultStubFilename = 'default-stub.php';

    /**
     * @param string $filename
     * @return self
     */
    public function setDefaultStubFilename($filename)
    {
        $this->assert(is_string($filename), "Argument 1 must be a string");
        $this->defaultStubFilename = $filename;
        return $this;
    }

    /**
     * @return void
     * @throws GossamerException
     */
    public function throwIfPharReadonly()
    {
        $readOnly = (bool) ini_get('phar.readonly');
        if ($readOnly) {
            throw new GossamerException(
                "Cannot work with Phars without setting readonly to false"
            );
        }
    }

    public function bundle($outputFile)
    {
        $this->throwIfPharReadonly();
        $workingDir = \getcwd();
        \chdir($this->directory);

        $phar = new Phar($outputFile);
        $phar->startBuffering();
        $defaultStub = $phar->createDefaultStub($this->defaultStubFilename);
        $phar->buildFromDirectory($this->directory);
        $phar->setStub('#!/usr/bin/env php' . "\n" . $defaultStub);
        $phar->stopBuffering();

        \chdir($workingDir);
        return is_file($outputFile);
    }
}
