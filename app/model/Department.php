<?php

namespace app\model;

use elaborate\orm\Model;

class Department extends Model
{
    protected $table = 'departments';

    public function list()
    {
        $list = $this->where('id', '=', '1')->find();
        return $list;
    }

    public function create()
    {
        
    }
}