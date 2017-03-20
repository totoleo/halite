<?php
namespace ParagonIE\Halite\Contract;
use \ParagonIE\Halite\Alerts as CryptoException;
/**
 * 
 */
interface StreamInterface
{
    /**
     * Read from a stream; prevent partial reads
     * 
     * @param int $num
     * @return string
     * @throws CryptoException\FileAccessDenied
     */
    public function readBytes($num);
    
    /**
     * Write to a stream; prevent partial writes
     * 
     * @param string $buf
     * @param int $num (number of bytes)
     * @throws CryptoException\FileAccessDenied
     */
    public function writeBytes($buf, $num = null);
}