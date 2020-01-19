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

    /** @var ?callable $attestCallback */
    protected $attestCallback = null;

    /**
     * @return string
     */
    public function getCheckpointHash()
    {
        return $this->state[self::TABLE_META]['lasthash'];
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function updateMeta($hash = '')
    {
        $this->state[self::TABLE_META]['lasthash'] = $hash;
        return true;
    }

    /**
     * DummyDB constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->state = [];
        if (!isset($this->state[self::TABLE_META])) {
            $this->state[self::TABLE_META] = [
                'version' => self::GOSSAMER_PROTOCOL_VERSION,
                'lasthash' => ''
            ];
        }
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
     * @param bool $limited
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws \SodiumException
     */
    public function appendKey($provider, $publicKey, $limited = false, array $meta = array(), $hash = '')
    {
        $providerId = $this->getProviderId($provider);
        $publicKeyId = $this->getPublicKeyId($publicKey, $providerId);
        if ($meta) {
            $this->state[self::TABLE_PUBLIC_KEYS][$publicKeyId]['metadata'] = json_encode($meta);
        }
        $this->updateMeta($hash);
        return !empty($this->state[self::TABLE_PUBLIC_KEYS][$publicKeyId]);
    }

    /**
     * @param string $provider
     * @param string $publicKey
     * @param array $meta
     * @param string $hash
     * @return bool
     * @throws \SodiumException
     */
    public function revokeKey($provider, $publicKey, array $meta = array(), $hash = '')
    {
        $providerId = $this->getProviderId($provider);
        $publicKeyId = $this->getPublicKeyId($publicKey, $providerId);
        $this->state[self::TABLE_PUBLIC_KEYS][$publicKeyId]['revoked'] = true;
        $this->updateMeta($hash);
        return true;
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function setAttestCallback($callback)
    {
        $this->attestCallback = $callback;
        return $this;
    }

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
    ) {
        if (is_callable($this->attestCallback)) {
            $cb = $this->attestCallback;
            return (bool) $cb($provider, $package, $release, $attestation, $meta, $hash);
        }
        return false;
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
        $this->updateMeta($hash);
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
        $this->updateMeta($hash);
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
     * @return bool
     * @throws \SodiumException
     */
    public function providerExists($providerName)
    {
        $index = $this->hashIndex(self::TABLE_PROVIDERS, $providerName);
        return isset($this->state[self::TABLE_PROVIDERS][$index]);
    }

    /**
     * @param string $providerName
     * @return array<array-key, string>
     * @throws \SodiumException
     */
    public function getPublicKeysForProvider($providerName)
    {
        $providerId = $this->getProviderId($providerName);
        $return = array();
        foreach ($this->state[self::TABLE_PUBLIC_KEYS] as $row) {
            if ($providerId !== $row['provider']) {
                continue;
            }
            $return []= $row['publickey'];
        }
        return $return;
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
