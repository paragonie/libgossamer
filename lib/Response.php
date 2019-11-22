<?php
namespace ParagonIE\Gossamer;

use ParagonIE\Gossamer\Protocol\Packet;
use ParagonIE\Gossamer\Protocol\SignedMessage;

/**
 * Class Response
 */
class Response extends Packet
{
    /**
     * @return array<int, array<string, string>>
     * @throws GossamerException
     */
    protected function decodeChronicleResponse()
    {
        /** @var array<string, array<int, array<string, string>>>|bool $decoded */
        $decoded = json_decode($this->contents, true);
        if (!is_array($decoded)) {
            throw new GossamerException('Could not decode JSON message.');
        }
        if (empty($decoded['results'])) {
            throw new GossamerException('Key "results" not found in JSON message.');
        }
        /** @var array<int, array<string, string>> $results */
        $results = $decoded['results'];

        return $results;
    }

    /**
     * @return array<array-key, SignedMessage>
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function extractAllFromChronicleResponse()
    {
        $results = $this->decodeChronicleResponse();
        $messages = array();

        /** @var array<string, string> $res */
        foreach ($results as $index => $res) {
            $messages []= SignedMessage::init(
                $res['message'],
                $res['signature'],
                $res['provider'],
                isset($res['publickey']) ? $res['publickey'] : ''
            );
        }
        return $messages;
    }

    /**
     * @param int $index
     * @return SignedMessage
     *
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function extractFromChronicleResponse($index = 0)
    {
        $results = $this->decodeChronicleResponse();
        if (empty($results[$index])) {
            throw new GossamerException('Index not found in response: ' . $index . '.');
        }

        /** @var array<string, string> $res */
        $res = $results[$index];

        if (empty($res['contents'])) {
            throw new GossamerException('Key "contents" not found at index ' . $index . '.');
        }
        if (empty($res['signature'])) {
            throw new GossamerException('Key "signature" not found at index ' . $index . '.');
        }
        if (empty($res['publickey'])) {
            throw new GossamerException('Key "publickey" not found at index ' . $index . '.');
        }
        return SignedMessage::init(
            $res['message'],
            $res['signature'],
            $res['provider'],
            isset($res['publickey']) ? $res['publickey'] : ''
        );
    }
}
