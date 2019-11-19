<?php
namespace ParagonIE\Gossamer\Tests;

use ParagonIE\Gossamer\Util;
use PHPUnit\Framework\TestCase;
use Exception;
use SodiumException;

/**
 * Class UtilTest
 */
class UtilTest extends TestCase
{
    /**
     * @throws Exception
     * @throws SodiumException
     */
    public function testBinary()
    {
        for ($i = 0; $i < 100; ++$i) {
            $fuzz = random_bytes(1 + $i);
            $hex = sodium_bin2hex($fuzz);
            $b64u = sodium_bin2base64($fuzz, SODIUM_BASE64_VARIANT_URLSAFE);
            $b64o = sodium_bin2base64($fuzz, SODIUM_BASE64_VARIANT_ORIGINAL);

            $this->assertSame(
                $fuzz,
                Util::rawBinary($fuzz, 1 + $i)
            );
            $this->assertSame(
                $fuzz,
                Util::rawBinary($hex, 1 + $i)
            );
            $this->assertSame(
                $fuzz,
                Util::rawBinary($b64o, 1 + $i)
            );
            $this->assertSame(
                $fuzz,
                Util::rawBinary($b64u, 1 + $i)
            );
        }
    }
}
