<?php
namespace ParagonIE\Gossamer;

interface DbInterface
{
    const GOSSAMER_PROTOCOL_VERSION = '1.0.0';
    const TABLE_META = 'gossamer_meta';
    const TABLE_PROVIDERS = 'gossamer_providers';
    const TABLE_PUBLIC_KEYS = 'gossamer_provider_publickeys';
    const TABLE_PACKAGES = 'gossamer_packages';
    const TABLE_PACKAGE_RELEASES = 'gossamer_package_releases';
    const TABLE_ATTESTATIONS = 'gossamer_package_release_attestations';

    /**
     * @return string
     */
    public function getCheckpointHash();

    /**
     * @param string $hash
     * @return bool
     */
    public function updateMeta($hash = '');

    /**
     * @param string $provider
     * @param string $publicKey
     * @param bool $limited
     * @param string $purpose
     * @param array $meta
     * @param string $hash
     * @return bool
     */
    public function appendKey(
        $provider,
        $publicKey,
        $limited = false,
        $purpose = '',
        array $meta = array(),
        $hash = ''
    );

    /**
     * @param string $provider
     * @param string $publicKey
     * @param array $meta
     * @param string $hash
     * @return bool
     */
    public function revokeKey($provider, $publicKey, array $meta = array(), $hash = '');

    /**
     * @param string $provider
     * @param string $package
     * @param string $publicKey
     * @param string $release
     * @param string $signature
     * @param array $meta
     * @param string $hash
     * @return bool
     */
    public function appendUpdate(
        $provider,
        $package,
        $publicKey,
        $release,
        $signature,
        array $meta = array(),
        $hash = ''
    );

    /**
     * @param string $provider
     * @param string $package
     * @param string $release
     * @param string $attestor
     * @param string $attestation
     * @param array $meta
     * @param string $hash
     * @return bool
     */
    public function attestUpdate(
        $provider,
        $package,
        $release,
        $attestor,
        $attestation,
        array $meta = array(),
        $hash = ''
    );

    /**
     * @param string $provider
     * @param string $package
     * @param string $publicKey
     * @param string $release
     * @param array $meta
     * @param string $hash
     * @return bool
     */
    public function revokeUpdate(
        $provider,
        $package,
        $publicKey,
        $release,
        array $meta = array(),
        $hash = ''
    );

    /**
     * @param string $providerName
     * @return bool
     */
    public function providerExists($providerName);

    /**
     * @param string $providerName
     * @param ?bool $limited
     * @param ?string $purpose
     * @return array<array-key, string>
     */
    public function getPublicKeysForProvider($providerName, $limited = null, $purpose = null);

    /**
     * @param string $packageName
     * @param int $providerId
     * @return int
     * @throws GossamerException
     */
    public function getPackageId($packageName, $providerId);

    /**
     * @param string $providerName
     * @return int
     */
    public function getProviderId($providerName);

    /**
     * @param string $publicKey
     * @param int $providerId
     * @return int
     * @throws GossamerException
     */
    public function getPublicKeyId($publicKey, $providerId);

    /**
     * Is the "limited" flag set to TRUE on this key?
     *
     * @param string $providerName
     * @param string $publicKey
     * @return bool
     */
    public function isKeyLimited($providerName, $publicKey);

    /**
     * @param string $providerName
     * @param string $packageName
     * @param string $version
     * @param int $offset          For supporting multiple releases with the same name (if some were revoked)
     * @return array
     */
    public function getRelease($providerName, $packageName, $version, $offset = 0);

    /**
     * @param string $providerName
     * @param string $packageName
     * @param string $version
     * @return array{attestor: string, attestation: string, ledgerhash: string}[]
     */
    public function getAttestations($providerName, $packageName, $version);
}
