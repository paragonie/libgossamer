<?php
namespace ParagonIE\Gossamer\Client;

use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\Release\Common;
use ParagonIE\Gossamer\Release\Verifier;
use Psr\Http\Message\StreamInterface;

/**
 * Class UpdateFile
 * @package ParagonIE\Gossamer\Client
 */
class UpdateFile
{
    /** @var int $algorithm */
    protected $algorithm = Common::SIGN_ALG_ED25519_BLAKE2B;

    /** @var array{attestor: string, attestation: string, ledgerhash: string}[] $attestations */
    protected $attestations = [];

    /** @var array $metadata */
    protected $metadata = [];

    /** @var string $publicKey */
    protected $publicKey;

    /** @var AttestPolicy $attestPolicy */
    protected $attestPolicy;

    /** @var string $signature */
    protected $signature;

    /** @var string $tmpDir */
    protected $tmpDir;

    /**
     * UpdateFile constructor.
     *
     * @param string $publicKey
     * @param string $signature
     * @param array $metadata
     * @param array{attestor: string, attestation: string, ledgerhash: string}[] $attestations
     * @param ?AttestPolicy $attestPolicy
     */
    public function __construct(
        $publicKey,
        $signature,
        $metadata = [],
        $attestations = [],
        $attestPolicy = null
    ) {
        $this->publicKey = $publicKey;
        $this->signature = $signature;
        $this->metadata = $metadata;
        $this->attestations = $attestations;
        $this->tmpDir = \sys_get_temp_dir();
        if (is_null($attestPolicy)) {
            $attestPolicy = new AttestPolicy();
        }
        $this->attestPolicy = $attestPolicy;
    }

    /**
     * @param int $algId
     * @return self
     */
    public function setAlgorithm($algId = Common::SIGN_ALG_ED25519_BLAKE2B)
    {
        $this->algorithm = $algId;
        return $this;
    }

    /**
     * @param AttestPolicy $policy
     * @return self
     */
    public function setAttestPolicy(AttestPolicy $policy)
    {
        $this->attestPolicy = $policy;
        return $this;
    }

    /**
     * Should we install this file?
     *
     * @param string|resource|StreamInterface $streamOrFilePath
     * @return bool
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function isFileValid($streamOrFilePath)
    {
        $signatureValid = $this->isSignatureValid($streamOrFilePath);
        $attestationsPass = $this->passesAttestationPolicy();
        return $signatureValid && $attestationsPass;
    }

    /**
     * Do the set of attestations registered for this update pass
     * the local policy?
     *
     * @return bool
     */
    public function passesAttestationPolicy()
    {
        return $this->attestPolicy->passes($this->attestations);
    }

    /**
     * Is the signature we see valid for a given file?
     *
     * @param string|resource|StreamInterface $streamOrFilePath
     * @return bool
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function isSignatureValid($streamOrFilePath)
    {
        if ($streamOrFilePath instanceof StreamInterface) {
            // PSR-7 Stream

            $tmpFile = \tempnam($this->tmpDir, 'gossamer');
            $pos = $streamOrFilePath->tell();
            $streamOrFilePath->rewind();

            // Copy stream to temp file:
            \file_put_contents($tmpFile, $streamOrFilePath->getContents());

            $valid = (new Verifier($this->algorithm))
                ->verify($tmpFile, $this->signature, [$this->publicKey]);
            $streamOrFilePath->seek($pos);
            unlink($tmpFile);
        } elseif (is_resource($streamOrFilePath)) {
            // PHP resource

            $tmpFile = \tempnam($this->tmpDir, 'gossamer');
            $pos = \ftell($streamOrFilePath);
            \fseek($streamOrFilePath, 0);

            // Copy stream to temp file (via handler):
            $outFile = \fopen($tmpFile, 'rb');
            if (\is_bool(\stream_copy_to_stream($outFile, $streamOrFilePath))) {
                throw new GossamerException('Could not copy to stream');
            }
            \fclose($outFile);

            $valid = (new Verifier($this->algorithm))
                ->verify($tmpFile, $this->signature, [$this->publicKey]);
            \fseek($streamOrFilePath, $pos);
            unlink($tmpFile);
        } elseif (is_string($streamOrFilePath)) {
            // File path

            $valid = (new Verifier($this->algorithm))
                ->verify($streamOrFilePath, $this->signature, [$this->publicKey]);
        } else {
            throw new GossamerException('Invalid type for Argument 1');
        }
        return $valid;
    }
}
