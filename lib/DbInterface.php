<?php
namespace ParagonIE\Gossamer;

interface DbInterface
{
    const TABLE_PROVIDERS = 'gossamer_providers';
    const TABLE_PUBLIC_KEYS = 'gossamer_provider_publickeys';
    const TABLE_PACKAGES = 'gossamer_packages';
    const TABLE_PACKAGE_RELEASES = 'gossamer_package_releases';

    /**
     * @param string $provider
     * @param string $publicKey
     * @param array $meta
     * @return bool
     */
    public function appendKey($provider, $publicKey, array $meta = array());

    /**
     * @param string $provider
     * @param string $publicKey
     * @param array $meta
     * @return bool
     */
    public function revokeKey($provider, $publicKey, array $meta = array());

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
