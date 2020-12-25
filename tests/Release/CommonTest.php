<?php
namespace ParagonIE\Gossamer\Tests\Release;

use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\Release\Common;
use PHPUnit\Framework\TestCase;

/**
 * Class CommonTest
 * @covers \ParagonIE\Gossamer\Release\Common
 * @package ParagonIE\Gossamer\Tests\Release
 */
class CommonTest extends TestCase
{
    /**
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function testPreHashFile()
    {
        $filepath = __DIR__ . '/test.txt';
        $random = random_bytes(16384);
        file_put_contents($filepath, $random);
        $sha384 = hash('sha384', $random, true);
        $blake2 = sodium_crypto_generichash($random);

        $expect1 = (new Common(Common::SIGN_ALG_ED25519_SHA384))
            ->preHashFile($filepath);
        $expect2 = (new Common(Common::SIGN_ALG_ED25519_BLAKE2B))
            ->preHashFile($filepath);

        $this->assertEquals($expect1, $sha384, 'ed25519-sha384');
        $this->assertEquals($expect2, $blake2, 'ed25519-blake2b');
        unlink($filepath);
    }
}
