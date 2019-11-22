<?php
namespace ParagonIE\Gossamer;

use ParagonIE\Gossamer\Protocol\SignedMessage;

/**
 * Interface ScribeInterface
 *
 * Used for publishing messages onto the configured ledger.
 *
 * @package ParagonIE\Gossamer
 */
interface ScribeInterface
{
    /**
     * Write a record onto the configured ledger.
     *
     * @param SignedMessage $message
     * @return bool
     */
    public function publish(SignedMessage $message);
}
