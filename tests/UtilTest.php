<?php
namespace ParagonIE\Gossamer\Tests;

use ParagonIE\Gossamer\GossamerException;
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
     * @throws SodiumException
     */
    public function testB64uEncode()
    {
        $binary = "\xF0\x9D\x92\xB3" . "\xF0\x9D\xA5\xB3" .
            "\xF0\x9D\x92\xB3" . "\xF0\x9D\xA5\xB3" .
            "\xF0\x9D\x92\xB3" . "\xF0\x9D\xA5\xB3" .
            "\xF0\x9D\x92\xB3" . "\xF0\x9D\xA5\xB3";
        $encoded = Util::b64uEncode($binary);
        $this->assertSame('8J2Ss_CdpbPwnZKz8J2ls_CdkrPwnaWz8J2Ss_CdpbM=', $encoded);
    }

    /**
     * @throws Exception
     */
    public function testMemzero()
    {
        $string = random_bytes(32);
        Util::memzero($string);
        $this->assertEmpty($string, 'memzero() failed');
    }

    /**
     * @throws GossamerException
     */
    public function testRandomInt()
    {
        $array = array();

        for ($i = 0; $i < 1000; ++$i) {
            $int = Util::randomInt(1, 10);
            if (empty($array[$int])) {
                $array[$int] = 1;
            } else {
                $array[$int]++;
            }
            $this->assertGreaterThan(0, $int);
            $this->assertLessThan(11, $int);
        }
        for ($i = 1; $i <= 10; ++$i) {
            $this->assertNotEmpty($array[$i]);
        }
    }

    /**
     * @throws Exception
     * @throws SodiumException
     */
    public function testRawBinary()
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

    /**
     * @throws GossamerException
     */
    public function testSecureShuffle()
    {
        $array = str_split('abcdefghijklmnopqrstuvwxyz', 1);
        $failures = 0;
        do {
            Util::secureShuffle($array);
            if (implode('', $array) !== 'abcdefghijklmnopqrstuvwxyz') {
                // Good!
                break;
            } else {
                ++$failures;
            }
        } while ($failures < 3);
        $this->assertLessThan(3, $failures, 'Had to re-shuffle too many times.');

        $unique = array_unique($array);
        $this->assertEquals($unique, $array);
        $this->assertSame(26, count($array));
    }

    public function testStrlen()
    {
        $unicode = "\xF0\x9D\x92\xB3" . "\xF0\x9D\xA5\xB3" .
            "\xF0\x9D\x92\xB3" . "\xF0\x9D\xA5\xB3" .
            "\xF0\x9D\x92\xB3" . "\xF0\x9D\xA5\xB3" .
            "\xF0\x9D\x92\xB3" . "\xF0\x9D\xA5\xB3";
        $this->assertSame(32, Util::strlen($unicode));
    }
}
