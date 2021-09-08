<?php
namespace ParagonIE\Gossamer\Interfaces;

use ParagonIE\Gossamer\Response;

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
