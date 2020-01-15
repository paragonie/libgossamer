<?php
namespace ParagonIE\Gossamer\Http;

use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\Certainty\RemoteFetch;
use ParagonIE\Gossamer\HttpException;
use ParagonIE\Gossamer\HttpInterface;
use WP_Http;
use WP_Error;

/**
 * Class WordPress
 * @package ParagonIE\Gossamer\Http
 */
class WordPress implements HttpInterface
{
    /** @var WP_Http $http */
    private $http;

    /** @var RemoteFetch|null $remoteFetch */
    private $remoteFetch;

    /**
     * WordPress constructor.
     *
     * @param RemoteFetch|null $remoteFetch
     * @throws CertaintyException
     * @throws \SodiumException
     */
    public function __construct($remoteFetch = null)
    {
        if (is_null($remoteFetch)) {
            $remoteFetch = new RemoteFetch(
                dirname(dirname(__DIR__)) . '/data'
            );
        }
        $this->remoteFetch = $remoteFetch;
        $this->http = new WP_Http();
    }

    /**
     * @param string $url
     * @return array{body: string, headers: array<array-key, array<array-key, string>>, status: int}
     * @throws HttpException
     */
    public function get($url)
    {
        /** @var WP_Error|array{headers: array<array-key,string|array<array-key, string>>, body:string, response: array, cookies: array, filename: string} $response */
        $response = $this->http->get($url);
        if ($response instanceof WP_Error) {
            throw new HttpException('An unexpected error occurred');
        }
        return $this->processResponse($response);
    }

    /**
     * @param array{headers: array<array-key,string|array<array-key, string>>, body:string, response: array, cookies: array, filename: string} $response
     * @return array{body: string, headers: array<array-key, array<array-key, string>>, status: int}
     */
    protected function processResponse(array $response)
    {
        // Array containing 'headers', 'body', 'response', 'cookies', 'filename'.
        return [
            'body' => $response['body'],
            'headers' => $this->processHeaders($response['headers']),
            'status' => (int) ($response['response']['code'] ?? 200)
        ];
    }

    /**
     * @param array<array-key, string|array> $headers
     * @return array<array-key, array<array-key, string>>
     */
    protected function processHeaders(array $headers = [])
    {
        foreach ($headers as $i => $v) {
            if (!is_array($v)) {
                $headers[$i] = [$v];
            }
        }
        /** @var array<array-key, array<array-key, string>> $headers */
        return $headers;
    }

    /**
     * @param string $url
     * @param string $body
     * @param array $headers
     * @return array{body: string, headers: array<array-key, array<array-key, string>>, status: int}
     * @throws HttpException
     */
    public function post($url, $body, array $headers = array())
    {
        /** @var array{method: string,body: string} $args */
        $args = [
            'method' => 'POST',
            'body' => $body
        ];/** @var WP_Error|array{headers: array<array-key,string|array<array-key, string>>, body:string, response: array, cookies: array, filename: string} $response */
        $response = $this->http->post($url, $args);
        if ($response instanceof WP_Error) {
            throw new HttpException('An unexpected error occurred');
        }
        return $this->processResponse($response);
    }
}
