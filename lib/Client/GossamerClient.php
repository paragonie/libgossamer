<?php
namespace ParagonIE\Gossamer\Client;

use ParagonIE\Gossamer\Release\Common;

/**
 * Class GossamerClient
 * @package ParagonIE\Gossamer\Client
 */
class GossamerClient
{
    use TypeHelperTrait;

    /** @var int $alg */
    private $alg;

    /** @var AttestPolicy $policy */
    private $policy;

    /** @var TrustModeInterface $trust */
    private $trust;

    /**
     * GossamerClient constructor.
     * @param TrustModeInterface $mode
     * @param ?AttestPolicy $policy
     * @param int $alg
     */
    public function __construct(
        TrustModeInterface $mode,
        AttestPolicy $policy = null,
        $alg = Common::SIGN_ALG_ED25519_BLAKE2B
    ) {
        $this->alg = $alg;
        $this->trust = $mode;
        if (is_null($policy)) {
            $policy = new AttestPolicy(); // No rules
        }
        $this->policy = $policy;
    }

    /**
     * Get the data about a current update file
     *
     * @param string ...$args
     * @return UpdateFile
     * @throws \Exception
     */
    public function getUpdate(...$args)
    {
        /* We have to do this for type strictness on PHP 5.6 (for WordPress): */
        $this->assert(is_string($args[0]), 'Argument 1 must be a string');
        $this->assert(is_string($args[1]), 'Argument 2 must be a string');
        if (count($args) === 3) {
            $this->assert(is_string($args[2]), 'Argument 3 must be a string');
            return $this->getUpdateActual(...$args);
        } elseif (count($args) === 2) {
            $pieces = explode('/', $args[0]);
            return $this->getUpdateActual($pieces[0], $pieces[1], $args[1]);
        }
        throw new \InvalidArgumentException('getUpdate() expects 2 or 3 string parameters');
    }

    /**
     * @param string $provider
     * @return string[]
     */
    public function getVerificationKeys($provider)
    {
        return $this->trust->getVerificationKeys($provider);
    }

    /**
     * Gets information about a Gossamer update from the trusted source, depending
     * on your configured Trust Mode.
     *
     * (local -> SQL database, federated -> HTTP server)
     *
     * @param string $provider
     * @param string $package
     * @param string $version
     * @return UpdateFile
     */
    private function getUpdateActual($provider, $package, $version)
    {
        return $this->trust->getUpdateInfo($provider, $package, $version)
                ->setAlgorithm($this->alg)
                ->setAttestPolicy($this->policy);
    }
}
