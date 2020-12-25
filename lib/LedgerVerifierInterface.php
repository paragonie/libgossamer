<?php
namespace ParagonIE\Gossamer;

use ParagonIE\Gossamer\Protocol\SignedMessage;

interface LedgerVerifierInterface extends VerifierInterface, LedgerInterface
{
    /**
     * @param SignedMessage $signedMessage
     * @return bool
     */
    public function signedMessageFound(SignedMessage $signedMessage);
}
