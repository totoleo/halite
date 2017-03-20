<?php
namespace ParagonIE\Halite;

use ParagonIE\Halite\Asymmetric\SignatureSecretKey;
use ParagonIE\Halite\Asymmetric\SignaturePublicKey;
use ParagonIE\Halite\Alerts as CryptoException;

/**
 * Describes a pair of secret and public keys
 */
final class SignatureKeyPair extends KeyPair
{
    /**
     *
     * Pass it a secret key, it will automatically generate a public key
     *
     * @param ...Key $keys
     */
    public function __construct(array $keys)
    {
        switch (\count($keys)) {
            /**
             * If we received two keys, it must be an asymmetric secret key and
             * an asymmetric public key, in either order.
             */
            case 2:
                if (!$keys[0]->isAsymmetricKey() || !$keys[1]->isAsymmetricKey()) {
                    throw new CryptoException\InvalidKey(
                        'Only keys intended for asymmetric cryptography can be used in a KeyPair object'
                    );
                }
                if ($keys[0]->isPublicKey()) {
                    if ($keys[1]->isPublicKey()) {
                        throw new CryptoException\InvalidKey(
                            'Both keys cannot be public keys'
                        );
                    }
                    // $keys[0] is public, $keys[1] is secret
                    $this->secret_key = $keys[1] instanceof SignatureSecretKey
                        ? $keys[1]
                        : new SignatureSecretKey($keys[1]->get());
                    /**
                     * Let's use the secret key to calculate the *correct*
                     * public key. We're effectively discarding $keys[0] but
                     * this ensures correct usage down the line.
                     */
                    if (!$this->secret_key->isSigningKey()) {
                        throw new CryptoException\InvalidKey(
                            'Must be a signing key pair'
                        );
                    }
                    // crypto_sign - Ed25519
                    $pub              = \Sodium\crypto_sign_publickey_from_secretkey(
                        $keys[1]->get()
                    );
                    $this->public_key = new SignaturePublicKey($pub, true);
                    \Sodium\memzero($pub);
                } elseif ($keys[1]->isPublicKey()) {
                    // We can deduce that $keys[0] is a secret key
                    $this->secret_key = $keys[0] instanceof SignatureSecretKey
                        ? $keys[0]
                        : new SignatureSecretKey($keys[0]->get());
                    /**
                     * Let's use the secret key to calculate the *correct*
                     * public key. We're effectively discarding $keys[0] but
                     * this ensures correct usage down the line.
                     */
                    if (!$this->secret_key->isSigningKey()) {
                        throw new CryptoException\InvalidKey(
                            'Must be a signing key pair'
                        );
                    }
                    // crypto_sign - Ed25519
                    $pub              = \Sodium\crypto_sign_publickey_from_secretkey(
                        $keys[0]->get()
                    );
                    $this->public_key = new SignaturePublicKey($pub, true);
                    \Sodium\memzero($pub);
                } else {
                    throw new CryptoException\InvalidKey(
                        'Both keys cannot be secret keys'
                    );
                }
                break;
            /**
             * If we only received one key, it must be an asymmetric secret key!
             */
            case 1:
                if (!$keys[0]->isAsymmetricKey()) {
                    throw new CryptoException\InvalidKey(
                        'Only keys intended for asymmetric cryptography can be used in a KeyPair object'
                    );
                }
                if ($keys[0]->isPublicKey()) {
                    throw new CryptoException\InvalidKey(
                        'We cannot generate a valid keypair given only a public key; we can given only a secret key, however.'
                    );
                }
                $this->secret_key = $keys[0] instanceof SignatureSecretKey
                    ? $keys[0]
                    : new SignatureSecretKey(
                        $keys[0]->get(),
                        $keys[0]->isSigningKey()
                    );

                if (!$this->secret_key->isSigningKey()) {
                    throw new CryptoException\InvalidKey(
                        'Must be a signing key pair'
                    );
                }
                // We need to calculate the public key from the secret key
                $pub              = \Sodium\crypto_sign_publickey_from_secretkey(
                    $keys[0]->get()
                );
                $this->public_key = new SignaturePublicKey($pub, true);
                \Sodium\memzero($pub);
                break;
            default:
                throw new \InvalidArgumentException(
                    'Halite\\EncryptionKeyPair expects 1 or 2 keys'
                );
        }
    }

    /**
     * Hide this from var_dump(), etc.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'privateKey' => '**protected**',
            'publicKey'  => '**protected**',
        ];
    }

    /**
     * Get a Key object for the public key
     *
     * @return Key
     */
    public function getPublicKey()
    {
        return $this->public_key;
    }

    /**
     * Get a Key object for the secret key
     *
     * @return Key
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }
}
