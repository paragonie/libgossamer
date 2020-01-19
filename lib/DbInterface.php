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
     * @param array $meta
     * @param string $hash
     * @return bool
     */
    public function appendKey($provider, $publicKey, array $meta = array(), $hash = '');

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
     * @param string $attestation
     * @param array $meta
     * @param string $hash
     * @return bool
     */
    public function attestUpdate(
        $provider,
        $package,
        $release,
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
     * @return array<array-key, string>
     */
    public function getPublicKeysForProvider($providerName);

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
}
