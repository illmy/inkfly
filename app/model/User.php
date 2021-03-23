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

    public function create(array $data = []): array
    {
        // 判断部门是否存在
        $department = new Department();
        $departList = $department->where('company_id', '=', $this->username['company_id'])
                        ->where('id', '=', $data['department_id'])->find();
        if (empty($departList)) {
            throw new InvalidRequestException("部门不存在");
        }

        $data = [
            'company_id' => $this->username['company_id'],
            'username' => $data['username'],
            'password' => md5($data['password']),
            'nickname' => $data['nickname'],
            'company_admin' => $data['company_admin'],
            'created_by' => $this->username['username'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        // 判断用户名是否存在
        $exists = $this->where('username', '=', $data['username'])->find();
        if (!$exists) {
            throw new InvalidRequestException('用户名已被注册');
        }

        $result = $this->insert($data);

        if ($result) {
            return $data;
        }

        throw new InvalidRequestException("新增失败");    
    }

    public function editor(array $data = [])
    {
        if (empty($data['id'])) {
            throw new InvalidRequestException('员工不存在');
        }
        $data = [
            'nickname' => $data['nickname'],
            'department_id' => $data['department_id'],
            'updated_by' => $this->username['username'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $exists = $this->where('company_id', '=', $this->username['company_id'])->where('id', '=', $data['id'])->find();
        if (!$exists) {
            throw new InvalidRequestException('员工不存在');
        }

        return $this->where('id', '=', $data['id'])->update($data);
    }


    public function delete($id)
    {
        $exists = $this->where('company_id', '=', $this->username['company_id'])->where('id', '=', $id)->find();
        if (empty($exists)) {
            throw new InvalidRequestException('查询不到该数据');
        }

        $result = $this->where('id', '=', $id)->delete();

        return $exists;
    }
}