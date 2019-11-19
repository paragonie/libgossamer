<?php
namespace ParagonIE\Gossamer\Tests\Dummy;

use ParagonIE\Gossamer\DbInterface;
use ParagonIE\Gossamer\GossamerException;

/**
 * Class DummyDB
 *
 * This object exists for the sake of unit testing without a DB driver.
 *
 * @package ParagonIE\Gossamer\Tests\Dummy
 */
class DummyDB implements DbInterface
{
    /** @var string $cacheKey */
    protected $cacheKey;

    /** @var array $state */
    protected $state;

    /**
     * DummyDB constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->state = [];
        if (!isset($this->state[self::TABLE_PROVIDERS])) {
            $this->state[self::TABLE_PROVIDERS] = [];
        }
        if (!isset($this->state[self::TABLE_PUBLIC_KEYS])) {
            $this->state[self::TABLE_PUBLIC_KEYS] = [];
        }
        if (!isset($this->state[self::TABLE_PACKAGES])) {
            $this->state[self::TABLE_PACKAGES] = [];
        }
        if (!isset($this->state[self::TABLE_PACKAGE_RELEASES])) {
            $this->state[self::TABLE_PACKAGE_RELEASES] = [];
        }
        $this->cacheKey = sodium_crypto_shorthash_keygen();
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * @return array
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $provider
     * @param string $publicKey
     * @param array $meta
     * @return bool
     * @throws \SodiumException
     */
    public function appendKey($provider, $publicKey, array $meta = array())
    {
        $providerId = $this->getProviderId($provider);
        $publicKeyId = $this->getPublicKeyId($publicKey, $providerId);
        if ($meta) {
            $this->state[self::TABLE_PUBLIC_KEYS][$publicKeyId]['metadata'] = json_encode($meta);
        }
        return !empty($this->state[self::TABLE_PUBLIC_KEYS][$publicKeyId]);
    }

    /**
     * @param string $provider
     * @param string $publicKey
     * @param array $meta
     * @return bool
     * @throws \SodiumException
     */
    public function revokeKey($provider, $publicKey, array $meta = array())
    {
        $providerId = $this->getProviderId($provider);
        $publicKeyId = $this->getPublicKeyId($publicKey, $providerId);
        $this->state[self::TABLE_PUBLIC_KEYS][$publicKeyId]['revoked'] = true;
        return true;
    }

    /**
     * @param string $provider
     * @param string $package
     * @param string $publicKey
     * @param string $release
     * @param string $signature
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws GossamerException
     * @throws \SodiumException
     */
    public function appendUpdate(
        $provider,
        $package,
        $publicKey,
        $release,
        $signature,
        array $meta = array(),
        $hash = ''
    ) {
        $providerId = $this->getProviderId($provider);
        $publicKeyId = $this->getPublicKeyId($publicKey, $providerId);
        $packageId = $this->getPackageId($package, $providerId);

        $index = $this->hashIndex(self::TABLE_PACKAGE_RELEASES, $packageId . '@@' . $release);
        $this->state[self::TABLE_PACKAGE_RELEASES][$index] = [
            'id' => $packageId,
            'provider' => $providerId,
            'name' => $package,
            'version' => $release,
            'publickey' => $publicKeyId,
            'signature' => $signature,
            'revoked' => false,
            'ledgerhash' => $hash,
            'revokehash' => null,
            'metadata' => json_encode($meta)
        ];
        return true;
    }

    /**
     * @param string $provider
     * @param string $package
     * @param string $publicKey
     * @param string $release
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws \SodiumException
     */
    public function revokeUpdate(
        $provider,
        $package,
        $publicKey,
        $release,
        array $meta = array(),
        $hash = ''
    ) {
        $providerId = $this->getProviderId($provider);
        $packageId = $this->getPackageId($package, $providerId);
        $index = $this->hashIndex(self::TABLE_PACKAGE_RELEASES, $packageId . '@@' . $release);
        $this->state[self::TABLE_PACKAGE_RELEASES][$index]['revoked'] = true;
        $this->state[self::TABLE_PACKAGE_RELEASES][$index]['revokehash'] = $hash;
        return true;
    }


    /**
     * @param string $packageName
     * @param int $providerId
     * @return int|string
     * @throws \SodiumException
     */
    public function getPackageId($packageName, $providerId)
    {
        $index = $this->hashIndex(self::TABLE_PACKAGES, $providerId . '/' . $packageName);
        if (!isset($this->state[self::TABLE_PACKAGES][$index])) {
            $this->state[self::TABLE_PACKAGES][$index] = [
                'id' => $index,
                'provider' => $providerId,
                'name' => $packageName,
            ];
        }
        return $index;
    }

    /**
     * @param string $providerName
     * @return int|string
     * @throws \SodiumException
     */
    public function getProviderId($providerName)
    {
        $index = $this->hashIndex(self::TABLE_PROVIDERS, $providerName);
        if (!isset($this->state[self::TABLE_PROVIDERS][$index])) {
            $this->state[self::TABLE_PROVIDERS][$index] = [
                'id' => $index,
                'name' => $providerName
            ];
        }
        return $index;
    }

    /**
     * @param string $publicKey
     * @param string|int $providerId
     * @return int|string
     * @throws \SodiumException
     */
    public function getPublicKeyId($publicKey, $providerId)
    {
        $index = $this->hashIndex(self::TABLE_PUBLIC_KEYS, $publicKey);
        if (!isset($this->state[self::TABLE_PUBLIC_KEYS][$index])) {
            $this->state[self::TABLE_PUBLIC_KEYS][$index] = [
                'id' => $index,
                'publickey' => $publicKey,
                'revoked' => false,
                'provider' => $providerId
            ];
        }
        return $index;
    }

    /**
     * @param string $table
     * @param string $name
     * @return string
     * @throws \SodiumException
     */
    public function hashIndex($table, $name)
    {
        return sodium_bin2hex(
            sodium_crypto_shorthash(
                $table . ' : ' . $name,
                $this->cacheKey
            )
        );
    }
}
