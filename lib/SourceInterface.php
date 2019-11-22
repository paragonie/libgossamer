<?php
namespace ParagonIE\Gossamer;

/**
 * Interface SourceInterface
 * @package ParagonIE\Gossamer
 */
interface SourceInterface
{
    /**
     * @param string $hash
     * @return Response
     */
    public function getRecordsSince($hash);
}
