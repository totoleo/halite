<?php
declare(strict_types=1);
namespace ParagonIE\Halite\Structure;

use \ParagonIE\Halite\Alerts\InvalidKey;
use \ParagonIE\Halite\Key;
use \ParagonIE\Halite\Symmetric\AuthenticationKey;
use \ParagonIE\Halite\Asymmetric\SignaturePublicKey;

/**
 * An implementation of a Merkle hash tree, built on the BLAKE2b hash function
 * (provided by libsodium)
 */
class MerkleTree
{
    const MERKLE_LEAF =   "\x01";
    const MERKLE_BRANCH = "\x00";

    private $key = '';
    private $root = '';
    private $nodes = [];
    
    /**
     * Instantiate a Merkle tree
     * 
     * @param Node[] $nodes
     */
    public function __construct(Node ...$nodes)
    {
        $this->nodes = $nodes;
        $this->root = $this->calculateRoot();
    }
    
    /**
     * 
     * @param bool $raw - Do we want a raw string instead of a hex string?
     * 
     * @return string
     */
    public function getRoot(bool $raw = false): string
    {
        return $raw
            ? $this->root
            : \Sodium\bin2hex($this->root);
    }
    
    /**
     * Merkle Trees are immutable. Return a replacement with extra nodes.
     *
     * @param Node[] $nodes
     * @return MerkleTree
     */
    public function getExpandedTree(Node ...$nodes): MerkleTree
    {
        $thisTree = $this->nodes;
        foreach ($nodes as $node) {
            $thisTree []= $node;
        }
        return (new MerkleTree(...$thisTree))
            ->setKeyFromString($this->key);
    }

    /**
     * Get the number of actual nodes
     *
     * @return int
     */
    public function getNodeCount(): int
    {
        return \count($this->nodes);
    }

    /**
     * Let's go ahead and round up to the nearest multiple of 2
     *
     * @param int $inputSize
     * @return int
     */
    public static function getSizeRoundedUp(int $inputSize): int
    {
        $order = 1;
        while($order < $inputSize) {
            $order <<= 1;
        }
        return $order;
    }
    /**
     * Set the key for calculating this Merkle root
     *
     * @param string $key
     * @return self
     */
    public function setKeyFromString(string $key)
    {
        $this->key = $key;
        $this->root = $this->calculateRoot();
        return $this;
    }

    /**
     * Set the key for calculating this Merkle root
     *
     * @param Key $key AuthenticationKey|SignaturePublicKey
     *
     * @throws InvalidKey
     * @return self
     */
    public function setKey(Key $key)
    {
        if ($key instanceof AuthenticationKey || $key instanceof SignaturePublicKey) {
            $this->key = $key->getRawKeyMaterial();
        } else {
            throw new InvalidKey('KeyedMerkleTree expects an AuthenticationKey or a SignaturePublicKey.');
        }
        $this->root = $this->calculateRoot();
        return $this;
    }
    /**
     * Calculate the Merkle root, taking care to distinguish between
     * leaves and branches (0x01 for the nodes, 0x00 for the branches)
     * to protect against second-preimage attacks
     *
     * @return string
     */
    protected function calculateRoot(): string
    {
        $size = \count($this->nodes);
        $order = self::getSizeRoundedUp($size);
        $hash = [];
        $debug = [];
        // Population (Use self::MERKLE_LEAF as a prefix)
        for ($i = 0; $i < $order; ++$i) {
            if ($i >= $size) {
                $hash[$i] = self::MERKLE_LEAF . $this->nodes[$size - 1]->getHash(true);
                $debug[$i] = \Sodium\bin2hex($hash[$i]);
            } else {
                $hash[$i] = self::MERKLE_LEAF . $this->nodes[$i]->getHash(true);
                $debug[$i] = \Sodium\bin2hex($hash[$i]);
            }
        }
        // Calculation (Use self::MERKLE_BRANCH as a prefix)
        do {
            $tmp = [];
            $j = 0;
            for ($i = 0; $i < $order; $i += 2) {
                if (empty($hash[$i + 1])) {
                    $tmp[$j] = \Sodium\crypto_generichash(
                        self::MERKLE_BRANCH . $hash[$i] . $hash[$i],
                        $this->key
                    );
                } else {
                    $tmp[$j] = \Sodium\crypto_generichash(
                        self::MERKLE_BRANCH . $hash[$i] . $hash[$i + 1],
                        $this->key
                    );
                }
                ++$j;
            }
            $hash = $tmp;
            $order >>= 1;
        } while ($order > 1);
        // We should only have one value left:
        return \array_shift($hash);
    }
}
