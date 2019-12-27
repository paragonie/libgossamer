<?php
namespace ParagonIE\Gossamer\Tests\Dummy;

use ParagonIE\Gossamer\Response;
use ParagonIE\Gossamer\SourceInterface;

/**
 * Class DummySource
 * @package ParagonIE\Gossamer\Tests\Dummy
 */
class DummySource implements SourceInterface
{
    private $chronicle;

    public function __construct(DummyChronicle $chronicle = null)
    {
        if (!$chronicle) {
            $chronicle = new DummyChronicle();
        }
        $this->chronicle = $chronicle;
    }

    /**
     * @param string $hash
     * @return \ParagonIE\Gossamer\Response
     * @throws \SodiumException
     */
    public function getRecordsSince($hash = '')
    {
        $records = $this->chronicle->getRecordsSince($hash);
        return new Response(
            json_encode([
                'results' => $records
            ])
        );
    }
}
