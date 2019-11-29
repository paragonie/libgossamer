<?php
namespace ParagonIE\Gossamer\Tests\Dummy;

use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\Util;

/**
 * Class DummyChronicle
 * @package ParagonIE\Gossamer\Tests\Dummy
 */
class DummyChronicle
{
    /** @var array<array-key, array<string, string>> $chain */
    private $chain = array();

    /** @var string $hs Hash State */
    private $hs;

    /**
     * DummyChronicle constructor.
     * @throws \SodiumException
     */
    public function __construct()
    {
        $this->hs = sodium_crypto_generichash_init();
    }

    /**
     * @param string $content
     * @param string $publicKey
     * @param string $signature
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function append($content, $publicKey, $signature)
    {
        $signature = Util::rawBinary($signature, 64);
        $publicKey = Util::rawBinary($publicKey, 32);
        if (!sodium_crypto_sign_verify_detached($signature, $content, $publicKey)) {
            throw new GossamerException('Invalid signature');
        }
        $latest = $this->latest();
        $prevhash = !empty($latest['currhash'])
            ? Util::rawBinary($latest['currhash'], 32)
            : '';
        $currhash = sodium_crypto_generichash($content, $prevhash);
        sodium_crypto_generichash_update($this->hs, $currhash);

        $summary = $this->getSummaryHash();

        $this->chain[] = array(
            'contents' => $content,
            'prev' => Util::b64uEncode($prevhash),
            'hash' => Util::b64uEncode($currhash),
            'summary' => Util::b64uEncode($summary),
            'created' => (new \DateTime())->format(\DateTime::ATOM),
            'publickey' => Util::b64uEncode($publicKey),
            'signature' => Util::b64uEncode($signature)
        );
    }

    /**
     * @return array<array-key, array<array-key, string>>
     */
    public function export()
    {
        return $this->chain;
    }

    /**
     * @return string
     * @throws \SodiumException
     */
    public function getSummaryHash()
    {
        $len = Util::strlen($this->hs);
        $random = random_bytes($len);
        $state = $this->hs ^ $random;
        $return = sodium_crypto_generichash_final($this->hs, 32);
        $this->hs = $state ^ $random;
        return $return;
    }

    /**
     * @return array
     */
    public function latest()
    {
        if (empty($this->chain)) {
            return array();
        }
        $key = array_keys($this->chain)[count($this->chain) - 1];
        return $this->chain[$key];
    }

    /**
     * @param string $summaryHash
     * @return array<array-key, array<array-key, string>>
     *
     * @throws \SodiumException
     */
    public function lookup($summaryHash)
    {
        $return = array();
        $match = Util::rawBinary($summaryHash, 32);
        foreach ($this->chain as $c) {
            $iter = Util::rawBinary($c['summary'], 32);
            if (hash_equals($match, $iter)) {
                $return[] = $c;
            }
        }
        return $return;
    }
}
