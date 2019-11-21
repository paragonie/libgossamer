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
     * @psalm-suppress RedundantConditionGivenDocblockType
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

    /**
     * @param int $min
     * @param int $max
     * @return int
     * @throws GossamerException
     */
    public static function randomInt($min, $max)
    {
        try {
            return random_int($min, $max);
        } catch (\Error $ex) {
            throw new GossamerException('RNG Failure', 0, $ex);
        } catch (\Exception $ex) {
            throw new GossamerException('RNG failure', 0, $ex);
        }
    }

    /**
     * Shuffle an array using a CSPRNG
     *
     * @link https://paragonie.com/b/JvICXzh_jhLyt4y3
     *
     * @param array<array-key, mixed> &$array reference to an array
     * @return void
     * @throws GossamerException
     * @psalm-suppress MixedAssignment
     */
    public static function secureShuffle(&$array)
    {
        $size = count($array);
        $keys = array_keys($array);
        for ($i = $size - 1; $i > 0; --$i) {
            $r = self::randomInt(0, $i);
            if ($r !== $i) {
                /** @var array<array-key, mixed> $temp */
                $temp = $array[$keys[$r]];
                $array[$keys[$r]] = $array[$keys[$i]];
                $array[$keys[$i]] = $temp;
            }
        }
        // Reset indices:
        $array = array_values($array);
    }
}
