<?php
namespace ParagonIE\Gossamer\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use ParagonIE\Gossamer\HttpInterface;
use Psr\Http\Message\ResponseInterface;

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
     */
    public function __construct()
    {
        $this->guzzle = new Client();
    }

    /**
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
