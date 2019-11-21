<?php
namespace ParagonIE\Gossamer;

/**
 * Interface VerifierInterface
 * @package ParagonIE\Gossamer
 */
interface VerifierInterface
{
    /**
     * Does the Quorum agree that a given hash exists?
     *
     * @param string $hash
     * @return bool
     */
    public function quorumAgrees($hash);
}
