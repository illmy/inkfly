<?php

namespace app\model;

use elaborate\orm\Model;
use app\exceptions\InvalidRequestException;

class Menu extends Model
{
    protected $table = 'menus';

    public function list()
    {
        $list = $this->where('state', '=', '1')->select();
        return $list;
    }
}