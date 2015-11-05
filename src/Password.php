<?php
namespace ParagonIE\Halite;

use \ParagonIE\Halite\Symmetric\Crypto;
use \ParagonIE\Halite\Symmetric\EncryptionKey;

/**
 * Secure password storage and secure password verification
 */
abstract class Password implements \ParagonIE\Halite\Contract\PasswordInterface
{
    /**
     * Hash then encrypt a password
     * 
     * @param string $password         - The user's password
     * @param EncryptionKey $secret_key - The master key for all passwords
     * @return string
     */
    public static function hash($password, EncryptionKey $secret_key)
    {
        // First, let's calculate the hash
        $hashed = \Sodium\crypto_pwhash_scryptsalsa208sha256_str(
            $password,
            \Sodium\CRYPTO_PWHASH_SCRYPTSALSA208SHA256_OPSLIMIT_INTERACTIVE,
            \Sodium\CRYPTO_PWHASH_SCRYPTSALSA208SHA256_MEMLIMIT_INTERACTIVE
        );
        
        // Now let's encrypt the result
        return Crypto::encrypt($hashed, $secret_key);
    }

    /**
     * Decrypt then verify a password
     * 
     * @param string $password          - The user-provided password
     * @param string $stored            - The encrypted password hash
     * @param EncryptionKey $secret_key  - The master key for all passwords
     * @return boolean
     */
    public static function verify($password, $stored, EncryptionKey $secret_key)
    {
        // First let's decrypt the hash
        $hash_str = Crypto::decrypt($stored, $secret_key);
        // Upon successful decryption, verify the password is correct
        return \Sodium\crypto_pwhash_scryptsalsa208sha256_str_verify($hash_str, $password);
    }
}
