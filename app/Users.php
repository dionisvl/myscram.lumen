<?php


namespace App;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{

    public $timestamps = false;


    protected $fillable = [
        'user_login', 'user_password', 'user_hash', 'user_ip'
    ];

    public static function add($fields)
    {
        $user = new static;
        $user->fill($fields);
        $user->save();
        //return $user;
    }
}