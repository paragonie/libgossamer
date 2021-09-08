<?php
namespace ParagonIE\Gossamer\Client;

use Exception;

/**
 * Trait TypeHelperTrait
 * @package ParagonIE\Gossamer\Client
 */
trait TypeHelperTrait
{
    /**
     * Assert a statement is true; otherwise, throw
     *
     * @param bool $statement
     * @param string $errorMessage
     * @param string $errorClass
     * @return void
     * @throws \Exception
     * @psalm-suppress InvalidStringClass
     */
    public function assert($statement, $errorMessage = '', $errorClass = '\\Exception')
    {
        if (!$statement) {
            /** @var \Exception|\Error $error */
            $error = new $errorClass($errorMessage);
            if (!($error instanceof \Exception) && !($error instanceof \Error)) {
                $error = new Exception($errorMessage);
            }
            throw $error;
        }
    }
}
