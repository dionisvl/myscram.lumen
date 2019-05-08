<?php


namespace App\Scram;


interface HashInterface
{
    public function hash($string, $algo): string;

}