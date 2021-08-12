<?php
namespace ParagonIE\Gossamer;

use ParagonIE\Gossamer\Interfaces\DbInterface;
use ParagonIE\Gossamer\Interfaces\HttpInterface;
use ParagonIE\Gossamer\Interfaces\LedgerVerifierInterface;
use ParagonIE\Gossamer\Interfaces\SourceInterface;
use ParagonIE\Gossamer\Protocol\Action;
use ParagonIE\Gossamer\Protocol\SignedMessage;
use ParagonIE\Gossamer\Source\Chronicle as ChronicleSource;
use ParagonIE\Gossamer\Verifier\Chronicle as ChronicleVerifier;

/**
 * Class Synchronizer
 * @package ParagonIE\Gossamer
 */
class Synchronizer
{
    /** @var DbInterface $db */
    protected $db;

    /** @var HttpInterface $http */
    protected $http;

    /** @var array<array-key, array{url: string, public-key: string, trust: string}> */
    protected $pool = array();

    /** @var string $superProvider */
    protected $superProvider = '';

    /** @var LedgerVerifierInterface $verifier */
    protected $verifier;

    /**
     * Synchronizer constructor.
     *
     * @param DbInterface $db
     * @param HttpInterface $http
     * @param LedgerVerifierInterface $verifier
     * @param array<array-key, array{url: string, public-key: string, trust: string}> $pool
     * @param string $superProvider
     */
    public function __construct(
        DbInterface $db,
        HttpInterface $http,
        LedgerVerifierInterface $verifier,
        array $pool = array(),
        $superProvider = ''
    ) {
        $this->db = $db;
        $this->http = $http;
        $this->verifier = $verifier;
        /** @var array{url: string, public-key: string, trust: string} $item */
        foreach ($pool as $item) {
            $this->addToPool($item['url'], $item['public-key'], $item['trust']);
        }
        $this->superProvider = $superProvider;
    }

    /**
     * @param string $url
     * @param string $publicKey
     * @param string $trust
     * @return self
     */
    public function addToPool($url, $publicKey, $trust = ChronicleVerifier::TRUST_BASIC)
    {
        $this->pool[] = array(
            'url' => $url,
            'public-key' => $publicKey,
            'trust' => $trust
        );
        return $this;
    }
    /**
     * @param int $index
     * @return array{0: array{url: string, public-key: string, trust: string}, 1: array{url: string, public-key: string, trust: string}[]}
     * @throws GossamerException
     */
    public function extractSourceAndPeers($index)
    {
        $poolCopy = $this->pool;
        $source = $poolCopy[$index];
        unset($poolCopy[$index]);
        return array($source, array_values($poolCopy));
    }

    /**
     * @param int $int
     * @return array{0: array{url: string, public-key: string, trust: string}, 1: array{url: string, public-key: string, trust: string}[]}
     * @throws GossamerException
     */
    public function extractRandomSourceAndPeers()
    {
        if (empty($this->pool)) {
            throw new GossamerException('Empty pool.');
        }
        $index = Util::randomInt(0, count($this->pool) - 1);
        return $this->extractSourceAndPeers($index);
    }

    /**
     * @param array{url: string, public-key: string, trust: string} $config
     * @return SourceInterface
     */
    public function getSource(array $config)
    {
        if ($this->verifier instanceof ChronicleVerifier) {
            return new ChronicleSource(
                $this->http,
                $config['url'],
                $config['public-key'],
                $config['trust']
            );
        }

        throw new \TypeError('No appropriate SourceInterface defined.');
    }

    /**
     * @param array{url: string, public-key: string, trust: string}[] $peers
     * @return LedgerVerifierInterface
     */
    public function getVerifier(array $peers)
    {
        $verifier = clone $this->verifier;
        $verifier->clearInstances();
        $verifier->populateInstances($peers);
        return $verifier;
    }

    /**
     * Keep calling transcribe() until we run out of upstream messages to copy/parse,
     * or we encounter a GossamerException.
     *
     * @return bool
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function sync()
    {
        $hash = $this->db->getCheckpointHash();
        list($sourceArray, $peers) = $this->extractRandomSourceAndPeers();
        $verifier = $this->getVerifier($peers);
        $source = $this->getSource($sourceArray);

        do {
            $recordsSince = $source->getRecordsSince($hash);
            $messages = $recordsSince->extractAllFromChronicleResponse();
            if (empty($messages)) {
                return true;
            }
            $this->transcribe($messages, $verifier);
            $prev = $hash;
            $hash = $this->db->getCheckpointHash();
        } while ($hash !== $prev);
        return true;
    }

    /**
     * @param array<array-key, SignedMessage> $signedMessages
     * @param LedgerVerifierInterface $verifier
     * @return bool
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function transcribe(array $signedMessages, LedgerVerifierInterface $verifier)
    {
        /** @var SignedMessage $signedMessage */
        foreach ($signedMessages as $signedMessage) {
            if ($verifier->signedMessageFound($signedMessage)) {
                $message = $signedMessage->verifyAndExtract($this->db, $this->superProvider);
                $action = Action::fromMessage($message);
                $action->perform($this->db);
            }
        }
        return true;
    }
}
