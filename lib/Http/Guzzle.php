<?php
namespace ParagonIE\Gossamer\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\Gossamer\HttpInterface;
use ParagonIE\Certainty\RemoteFetch;
use Psr\Http\Message\ResponseInterface;
use SodiumException;

/**
 * Class Guzzle
 * @package ParagonIE\Gossamer\Http
 */
class Guzzle implements HttpInterface
{
    /**
     * @var Client
     */
    private $guzzle;

    /**
     * Guzzle constructor.
     *
     * @param RemoteFetch|null $remoteFetch
     * @throws CertaintyException
     * @throws SodiumException
     */
    public function __construct($remoteFetch = null)
    {
        if (is_null($remoteFetch)) {
            $remoteFetch = new RemoteFetch(
                dirname(dirname(__DIR__)) . '/data'
            );
        }
        $this->guzzle = new Client(array(
            'verify' => $remoteFetch
                ->getLatestBundle()
                ->getFilePath()
        ));
    }

    /**
     * Send an HTTP GET request. Returns the response as an array:
     * - body: string,
     * - headers: string[][],
     * - status: int
     *
     * @param string $url
     * @return array{body: string, headers: array<array-key, array<array-key, string>>, status: int}
     */
    public function get($url)
    {
        try {
            $response = $this->guzzle->get($url);
        } catch (ClientException $ex) {
            $response = $ex->getResponse();
        }
        /** @var ResponseInterface $response */
        return $this->responseToArray($response);
    }

    /**
     * Convert a PSR-7 Response object into an array conforming to what our
     * library expects.
     *
     * @param ResponseInterface $response
     * @return array{body: string, headers: array<array-key, array<array-key, string>>, status: int}
     */
    public function responseToArray(ResponseInterface $response)
    {
        return array(
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string) $response->getBody()
        );
    }

    /**
     * Send an HTTP POST request. Returns the response as an array:
     * - body: string,
     * - headers: string[][],
     * - status: int
     *
     * @param string $url
     * @param string $body
     * @param array $headers
     * @return array{body: string, headers: array<array-key, array<array-key, string>>, status: int}
     */
    public function post($url, $body, array $headers = array())
    {
        try {
            $response = $this->guzzle->post(
                $url,
                array(
                    'body' => $body,
                    'headers' => $headers
                )
            );
        } catch (ClientException $ex) {
            $response = $ex->getResponse();
        }
        /** @var ResponseInterface $response */
        return $this->responseToArray($response);
    }
}
