<?php

use \ParagonIE\Halite\Structure\{
    MerkleTree,
    Node
};

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class KeyedMerkleTreeTest extends PHPUnit_Framework_TestCase
{
    public function testExpectedBehavior()
    {
        $treeA = new MerkleTree(
            new Node('a'),
            new Node('b'),
            new Node('c'),
            new Node('d'),
            new Node('e')
        );
        $treeA->setKeyFromString('');
        $this->assertSame(
            '6781891a87aa476454b74dc635c5cdebfc8f887438829ce2e81423f54906c058',
            $treeA->getRoot()
        );
        $treeA->setKeyFromString(
            \ParagonIE\Halite\Util::raw_hash('' . $treeA->getNodeCount())
        );

        $this->assertNotEquals(
            '6781891a87aa476454b74dc635c5cdebfc8f887438829ce2e81423f54906c058',
            $treeA->getRoot()
        );
        $this->assertEquals(
            '8c53bbdf103a1319fe05bb361c70beacf01c5d55ce70ea900cc4a6e7b95cf524',
            $treeA->getRoot()
        );

        $treeB = new MerkleTree(
            new Node('a'),
            new Node('b'),
            new Node('c'),
            new Node('d'),
            new Node('e'),
            new Node('e'),
            new Node('e'),
            new Node('e')
        );
        $treeB->setKeyFromString(
            \ParagonIE\Halite\Util::raw_hash('' . $treeB->getNodeCount())
        );
        // Deviation from MerkleTree; if the size of tree is used to key the hash,
        // then B isn't the same as A
        $this->assertNotEquals(
            $treeA->getRoot(),
            $treeB->getRoot()
        );

        // Expanded trees inherit the same key
        $treeC = $treeA->getExpandedTree(
            new Node('e'),
            new Node('e'),
            new Node('e')
        );
        $this->assertEquals(
            $treeA->getRoot(),
            $treeC->getRoot()
        );
        // We're setting the key this time, so it should differ.
        $treeD = $treeA->getExpandedTree(
            new Node('e'),
            new Node('e'),
            new Node('e')
        );
        $treeD->setKeyIsHashOfSize(true);

        $this->assertNotEquals(
            $treeA->getRoot(),
            $treeD->getRoot()
        );

        // This should fail
        $treeE = $treeA->getExpandedTree(
            new Node('f'),
            new Node('e'),
            new Node('e')
        );
        $this->assertNotEquals(
            $treeA->getRoot(),
            $treeE->getRoot()
        );
    }
}