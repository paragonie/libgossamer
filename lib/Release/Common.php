<?php
namespace ParagonIE\Gossamer\Release;

use ParagonIE\Gossamer\CryptoBackendInterface;
use ParagonIE\Gossamer\CryptoBackends\SodiumBackend;
use ParagonIE\Gossamer\GossamerException;

/**
 * Class Common
 * @package ParagonIE\Gossamer\Release
 */
class Common
{
    const SIGN_ALG_ED25519_BLAKE2B = 0x57505733;
    const SIGN_ALG_ED25519_SHA384  = 0x5750652f;

    /** @var string $alg */
    protected $alg;

    /** @var CryptoBackendInterface $backend */
    protected $backend;

    /** @var string $alg */
    protected $hashAlgorithm;

    /** @var string $alg */
    protected $signatureAlgorithm;

    /**
     * Verifier constructor.
     *
     * @param int $alg
     * @param CryptoBackendInterface|null $backend
     *
     * @throws GossamerException
     * @psalm-suppress InternalMethod
     */
    public function __construct(
        $alg = self::SIGN_ALG_ED25519_SHA384,
        CryptoBackendInterface $backend = null
    ) {
        if (empty($backend)) {
            $backend = new SodiumBackend();
        }
        $this->backend = $backend;
        /** @var array<int, array{signature: string, file-hash: string}> $map */
        $map = self::signatureAlgorithmMap();
        if (!array_key_exists($alg, $map)) {
            throw new GossamerException('Invalid algorithm: ' . $alg . ' (0x' . dechex($alg) . ').');
        }
        $this->alg = \ParagonIE_Sodium_Core_Util::store_4($alg);
        $this->signatureAlgorithm = $map[$alg]['signature'];
        $this->hashAlgorithm = $map[$alg]['file-hash'];
    }

    /**
     * @param string $filePath
     * @return string
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function preHashFile($filePath)
    {
        if (!is_readable($filePath)) {
            throw new GossamerException('Cannot read file: ' . $filePath);
        }
        if (in_array($this->hashAlgorithm, hash_algos(), true)) {
            return hash_file(
                $this->hashAlgorithm,
                $filePath,
                true
            );
        } elseif ($this->hashAlgorithm === 'blake2b') {
            $fp = fopen($filePath, 'rb');
            $stat = fstat($fp);
            /** @var int $size */
            $size = (int) $stat['size'];
            $state = sodium_crypto_generichash_init();
            for ($i = 0; $i < $size; $i += 8192) {
                $buf = fread($fp, 8192);
                sodium_crypto_generichash_update($state, $buf);
            }
            fclose($fp);
            return sodium_crypto_generichash_final($state);
        } else {
            throw new GossamerException('Invalid hash function: ' . $this->hashAlgorithm);
        }
    }

    /**
     * @return array
     */
    public static function signatureAlgorithmMap()
    {
        return array(
            self::SIGN_ALG_ED25519_SHA384 => array(
                'signature' => 'ed25519',
                'file-hash' => 'sha384'
            ),
            self::SIGN_ALG_ED25519_BLAKE2B => array(
                'signature' => 'ed25519',
                'file-hash' => 'blake2b'
            )
        );
    }
}