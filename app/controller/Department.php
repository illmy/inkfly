<?php

namespace app\controller;

use app\model\Department as Depart;
use app\validate\Department as DepartValidate;

class Department extends Base
{
    public function store(Depart $model, DepartValidate $validate)
    {
        // 参数验证
        $this->validate($validate->loginRules, $this->requestParam);
        $result = $model->initData($this->userData)->create($this->requestParam);

        $this->success($result);
    }
}