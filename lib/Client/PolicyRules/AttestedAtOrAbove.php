<?php
namespace ParagonIE\Gossamer\Client\PolicyRules;

use ParagonIE\Gossamer\Client\AttestPolicy;
use ParagonIE\Gossamer\Client\PolicyRuleInterface;

/**
 * Class AttestedAtOrAbove
 *
 * "We have attestations at the $level level (or higher)
 *  from at least $needed of these providers: $providers."
 *
 * @package ParagonIE\Gossamer\Client\PolicyRules
 */
class AttestedAtOrAbove implements PolicyRuleInterface
{
    /** @var string $level */
    private $level;

    /** @var string[] $providers */
    private $providers;

    /** @var int $needed */
    private $needed;

    /**
     * AttestedAtOrAbove constructor.
     *
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
            if ($this->meetsOrExceeds($row['attestation'], $this->level)) {
                $found++;
            }
        }
        return $found >= $this->needed;
    }


    /**
     * @param string $provided
     * @param string $target
     * @return bool
     */
    public function meetsOrExceeds($provided, $target)
    {
        // REPRODUCED is intentionally left out since it's orthogonal:
        $values = array(
            AttestPolicy::SPOT_CHECK => 1,
            AttestPolicy::CODE_REVIEW => 2,
            AttestPolicy::SECURITY_AUDIT => 3
        );
        // Undefined values are treated as not meeting or exceeding:
        if (!array_key_exists($provided, $values) || !array_key_exists($target, $values)) {
            return false;
        }

        // Now we return TRUE if provided >= target
        return $values[$provided] >= $values[$target];
    }
}
