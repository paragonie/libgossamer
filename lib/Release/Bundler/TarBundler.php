<?php
namespace ParagonIE\Gossamer\Release\Bundler;

use Archive_Tar;

class TarBundler extends AbstractBundler
{
    /** @var ?string $compression */
    protected $compression = null;

    /**
     * @return ?string
     */
    public function getCompression()
    {
        return $this->compression;
    }

    /**
     * @param ?string $compress
     * @return self
     * @throws \Exception
     */
    public function setCompression($compress)
    {
        if (is_null($compress)) {
            $this->compression = null;
            return $this;
        }
        $this->assert(
            is_string($compress),
            "Argument 1 must be a string or null"
        );
        $this->assert(
            in_array($compress, ['gz', 'bz2', 'lzma2']),
            "Invalid compression method: " . $compress
        );
        $this->compression = $compress;
        return $this;
    }

    /**
     * @param string $parent
     * @return array
     */
    public function listFilesToTar($parent)
    {
        /** @var string $file */
        $paths = [];
        foreach (glob($parent . '/*') as $file) {
            if (is_dir($file)) {
                $paths = array_merge($paths, $this->listFilesToTar($file));
            } else {
                $paths[] = str_replace($this->directory . '/', '',  $file);
            }
        }
        return $paths;
    }

    public function bundle($outputFile)
    {
        $workingDir = \getcwd();
        \chdir($this->directory);

        $tar = new Archive_Tar($outputFile, $this->compression);

        $fileList = $this->listFilesToTar($this->directory);

        $result = $tar->create($fileList);
        \chdir($workingDir);
        return $result;
    }
}
