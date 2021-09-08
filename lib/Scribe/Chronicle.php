<?php
namespace ParagonIE\Gossamer\Scribe;

use ParagonIE\Gossamer\Interfaces\HttpInterface;
use ParagonIE\Gossamer\Protocol\SignedMessage;
use ParagonIE\Gossamer\Interfaces\ScribeInterface;
use ParagonIE\Gossamer\Util;
use SodiumException;

/**
 * Class Chronicle
 * @package ParagonIE\Gossamer\Scribe
 */
class Chronicle implements ScribeInterface
{
    const CLIENT_ID_HEADER = 'Chronicle-Client-Key-ID';
    const BODY_SIGNATURE_HEADER = 'Body-Signature-Ed25519';

    /** @var string $baseUrl */
    private $baseUrl;

    /** @var string $clientId */
    private $clientId;

    /** @var string $clientSecretKey */
    private $clientSecretKey;

    /** @var HttpInterface $http */
    private $http;

    /** @var string $serverPublicKey */
    private $serverPublicKey;

    /**
     * Chronicle constructor.
     *
     * @param HttpInterface $http
     * @param string $baseUrl
     * @param string $clientId
     * @param string $clientSecretKey
     * @param string $serverPublicKey
     */
    public function __construct(
        HttpInterface $http,
        $baseUrl,
        $clientId,
        $clientSecretKey,
        $serverPublicKey
    ) {
        $this->http = $http;
        $this->baseUrl = $baseUrl;
        $this->clientId = $clientId;
        $this->clientSecretKey = $clientSecretKey;
        $this->serverPublicKey = $serverPublicKey;
    }

    /**
     * Sign the message body.
     * Returns the string to place in the HTTP message header.
     *
     * @param string $serialized
     * @return string
     * @throws SodiumException
     */
    public function signMessageBody($serialized)
    {
        return sodium_bin2base64(
            sodium_crypto_sign_detached(
                $serialized,
                Util::rawBinary($this->clientSecretKey, 64)
            ),
            SODIUM_BASE64_VARIANT_URLSAFE
        );
    }

    /**
     * Did the Chronicle return a valid response?
     *
     * @param int $status
     * @param string $body
     * @param array<array-key, array<array-key, string>> $headers
     * @return bool
     * @throws SodiumException
     * @psalm-suppress DocblockTypeContradiction
     */
    public function responseValid($status, $body, array $headers = array())
    {
        if ($status < 200 || $status >= 300) {
            // Bad HTTP status.
            return false;
        }
        if (!array_key_exists(self::BODY_SIGNATURE_HEADER, $headers)) {
            // No signature header provided.
            return false;
        }
        // Coerce to an iterable...
        if (!is_array($headers[self::BODY_SIGNATURE_HEADER])) {
            $headers[self::BODY_SIGNATURE_HEADER] = array($headers[self::BODY_SIGNATURE_HEADER]);
        }
        /** @var string $header */
        foreach ($headers[self::BODY_SIGNATURE_HEADER] as $header) {
            if (sodium_crypto_sign_verify_detached(
                Util::rawBinary($header, 64),
                $body,
                Util::rawBinary($this->serverPublicKey, 32)
            )) {
                return true;
            }
        }
        return false;
    }

    /**
     * Publish a new record to the Chronicle.
     *
     * @param SignedMessage $message
     * @return bool
     * @throws SodiumException
     */
    public function publish(SignedMessage $message)
    {
        $serialized = $message->toString();
        $signature = $this->signMessageBody($serialized);

        /** @var array{body: string, headers: array<array-key, array<array-key, string>>, status: int} $response */
        $response = $this->http->post(
            $this->baseUrl . '/publish',
            $serialized,
            [
                self::CLIENT_ID_HEADER => $this->clientId,
                self::BODY_SIGNATURE_HEADER => $signature
            ]
        );
        return $this->responseValid(
            $response['status'],
            $response['body'],
            $response['headers']
        );
    }
}
