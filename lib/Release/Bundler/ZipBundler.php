<?php
namespace ParagonIE\Gossamer\Release\Bundler;

use Exception;
use ParagonIE\Gossamer\GossamerException;
use ZipArchive;

class ZipBundler extends AbstractBundler
{
    /**
     * @param ZipArchive $zip
     * @param string $parent
     * @return ZipArchive
     */
    public function addFilesToZip(ZipArchive $zip, $parent)
    {
        $base = str_replace($this->directory, '', $parent);
        $zip->addEmptyDir($base);
        /** @var string $file */
        foreach (glob($parent . '/*') as $file) {
            if (is_dir($file)) {
                $this->addFilesToZip($zip, $file);
            } else {
                $newFile = ltrim(str_replace($this->directory, '', $file), '/');
                $zip->addFile((string) $file, (string) $newFile);
            }
        }
        return $zip;
    }

    public function bundle($outputFile)
    {
        $workingDir = \getcwd();
        \chdir($this->directory);
        $zip = new ZipArchive();
        if ($zip->open($outputFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new GossamerException("Cannot create zip");
        }

        $this->addFilesToZip($zip, $this->directory);

        \chdir($workingDir);
        return $zip->close();
    }
}
