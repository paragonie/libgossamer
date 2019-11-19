<?php
namespace ParagonIE\Gossamer;
use RangeException;
use SodiumException;
use TypeError;

/**
 * Class Util
 */
class Util
{
    /**
     * @param string &$var
     * @return void
     */
    public static function memzero(&$var)
    {
        try {
            sodium_memzero($var);
        } catch (SodiumException $ex) {
            $var ^= $var;
            unset($var);
        }
    }

    /**
     * @param string $input
     * @param int|null $outLen
     * @return string
     * @throws RangeException
     * @throws SodiumException
     * @throws TypeError
     * @psalm-suppress TooFewArguments
     */
    public static function rawBinary($input, $outLen)
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (!is_string($input)) {
            throw new TypeError('Argument 1 must be a string; ' . gettype($input) . ' given.');
        }
        if (!is_int($outLen)) {
            throw new TypeError('Argument 2 must be an integer; ' . gettype($outLen) . ' given.');
        }
        if (strlen($input) === $outLen) {
            return $input;
        }

        if (strlen(trim($input, '=')) === $outLen * 2 && preg_match('#^[0-9A-Fa-f]+$#', $input)) {
            return sodium_hex2bin($input);
        } elseif (strlen($input) >= ($outLen * 4 / 3)) {
            if (preg_match('#^[0-9A-Za-z+/]+=*$#', $input)) {
                return sodium_base642bin($input, SODIUM_BASE64_VARIANT_ORIGINAL);
            }
            if (preg_match('#^[0-9A-Za-z-_]+=*$#', $input)) {
                return sodium_base642bin($input, SODIUM_BASE64_VARIANT_URLSAFE);
            }
            throw new RangeException(
                "Based on the length of this string, we expected it to be base64-encoded. " .
                "However, it wasn't, so we cannot safely base64-decode it."
            );
        }
        return $input;
    }
}
