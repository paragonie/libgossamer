<?php
namespace ParagonIE\Gossamer\Tests;
use ParagonIE\Gossamer\Http\Guzzle;
use ParagonIE\Gossamer\Verifier\Chronicle;
use PHPUnit\Framework\TestCase;

class ChronicleVerifierTest extends TestCase
{
    /** @var Guzzle */
    private $http;

    /** @var Chronicle $verifier */
    private $verifier;

    public function setUp()
    {
        $this->http = new Guzzle();
        $this->verifier = new Chronicle($this->http);
        $this->verifier->addChronicle(
            'https://php-chronicle-replica.pie-hosted.com/chronicle/replica/_vi6Mgw6KXBSuOFUwYA2H2GEPLawUmjqFJbCCuqtHzGZ',
            'M4f6jYjFTHMOkCFRghC7_Ktu1xHTgfoKg7_RPAzzUdE='
        );
        $this->verifier->addChronicle(
            'https://php-chronicle.pie-hosted.com/chronicle',
            'Bgcc1QfkP0UNgMZuHzi0hC1hA1SoVAyUrskmSkzRw3E='
        );
    }

    /**
     * @throws \ParagonIE\Gossamer\GossamerException
     * @throws \SodiumException
     */
    public function testQuorum()
    {
        $this->verifier->setQuorumMinimum(3);
        $this->assertFalse(
            $this->verifier->quorumAgrees('qRzI9Hpck8sbbRi4I0-8TNkEl8Y8DD0myOpN6gWlAwU=')
        );

        $this->verifier->setQuorumMinimum(1);
        $this->assertTrue(
            $this->verifier->quorumAgrees('qRzI9Hpck8sbbRi4I0-8TNkEl8Y8DD0myOpN6gWlAwU=')
        );
    }
}
