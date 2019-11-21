<?php
namespace ParagonIE\Gossamer;

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
}
