<?php


namespace App\Scram;


class ShaCompute
{
    private $favoriteAlgo = 'sha512';
    private $favoriteEncrypt = 'hash';
    private $protocolVersion = '1';


    /**
     * ShaCompute constructor.
     * @param string $favoriteAlgo
     * @param string $favoriteEncrypt
     */
    public function __construct(string $favoriteAlgo, string $favoriteEncrypt, $protocolVersion)
    {
        $this->favoriteAlgo = $favoriteAlgo;
        $this->favoriteEncrypt = $favoriteEncrypt;
        $this->protocolVersion = $protocolVersion;
    }

    public function compute($data){
        switch ($this->favoriteEncrypt) {
            case 'openssl':
                return openssl_digest($data, $this->favoriteAlgo);
            case 'hash':
                return hash($this->favoriteAlgo, $data);
        }
        throw new \RuntimeException('No hash function on this platform');
    }
}