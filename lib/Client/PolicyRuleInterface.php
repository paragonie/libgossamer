<?php
namespace ParagonIE\Gossamer\Client;

/**
 * Interface PolicyRuleInterface
 * @package ParagonIE\Gossamer\Client
 */
interface PolicyRuleInterface
{
    /**
     * @param array{attestor: string, attestation: string, ledgerhash: string}[] $attestations
     * @return bool
     */
    public function passes(array $attestations);
}
