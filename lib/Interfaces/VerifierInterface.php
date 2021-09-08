<?php
namespace ParagonIE\Gossamer\Interfaces;

/**
 * Interface VerifierInterface
 * @package ParagonIE\Gossamer
 */
interface VerifierInterface
{
    /**
     * @param string $string
     * @return bool
     */
    public function verify($string);
}
