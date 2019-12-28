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
        if (!isset($decoded['results'])) {
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
            /** @var array<string, string> $contents */
            $contents = json_decode($res['contents'], true);
            if (!isset($contents['message'])) {
                throw new GossamerException('Key "message" not found in "contents" at index ' . $index . '.');
            }
            if (!isset($contents['signature'])) {
                throw new GossamerException('Key "signature" not found in "contents" at index ' . $index . '.');
            }
            if (!isset($contents['provider'])) {
                throw new GossamerException('Key "provider" not found in "contents" at index ' . $index . '.');
            }
            $signedMessage = SignedMessage::init(
                (string) $contents['message'],
                (string) $contents['signature'],
                (string) $contents['provider'],
                (string) (isset($contents['publickey']) ? $contents['publickey'] : '')
            );
            $signedMessage->setMeta('summary-hash', $res['summary']);
            $messages []= $signedMessage;
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
        /** @var array<string, string> $contents */
        $contents = json_decode($res['contents'], true);
        if (!isset($contents['message'])) {
            throw new GossamerException('Key "message" not found in "contents" at index ' . $index . '.');
        }
        if (!isset($contents['signature'])) {
            throw new GossamerException('Key "signature" not found in "contents" at index ' . $index . '.');
        }
        if (!isset($contents['provider'])) {
            throw new GossamerException('Key "provider" not found in "contents" at index ' . $index . '.');
        }
        $signedMessage = SignedMessage::init(
            (string) $contents['message'],
            (string) $contents['signature'],
            (string) $contents['provider'],
            (string) (isset($contents['publickey']) ? $contents['publickey'] : '')
        );
        $signedMessage->setMeta('summary-hash', $res['summary']);
        return $signedMessage;
    }
}
