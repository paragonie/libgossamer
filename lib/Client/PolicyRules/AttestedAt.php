<?php
namespace ParagonIE\Gossamer\Client\PolicyRules;

use ParagonIE\Gossamer\Client\PolicyRuleInterface;

/**
 * Class AttestedAt
 *
 * "We have attestations at the $level level from at
 *  least $needed of these providers: $providers."
 *
 * @package ParagonIE\Gossamer\Client\PolicyRules
 */
class AttestedAt implements PolicyRuleInterface
{
    /** @var string $level */
    private $level;

    /** @var string[] $providers */
    private $providers;

    /** @var int $needed */
    private $needed;

    /**
     * AttestedAt constructor.
     * @param string $level
     * @param array<array-key, string> $providers
     * @param int $needed
     */
    public function __construct($level, $providers, $needed = 1)
    {
        $this->level = $level;
        $this->providers = $providers;
        $this->needed = $needed;
    }

    /**
     * @param array{attestor: string, attestation: string, ledgerhash: string}[] $attestations
     * @return bool
     */
    public function passes(array $attestations)
    {
        $found = 0;
        foreach ($attestations as $row) {
            if (!\in_array($row['attestor'], $this->providers, true)) {
                // This attestor is irrelevant to this policy.
                continue;
            }
            if (\hash_equals($row['attestation'], $this->level)) {
                $found++;
            }
        }
        return $found >= $this->needed;
    }
}
