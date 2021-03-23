<?php

namespace app\model;

use elaborate\orm\Model;
use app\exceptions\InvalidRequestException;

class User extends Model
{
    const USER_STATE_DISABLE = 0;

    const USER_STATE_ENABLE = 1;

    protected $table = 'users';

    public function Login(string $username, string $password)
    {
        $field = ['username', 'password', 'nickname', 'id', 'department_id', 'company_id'];
        $list = $this->field($field)->where('username', '=', $username)->find();

        if (empty($list)) {
            throw new InvalidRequestException('用户不存在');
        }

        //验证密码
        if (md5($password) != $list['password']) {
            throw new InvalidRequestException('密码错误');
        }

        if ($list['state'] === self::USER_STATE_DISABLE) {
            throw new InvalidRequestException('用户被禁用');
        }
        unset($list['password']);
        
        return $list;
    }

    public function list()
    {
        $list = $this->where('id', '=', '1')->find();
        return $list;
    }
}