<?php

namespace app\model;

use app\exceptions\InvalidRequestException;
use app\model\AuthGroup;

class User extends Model
{
    const USER_STATE_DISABLE = 0;

    const USER_STATE_ENABLE = 1;

    const IS_ADMIN = 1;

    const NO_ADMIN = 0;

    protected $table = 'users';

    protected $queryWhereField = [
        ['u.department_id', '=', '%VALUE%'],
    ];

    protected $queryShowField = ['u.id', 'u.nickname', 'd.manager_id', 'u.created_at', 'u.state', 'u.department_id'];

    public function beforeList($query = [])
    {
        $this->alias('u');
        $this->leftJoin('departments as d', 'u.id', 'd.manager_id');
        $this->where('u.company_id', '=', $this->userData['company_id']);

    }

    public function Login(string $username, string $password)
    {
        $field = ['username', 'password', 'nickname', 'id', 'department_id', 'company_id', 'state'];
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
        $departList = $department->where('company_id', '=', $this->userData['company_id'])
                        ->where('id', '=', $data['department_id'])->find();
        if (empty($departList)) {
            throw new InvalidRequestException("部门不存在");
        }

        $data['company_admin'] = $data['company_admin'] ?? 0;
        if (!in_array($data['company_admin'], [self::IS_ADMIN, self::NO_ADMIN])) {
            $data['company_admin'] = self::NO_ADMIN;
        }

        $data = [
            'company_id' => $this->userData['company_id'],
            'username' => $data['username'],
            'password' => md5($data['password']),
            'nickname' => $data['nickname'],
            'state' => self::USER_STATE_ENABLE,
            'department_id' => $data['department_id'],
            'company_admin' => $data['company_admin'],
            'created_by' => $this->userData['username'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        // 判断用户名是否存在
        $exists = $this->where('username', '=', $data['username'])->find();
        if ($exists) {
            throw new InvalidRequestException('用户名已被注册');
        }

        $result = $this->insert($data);

        if ($result) {
            (new AuthGroup)->bindUserGroup($result);
            unset($data['password']);
            return $data;
        }

        throw new InvalidRequestException("新增失败");    
    }

    public function info(string $id) 
    {
        $field = ['company_id', 'username', 'nickname', 'department_id', 'company_admin', 'id'];
        $exists = $this->where('company_id', '=', $this->userData['company_id'])
                        ->where('id', '=', $id)
                        ->field($field)
                        ->find();

        if (empty($exists)) {
            throw new InvalidRequestException('员工不存在');
        }

        return $exists;
    }

    public function editor(array $data = [])
    {
        if (empty($data['id'])) {
            throw new InvalidRequestException('员工不存在');
        }
        // 判断部门是否存在
        $department = new Department();
        $departList = $department->where('company_id', '=', $this->userData['company_id'])
                        ->where('id', '=', $data['department_id'])->find();
        if (empty($departList)) {
            throw new InvalidRequestException("部门不存在");
        }

        $data['company_admin'] = $data['company_admin'] ?? 0;
        if (!in_array($data['company_admin'], [self::IS_ADMIN, self::NO_ADMIN])) {
            $data['company_admin'] = self::NO_ADMIN;
        }

        $init = [
            'nickname' => $data['nickname'],
            'department_id' => $data['department_id'],
            'company_admin' => $data['company_admin'] ?? 0,
            'updated_by' => $this->userData['username'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $exists = $this->where('company_id', '=', $this->userData['company_id'])->where('id', '=', $data['id'])->find();
        if (!$exists) {
            throw new InvalidRequestException('员工不存在');
        }

        $result = $this->where('id', '=', $data['id'])->update($init);
        if ($result) {
            return $data;
        }

        throw new InvalidRequestException("编辑失败");  
    }


    public function delete($id)
    {
        $exists = $this->where('company_id', '=', $this->userData['company_id'])->where('id', '=', $id)->find();
        if (empty($exists)) {
            throw new InvalidRequestException('查询不到该数据');
        }

        $result = $this->where('id', '=', $id)->delete();

        return $exists;
    }

    /**
     * 获取所有经理
     *
     * @return array
     */
    public function getAllManager()
    {
        $sql = "select id, username, nickname from users where id in (select manager_id from departments where manager_id > 0)";

        $list = $this->query($sql);

        return $list;
    }
}