<?php
namespace ParagonIE\Gossamer\Client\TrustMode;

use ParagonIE\Gossamer\Client\TrustModeInterface;
use ParagonIE\Gossamer\Client\TypeHelperTrait;
use ParagonIE\Gossamer\Client\UpdateFile;
use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\HttpInterface;

/**
 * Class FederatedTrust
 *
 * Federated trust means outsourcing this to a Gossamer server.
 * @link https://github.com/paragonie/gossamer-server
 *
 * @package ParagonIE\Gossamer\Client\TrustMode
 */
class FederatedTrust implements TrustModeInterface
{
    use TypeHelperTrait;

    /** @var HttpInterface $http */
    private $http;

    /** @var string $url */
    private $url;

    /** @var string $serverPublicKey */
    private $serverPublicKey;

    /**
     * FederatedTrust constructor.
     *
     * @param string $url
     * @param string $serverPublicKey
     */
    public function __construct(HttpInterface $http, $url, $serverPublicKey)
    {
        $this->http = $http;
        $this->url = $url;
        $this->serverPublicKey = $serverPublicKey;
    }

    /**
     * Obtain update info from the remote HTTP API.
     *
     * @param string $provider
     * @param string $package
     * @param string $version
     * @return UpdateFile
     * @throws GossamerException
     */
    public function getUpdateInfo($provider, $package, $version)
    {
        /** @var array{publickey: string, signature: string, metadata:array} $data */
        $data = $this->getUpdateInfoHttp($provider, $package, $version);
        // Return an object that encapsulates this state.
        return new UpdateFile(
            (string) $data['publickey'],
            (string) $data['signature'],
            (array) $data['metadata'],
            $this->getAttestationsHttp($provider, $package, $version)
        );
    }

    /**
     * Get the update info from a Gossamer server.
     *
     * @param string $provider
     * @param string $package
     * @param string $version
     * @return array{publickey: string, signature: string, metadata:array}
     * @throws GossamerException
     */
    protected function getUpdateInfoHttp($provider, $package, $version)
    {
        // Fetch the HTTP response.
        /** @var array{body: string} $response */
        $response = $this->http->get(
            $this->url . '/release/' . $provider . '/' . $package . '/' . $version
        );

        // Decode the response body as a JSON object.
        /** @var array<array-key, array{publickey: string, signature: string, metadata:array}> $decoded */
        $decoded = json_decode($response['body'], true);
        if (empty($decoded)) {
            throw new GossamerException('No update file available');
        }
        // Get the top element if there are multiple. (This is the non-revoked version.)
        return array_shift($decoded);
    }

    /**
     * Get attestations for this particular update from the Gossamer server.
     *
     * @param string $provider
     * @param string $package
     * @param string $version
     * @return array{attestor: string, attestation: string, ledgerhash: string}[]
     */
    protected function getAttestationsHttp($provider, $package, $version)
    {
        /** @var array{body: string} $response */
        $response = $this->http->get(
            $this->url . '/release/' . $provider . '/' . $package . '/' . $version
        );

        // Decode the response body as a JSON object.
        /**
         * @var array{attestor: string, attestation: string, ledgerhash: string}[] $decoded
         */
        $decoded = json_decode($response['body'], true);
        if (empty($decoded)) {
            return array();
        }
        return $decoded;
    }
}
