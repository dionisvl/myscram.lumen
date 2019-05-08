<?php


namespace App\Scram;

/**
 * https://www.php.net/manual/ru/function.hash.php
 *
 * Class PhpHash
 * @package App\Scram
 */
class PhpHash implements HashInterface
{
    public function hash($string, $algo): string
    {
        return hash($algo, $string);
    }

}