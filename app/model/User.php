<?php

namespace app\model;

use elaborate\orm\Model;

class User extends Model
{
    protected $table = 'users';

    public function Login(string $username, string $password)
    {
        
    }

    public function list()
    {
        $list = $this->where('id', '=', '1')->find();
        return $list;
    }
}