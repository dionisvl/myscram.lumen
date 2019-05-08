<?php


namespace App\Scram;


class RandomString
{
    /**
     * @var string
     */
    private $generatedString = '';

    /**
     * RandomString constructor.
     * @param int $length
     * @throws \Exception
     */
    function __construct($length = 10)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, random_int(1, $numChars) - 1, 1);
        }
        $this->generatedString = $string;
    }

    public function handle(){
        return $this->generatedString;
    }
}