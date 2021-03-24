<?php

namespace app\controller;

use app\model\Department as Depart;
use app\validate\Department as DepartValidate;

class Department extends Base
{
    public function store(Depart $model, DepartValidate $validate)
    {
        // 参数验证
        $this->validate($validate->createRules, $this->requestParam);
        $result = $model->initData($this->userData)->create($this->requestParam);

        return $this->success($result);
    }

    public function info(Depart $model)
    {
        $list = $model->initData($this->userData)->info($this->requestParam['id']);

        return $this->success($list);
    }

    public function list(Depart $model)
    {
        $list = $model->initData($this->userData)->list($this->requestParam);

        return $this->success($list);
    }

    public function update(Depart $model)
    {
        $list = $model->initData($this->userData)->editor($this->requestParam);

        return $this->success($list);
    }

    public function delete(Depart $model)
    {
        $list = $model->initData($this->userData)->delete($this->requestParam['id']);

        return $this->success($list);
    }
}