<?php

namespace app\model;

use elaborate\orm\Model;

class Index extends Model
{
    protected $table = 'users';

    public function list()
    {
        $list = $this->where('id', '=', '1')->find();
        return $list;
    }
}