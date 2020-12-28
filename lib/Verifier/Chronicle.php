<?php
namespace ParagonIE\Gossamer\Verifier;

use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\HttpInterface;
use ParagonIE\Gossamer\LedgerInterface;
use ParagonIE\Gossamer\LedgerVerifierInterface;
use ParagonIE\Gossamer\Protocol\SignedMessage;
use ParagonIE\Gossamer\Util;
use ParagonIE\Gossamer\VerifierInterface;
use SodiumException;

/**
 * Class Chronicle
 * @package ParagonIE\Gossamer\Verifier
 */
class Chronicle implements LedgerInterface, LedgerVerifierInterface
{
    // Verify Ed25519 signatures with the given public key
    const TRUST_BASIC = 'basic';

    // Blind faith in this Chronicle instance
    const TRUST_ZEALOUS = 'zealous';

    /**
     * @var HttpInterface $http
     */
    protected $http;

    /**
     * @var array<int, array<string, string>> $instances
     */
    protected $instances = array();

    /**
     * @var int $quorumMinimum
     */
    protected $quorumMinimum = 1;

    /**
     * Chronicle constructor.
     *
     * @param HttpInterface $http
     */
    public function __construct(HttpInterface $http)
    {
        $this->http = $http;
    }

    /**
     * @see Chronicle::quorumAgrees()
     *
     * @param string $hash
     * @return bool
     * @throws GossamerException
     * @throws SodiumException
     */
    public function verify($hash = '')
    {
        return $this->quorumAgrees($hash);
    }

    /**
     * Adds a Chronicle instance to this verifier.
     *
     * @param string $url
     * @param string $publicKey
     * @param string $trust
     * @return self
     */
    public function addChronicle($url, $publicKey, $trust = self::TRUST_BASIC)
    {
        $this->instances []= array(
            'url' => $url,
            'public-key' => $publicKey,
            'trust' => $trust
        );

        return $this;
    }

    /**
     * Fetch a random Chronicle instance.
     *
     * @return array<string, string>
     * @throws GossamerException
     */
    public function randomChronicle()
    {
        $r = Util::randomInt(0, count($this->instances));
        return $this->instances[$r];
    }

    /**
     * Fetch a random subset of the configured Chronicles.
     *
     * @param int $num
     * @return array<int, array<string, string>>
     * @throws GossamerException
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function randomSubset($num)
    {
        $chosen = array();
        if ($num < 1) {
            return $chosen;
        }
        $count = count($this->instances);
        if ($num > $count) {
            throw new GossamerException(
                'You want to query more Chronicle instances than are configured.'
            );
        }

        // Randomize the instances.
        /** @var array<int, array<string, string>> $selected */
        $selected = $this->instances;
        Util::secureShuffle($selected);
        if ($num === $count) {
            return $selected;
        }
        return array_slice($selected, 0, $num);
    }

    /**
     * Does the Quorum agree that a given hash exists?
     *
     * @param string $hash
     * @return bool
     * @throws GossamerException
     * @throws SodiumException
     */
    public function quorumAgrees($hash)
    {
        $shuffled = $this->instances;
        if (count($shuffled) < $this->quorumMinimum) {
            return false;
        }
        Util::secureShuffle($shuffled);

        $agrees = 0;
        do {
            /** @var array<string, string> $iterated */
            $iterated = array_pop($shuffled);
            if ($this->chronicleSeesHash($hash, $iterated)) {
                ++$agrees;
            }
        } while (!empty($shuffled) && $agrees < $this->quorumMinimum);
        return $agrees >= $this->quorumMinimum;
    }

    /**
     * Does the Chronicle server in question see this hash?
     *
     * @param string $hash
     * @param array<string, string> $chronicle
     * @return bool
     * @throws SodiumException
     */
    public function chronicleSeesHash($hash, array $chronicle)
    {
        /**
         * @var array{body: string, headers: array<array-key, array<array-key, string>>, status: int}
         */
        $response = $this->http->get(
            $chronicle['url'] . '/lookup/' . $hash
        );
        return $this->processChronicleResponse(
            $chronicle,
            $response['status'],
            $response['headers'],
            $response['body']
        );
    }

    /**
     * Process the response from a Chronicle instance.
     *
     * @param array<string, string> $chronicle
     * @param int $status
     * @param array<array-key, array<array-key, string>> $headers
     * @param string $body
     * @return bool
     *
     * @throws SodiumException
     * @psalm-suppress DocblockTypeContradiction
     */
    public function processChronicleResponse(array $chronicle, $status, array $headers, $body)
    {
        // Blind faith in a Chronicle instance; improves performance, costs security
        if (hash_equals($chronicle['trust'], self::TRUST_ZEALOUS)) {
            // We just want to see an HTTP 200 status.
            return $status >= 200 && $status < 300;
        }
        if ($status < 200 || $status >= 300) {
            // Fail fast for non-2xx HTTP status codes.
            return false;
        }
        if (!isset($headers['Body-Signature-Ed25519'])) {
            // Fail fast if the Ed25519 header is not provided.
            return false;
        }

        // Coerce into an array to ensure iterable structure:
        if (!is_array($headers['Body-Signature-Ed25519'])) {
            $headers['Body-Signature-Ed25519'] = array(
                $headers['Body-Signature-Ed25519']
            );
        }

        $validSignature = false;
        foreach ($headers['Body-Signature-Ed25519'] as $signature) {
            $validSignature = $validSignature || sodium_crypto_sign_verify_detached(
                Util::rawBinary($signature, 64),
                $body,
                Util::rawBinary($chronicle['public-key'], 32)
            );
        }
        return $validSignature;
    }

    /**
     * Set the minimum number of instances required to establish quorum.
     *
     * @param int $numInstances
     * @return self
     */
    public function setQuorumMinimum($numInstances)
    {
        $this->quorumMinimum = $numInstances;
        return $this;
    }

    /**
     * Clear all configured Chronicle instances.
     *
     * @return self
     */
    public function clearInstances()
    {
        $this->instances = array();
        return $this;
    }

    /**
     * Populate the verifier with the given Chronicle configuration.
     *
     * Appends, does not overwrite.
     *
     * @param array<array-key, array{url: string, public-key: string, trust: string}> $instances
     * @return self
     */
    public function populateInstances(array $instances)
    {
        /**
         * @var array{url: string, public-key: string, trust: string} $inst
         */
        foreach ($instances as $inst) {
            $this->addChronicle($inst['url'], $inst['public-key'], $inst['trust']);
        }
        return $this;
    }

    /**
     * Extract the summary hash from the signed message, then verify that summaryhash.
     *
     * @param SignedMessage $signedMessage
     * @return bool
     * @throws GossamerException
     * @throws SodiumException
     */
    public function signedMessageFound(SignedMessage $signedMessage)
    {
        return $this->verify($signedMessage->getMeta('summaryhash'));
    }
}
