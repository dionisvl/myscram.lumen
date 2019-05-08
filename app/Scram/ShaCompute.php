<?php


namespace App\Scram;

use App\Scram\OpensslHash;
use App\Scram\PhpHash;

class ShaCompute
{
    private $favoriteAlgo;
    private $encrypter;
    private $protocolVersion;


    /**
     * ShaCompute constructor.
     * @param string $favoriteAlgo
     * @param string $favoriteEncrypt
     */
    public function __construct(string $favoriteAlgo, string $favoriteEncrypt,  $protocolVersion)
    {
        $this->favoriteAlgo = $favoriteAlgo;
        $this->favoriteEncrypt = $favoriteEncrypt;
        $this->protocolVersion = $protocolVersion;

        switch ($favoriteEncrypt) {
            case 'openssl':
                $this->encrypter = new OpensslHash();
                break;
            case 'hash':
                $this->encrypter = new PhpHash();
                break;
            default:
                throw new \RuntimeException('No hash function on this platform');
        }

    }

    public function compute($data){
        return ($this->encrypter)->hash($data, $this->favoriteAlgo);
    }
}