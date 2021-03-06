<?php
namespace ParagonIE\Halite\Contract;

use ParagonIE\Halite\Halite;

/**
 * An interface fundamental to all cryptography implementations
 */
interface AsymmetricKeyCryptoInterface
{
    /**
     * Diffie-Hellman, ECDHE, etc.
     *
     * Get a shared secret from a private key you possess and a public key for
     * the intended message recipient
     *
     * @param KeyInterface $privateKey
     * @param KeyInterface $publicKey
     *
     * @return string
     */
    public static function getSharedSecret(
        KeyInterface $privateKey,
        KeyInterface $publicKey
    );

    /**
     * Encrypt a string using asymmetric cryptography
     * Seal then sign
     *
     * @param string       $source Plaintext
     * @param KeyInterface $privateKey
     * @param KeyInterface $publicKey
     * @param boolean      $raw Don't hex encode the output?
     * @return string
     */
    public static function encrypt(
        $source,
        KeyInterface $privateKey,
        KeyInterface $publicKey,
        $raw = false
    );

    /**
     * Decrypt a string using asymmetric cryptography
     * Verify then unseal
     *
     * @param string       $source Ciphertext
     * @param KeyInterface $privateKey
     * @param KeyInterface $publicKey
     * @param boolean      $raw Don't hex decode the input?
     * @return string
     */
    public static function decrypt(
        $source,
        KeyInterface $privateKey,
        KeyInterface $publicKey,
        $raw = false
    );

    /**
     * Encrypt a message with a target users' public key
     *
     * @param string       $source Message to encrypt
     * @param KeyInterface $publicKey
     * @param boolean      $raw Don't hex encode the output?
     *
     * @return string
     */
    public static function seal(
        $source,
        KeyInterface $publicKey,
        $raw = false
    );

    /**
     * Decrypt a sealed message with our private key
     *
     * @param string       $source Encrypted message (string or resource for a file)
     * @param KeyInterface $privateKey
     * @param boolean      $raw Don't hex decode the input?
     *
     * @return string
     */
    public static function unseal(
        $source,
        KeyInterface $privateKey,
        $raw = false
    );

    /**
     * Sign a message with our private key
     *
     * @param string       $message Message to sign
     * @param KeyInterface $privateKey
     * @param string|bool  $encoding
     * @return string Signature (detached)
     *
     */
    public static function sign(
        $message,
        KeyInterface $privateKey,
        $encoding = Halite::ENCODE_BASE64URLSAFE
    );

    /**
     * Verify a signed message with the correct public key
     *
     * @param string       $message Message to verifyn
     * @param KeyInterface $publicKey
     * @param string       $signature
     * @param string       $encoding
     *
     * @return boolean
     */
    public static function verify(
        $message,
        KeyInterface $publicKey,
        $signature,
        $encoding = Halite::ENCODE_BASE64URLSAFE
    );
}
