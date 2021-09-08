<?php
namespace ParagonIE\Gossamer\Interfaces;

/**
 * Interface HttpInterface
 * @package ParagonIE\Gossamer
 */
interface HttpInterface
{
    /**
     * @param string $url
     * @return array{body: string, headers: array<array-key, array<array-key, string>>, status: int}
     */
    public function get($url);

    /**
     * @param string $url
     * @param string $body
     * @param array $headers
     * @return array{body: string, headers: array<array-key, array<array-key, string>>, status: int}
     */
    public function post($url, $body, array $headers = array());
}
