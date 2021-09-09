<?php
namespace ParagonIE\Gossamer\Tests\Release\Bundler;

use Exception;
use ParagonIE\Gossamer\Release\Bundler\PharBundler;
use PHPUnit\Framework\TestCase;

/**
 * @covers PharBundler
 */
class PharBundlerTest extends TestCase
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

    /**
     * @throws Exception
     */
    public function testPhar()
    {
        if (ini_get('phar.readonly')) {
            $this->markTestSkipped("phar.readonly is set to true");
        }
        
        $bundler = (new PharBundler())
            ->setWorkDirectory($this->dir . '/lib');
        $bundler->bundle(dirname($this->dir) . '/test.phar');
        $this->assertFileExists(dirname($this->dir) . '/test.phar');
        unlink(dirname($this->dir) . '/test.phar');
    }
}
