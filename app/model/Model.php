<?php

/**
 * 部门
 */

namespace app\model;

use elaborate\orm\Model as ModelBase;

class Model extends ModelBase
{
    protected $userData = [];

    public function initUser(array $userData = []) 
    {
        $this->userData = $userData;

        return $this;
    }
}
