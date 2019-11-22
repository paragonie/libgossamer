<?php
namespace ParagonIE\Gossamer;

/**
 * Interface LedgerInterface
 * @package ParagonIE\Gossamer
 */
interface LedgerInterface
{
    /**
     * @return self
     */
    public function clearInstances();

    /**
     * @param array<array-key, array{url: string, public-key: string, trust: string}> $instances
     * @return self
     */
    public function populateInstances(array $instances);
}
