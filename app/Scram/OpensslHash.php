<?php


namespace App\Scram;

/**
 * https://www.php.net/manual/ru/function.openssl-digest.php
 *
 * Class OpensslHash
 * @package App\Scram
 */
class OpensslHash implements HashInterface
{
    public function hash($string, $algo): string
    {
        return openssl_digest($string, $algo);
    }
}