<?php
namespace ParagonIE\Gossamer\Tests\Release\Bundler;

use ParagonIE\Gossamer\Release\Bundler\ZipBundler;
use PHPUnit\Framework\TestCase;

/**
 * @covers ZipBundler
 */
class ZipBundlerTest extends TestCase
{
    /** @var string $dir */
    protected $dir = '';

    /**
     * @before
     */
    public function before()
    {
        $dir = __DIR__;
        do {
            $prev = $dir;
            $dir = realpath(dirname($dir));
        } while (!empty($dir) && $dir !== $prev && !is_dir($dir . '/.git'));
        $this->dir = $dir;
    }

    public function testZip()
    {
        $bundler = (new ZipBundler())
            ->setWorkDirectory($this->dir . '/lib');
        $bundler->bundle(dirname($this->dir) . '/test.zip');
        $this->assertFileExists(dirname($this->dir) . '/test.zip');
        unlink(dirname($this->dir) . '/test.zip');
    }
}