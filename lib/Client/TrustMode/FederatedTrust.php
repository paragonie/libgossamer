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

    /**
     * FederatedTrust constructor.
     *
     * @param HttpInterface $http
     * @param string $url
     */
    public function __construct(HttpInterface $http, $url)
    {
        $this->http = $http;
        $this->url = $url;
    }

    /**
     * @param string $provider
     * @param ?string $purpose
     * @return array<array-key, string>
     * @throws GossamerException
     */
    public function getVerificationKeys($provider, $purpose = null)
    {
        return $this->getVerificationKeysHttp($provider, $purpose);
    }

    /**
     * Obtain update info from the remote HTTP API.
     *
     * @param string $provider
     * @param string $package
     * @param string $version
     * @param ?string $artifact
     * @return UpdateFile
     * @throws GossamerException
     */
    public function getUpdateInfo($provider, $package, $version, $artifact = null)
    {
        /** @var array{publickey: string, signature: string, metadata:array} $data */
        $data = $this->getUpdateInfoHttp($provider, $package, $version, $artifact);
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
     * @param ?string $purpose
     * @return array<array-key, string>
     * @throws GossamerException
     */
    protected function getVerificationKeysHttp($provider, $purpose = null)
    {
        // Fetch the HTTP response.
        /** @var array{body: string} $response */
        $response = $this->http->get(
            $this->url . '/verification-keys/' . $provider
        );

        // Decode the response body as a JSON object.
        /** @var array{publickey: string, limited: bool, purpose: ?string, ledgerhash: string, metadata:array}[] $decoded */
        $decoded = json_decode($response['body'], true);
        if (empty($decoded)) {
            throw new GossamerException('No update file available');
        }

        // We only need the public key:
        /** @var array<array-key, string> $verificationKeys */
        $verificationKeys = array();
        foreach ($decoded as $row) {
            if (is_null($row['purpose']) && is_null($purpose)) {
                // We want NULL purposes only (default behavior):
                $verificationKeys[] = $row['publickey'];
            } elseif (!is_null($row['purpose']) && !is_null($purpose)) {
                // Both are non-NULL, so we must match in our inclusion filter:
                if (hash_equals($row['purpose'], $purpose)) {
                    $verificationKeys[] = $row['publickey'];
                }
            }
        }
        return $verificationKeys;
    }

    /**
     * Get the update info from a Gossamer server.
     *
     * @param string $provider
     * @param string $package
     * @param string $version
     * @param ?string $artifact
     * @return array{publickey: string, signature: string, metadata:array}
     * @throws GossamerException
     */
    protected function getUpdateInfoHttp($provider, $package, $version, $artifact = null)
    {
        // Fetch the HTTP response.
        /** @var array{body: string} $response */
        $response = $this->http->get(
            $this->url . '/release/' . $provider . '/' . $package . '/' . $version
        );

        // Decode the response body as a JSON object.
        /** @var array<array-key, array{artifact: string, publickey: string, signature: string, metadata:array}> $decoded */
        $decoded = json_decode($response['body'], true);
        // If we have an artifact type, filter only the updates
        if (!is_null($artifact) && !empty($decoded)) {
            foreach ($decoded as $key => $row) {
                if ($row['artifact'] !== $artifact) {
                    unset($decoded[$key]);
                }
            }
        }
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
     * @param ?string $artifact
     * @return array{attestor: string, attestation: string, ledgerhash: string}[]
     */
    protected function getAttestationsHttp($provider, $package, $version, $artifact = null)
    {
        /** @var array{body: string} $response */
        $response = $this->http->get(
            $this->url . '/release/' . $provider . '/' . $package . '/' . $version
        );

        // Decode the response body as a JSON object.
        /**
         * @var array{artifact: string, attestor: string, attestation: string, ledgerhash: string}[] $decoded
         */
        $decoded = json_decode($response['body'], true);
        // If we have an artifact type, filter only the updates
        if (!is_null($artifact) && !empty($decoded)) {
            foreach ($decoded as $key => $row) {
                if ($row['artifact'] !== $artifact) {
                    unset($decoded[$key]);
                }
            }
        }
        if (empty($decoded)) {
            return array();
        }
        return $decoded;
    }
}
