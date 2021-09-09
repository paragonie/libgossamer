<?php
namespace ParagonIE\Gossamer\Interfaces;

interface ReleaseBundlerInterface
{
    /**
     * @param string $directory
     * @return self
     */
    public function setWorkDirectory($directory);

    /**
     * @param string $outputFile
     * @return bool
     */
    public function bundle($outputFile);
}
