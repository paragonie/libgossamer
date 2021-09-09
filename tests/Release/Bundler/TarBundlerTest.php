<?php
namespace ParagonIE\Gossamer\Tests\Release\Bundler;

use Exception;
use ParagonIE\Gossamer\Release\Bundler\TarBundler;
use PHPUnit\Framework\TestCase;

/**
 * @covers TarBundler
 */
class TarBundlerTest extends TestCase
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

    public function compressions()
    {
        return [
            [null, false],
            ['gz', false],
            ['bz2', false],
            ['lzma2', false],
            ['gzip', true],
        ];
    }

    /**
     * @dataProvider compressions
     *
     * @param string|null $compression
     * @param bool $expectFail
     */
    public function testCompression($compression, $expectFail = false)
    {
        if ($expectFail) {
            $this->expectException(Exception::class);
        }
        $tar = (new TarBundler())->setCompression($compression);
        $this->assertSame($compression, $tar->getCompression());
    }

    public function testTar()
    {
        $bundler = (new TarBundler())
            ->setWorkDirectory($this->dir . '/lib');
        $bundler->bundle(dirname($this->dir) . '/test.tar');
        $this->assertFileExists(dirname($this->dir) . '/test.tar');
        unlink(dirname($this->dir) . '/test.tar');
    }
}
