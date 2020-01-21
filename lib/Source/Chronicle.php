<?php
namespace ParagonIE\Gossamer\Source;

use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\HttpInterface;
use ParagonIE\Gossamer\SourceInterface;
use ParagonIE\Gossamer\Response;
use ParagonIE\Gossamer\Verifier\Chronicle as ChronicleVerifier;
use SodiumException;

/**
 * Class Chronicle
 * @package ParagonIE\Gossamer\Source
 */
class Chronicle implements SourceInterface
{
    /** @var HttpInterface $http */
    private $http;

    /** @var string $publicKey */
    private $publicKey;

    /** @var string $trust */
    private $trust;

    /** @var string $url */
    private $url;

    /** @var ChronicleVerifier $verifier */
    private $verifier;

    /**
     * Chronicle constructor.
     *
     * @param HttpInterface $http
     * @param string $url
     * @param string $publicKey
     * @param string $trust
     */
    public function __construct(HttpInterface $http, $url, $publicKey, $trust)
    {
        $this->http = $http;
        $this->url = $url;
        $this->publicKey = $publicKey;
        $this->trust = $trust;
        $this->verifier = new ChronicleVerifier($this->http);
    }

    /**
     * @param string $hash
     * @return Response
     *
     * @throws GossamerException
     * @throws SodiumException
     */
    public function getRecordsSince($hash = '')
    {
        /** @var array{body: string, headers: array<array-key, array<array-key, string>>, status: int} $contents */
        if (empty($hash)) {
            $httpResponse = $this->http->get('/export');
        } else {
            $httpResponse = $this->http->get('/since/' . $hash);
        }
        if (!$this->verifier->processChronicleResponse(
            array(
                'url' => $this->url,
                'public-key' => $this->publicKey,
                'trust' => $this->trust
            ),
            $httpResponse['status'],
            $httpResponse['headers'],
            $httpResponse['body']
        )) {
            throw new GossamerException('Invalid Chronicle response');
        }
        return new Response($httpResponse['body']);
    }
}
