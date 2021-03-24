<?php 

namespace app\controller;

use app\model\Menu;
use app\model\User as UserModel;
use app\validate\User as UserValidate;

class User extends Base
{
    public function store(UserModel $model, UserValidate $validate)
    {
        // 参数验证
        $this->validate($validate->loginRules, $this->requestParam);
        $result = $model->initData($this->userData)->create($this->requestParam);

        return $this->success($result);
    }

    public function info(UserModel $model)
    {
        $list = $model->initData($this->userData)->info($this->requestParam['id']);

        return $this->success($list);
    }

    public function list(UserModel $model)
    {
        $list = $model->initData($this->userData)->list($this->requestParam);

        return $this->success($list);
    }

    public function update(UserModel $model)
    {
        $list = $model->initData($this->userData)->editor($this->requestParam);

        return $this->success($list);
    }

    public function delete(UserModel $model)
    {
        $list = $model->initData($this->userData)->delete($this->requestParam['id']);

        return $this->success($list);
    }
}