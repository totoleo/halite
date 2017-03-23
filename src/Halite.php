<?php
namespace ParagonIE\Halite;

use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base32Hex;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\ConstantTime\Hex;
use ParagonIE\Halite\Alerts\InvalidType;

/**
 * This is just an abstract class that hosts some constants
 *
 * Version Tag Info:
 *
 *  \x31\x41 => 3.141 (approx. pi)
 *  \x31\x42 => 3.142 (approx. pi)
 *  Because pi is the symbol we use for Paragon Initiative Enterprises
 *  \x00\x07 => version 0.07
 */
abstract class Halite
{
    const VERSION = '1.0.0';

    const HALITE_VERSION_KEYS = "\x31\x40\x01\x00";

    const HALITE_VERSION_FILE = "\x31\x41\x01\x00";

    const HALITE_VERSION = "\x31\x42\x01\x00";

    const VERSION_TAG_LEN = 4;

    const ENCODE_HEX = 'hex';

    const ENCODE_BASE32 = 'base32';

    const ENCODE_BASE32HEX = 'base32hex';

    const ENCODE_BASE64 = 'base64';

    const ENCODE_BASE64URLSAFE = 'base64urlsafe';

    /**
     * Select which encoding/decoding function to use.
     *
     * @internal
     * @param mixed $chosen
     * @param bool  $decode
     * @return callable (array or string)
     * @throws InvalidType
     */
    public static function chooseEncoder($chosen, $decode = false)
    {
        if ($chosen === true) {
            return null;
        } elseif ($chosen === false) {
            return \implode(
                '::',
                [
                    Hex::class,
                    $decode ? 'decode' : 'encode',
                ]
            );
        } elseif ($chosen === self::ENCODE_BASE32) {
            return \implode(
                '::',
                [
                    Base32::class,
                    $decode ? 'decode' : 'encode',
                ]
            );
        } elseif ($chosen === self::ENCODE_BASE32HEX) {
            return \implode(
                '::',
                [
                    Base32Hex::class,
                    $decode ? 'decode' : 'encode',
                ]
            );
        } elseif ($chosen === self::ENCODE_BASE64) {
            return \implode(
                '::',
                [
                    Base64::class,
                    $decode ? 'decode' : 'encode',
                ]
            );
        } elseif ($chosen === self::ENCODE_BASE64URLSAFE) {
            return \implode(
                '::',
                [
                    Base64UrlSafe::class,
                    $decode ? 'decode' : 'encode',
                ]
            );
        } elseif ($chosen === self::ENCODE_HEX) {
            return \implode(
                '::',
                [
                    Hex::class,
                    $decode ? 'decode' : 'encode',
                ]
            );
        }
        throw new InvalidType(
            'Illegal value for encoding choice.'
        );
    }
}
