<?php
namespace ParagonIE\Gossamer;

use ParagonIE\Gossamer\Protocol\SignedMessage;

/**
 * Interface LedgerVerifierInterface
 * @package ParagonIE\Gossamer
 */
interface LedgerVerifierInterface extends LedgerInterface
{
    /**
     * @param SignedMessage $signedMessage
     * @return bool
     */
    public function signedMessageFound(SignedMessage $signedMessage);

    /**
     * @param string $hash
     * @return bool
     */
    public function verify($hash);
}
