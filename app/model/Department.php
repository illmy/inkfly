<?php

/**
 * 部门
 */

namespace app\model;

use app\exceptions\InvalidRequestException;

class Department extends Model
{
    protected $table = 'departments';

    protected $queryWhereField = [
        ['parent_id', '=', '%VALUE%'],
        ['dept_name', 'like', '%%VALUE%%']
    ];

    protected $queryShowField = ['id', 'parent_id', 'dept_name as name', 'created_at'];

    protected $queryOrderField = [
        ['parent_id', 'asc']
    ];

    protected $queryGroupField = '';

    public function beforeList($query = [])
    {
        // $this->alias('d');
        // $this->leftJoin('users as u', 'u.id', 'd.manager_id');
        $this->where('company_id', '=', $this->userData['company_id']);

    }

    public function create(array $data = []): array
    {
        // 判断parent是否存在
        $list = $this->where('company_id', '=', $this->userData['company_id'])->where('id', '=', $data['parent_id'])->find();
        if (empty($list)) {
            throw new InvalidRequestException("上级部门不存在");
        }

        $data = [
            'company_id' => $this->userData['company_id'],
            'dept_name' => $data['dept_name'],
            'parent_id' => $data['parent_id'],
            'manager_id' => !empty($data['manager_id']) ? $data['manager_id'] : 0,
            'created_by' => $this->userData['username'],
            'created_at' => date('Y-m-d H:i:s')
        ];


        // 同级部门不能出现相同名称的部门
        $this->checkData($data);

        // 一个人只能担任一个部门经理
        if (!empty($data['manager_id'])) {
            $user = $this->where('company_id', '=', $this->userData['company_id'])->where('manager_id', '=', $data['manager_id'])->find();
            if (!empty($user)) {
                throw new InvalidRequestException("已经是部门经理");
            }
            
        }
        
        $result = $this->insert($data);

        if ($result) {
            $data['id'] = $result;
            return $data;
        }

        throw new InvalidRequestException("新增失败");
    }

    public function info(string $id) 
    {
        $exists = $this->where('company_id', '=', $this->userData['company_id'])->where('id', '=', $id)->find();

        if (empty($exists)) {
            throw new InvalidRequestException('部门不存在');
        }

        return $exists;
    }

    public function editor(array $data = [])
    {
        if (empty($data['id'])) {
            throw new InvalidRequestException('部门不存在');
        }
        $init = [
            'dept_name' => $data['dept_name'],
            'parent_id' => $data['parent_id'],
            'manager_id' => !empty($data['manager_id']) ? $data['manager_id'] : 0,
            'updated_by' => $this->userData['username'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $exists = $this->where('company_id', '=', $this->userData['company_id'])->where('id', '=', $data['id'])->find();
        if (!$exists) {
            throw new InvalidRequestException('部门不存在');
        }

        if ($exists['parent_id'] == '0') {
            throw new InvalidRequestException('顶级部门不能修改');
        }

        
        // 同级部门不能出现相同名称的部门
        $this->checkData($data);

        // 一个人只能担任一个部门经理
        if (!empty($data['manager_id'])) {
            $user = $this->where('company_id', '=', $this->userData['company_id'])
                    ->where('id', '<>', $data['id'])
                    ->where('manager_id', '=', $data['manager_id'])
                    ->find();
            if (!empty($user)) {
                throw new InvalidRequestException("已经是部门经理");
            }
            
        }

        // 不能是下级部门 或 自己本身
        $child = $this->getChildDepart($data['id']);
        if (in_array($data['parent_id'], $child)) {
            throw new InvalidRequestException('上级部门不能是本身和下级部门');
        }
        
        $result = $this->where('id', '=', $data['id'])->update($init);

        if ($result) {
            return $data;
        }

        throw new InvalidRequestException("编辑失败");
    }

    public function getChildDepart($id, $isSelf = true)
    {
        $list = $this->where('company_id', '=', $this->userData['company_id'])->select();

        $arr = $this->getLoopDepart($id, $list);

        $arr = array_column($arr, 'id');

        if ($isSelf) {
            array_push($arr, $id);
        }

        return $arr;
    }

    private function getLoopDepart($id, $list = [])
    {
        $arr = [];

        foreach ($list as $value) {
            if ($value['parent_id'] == $id) {
                array_push($arr, $value);
                $gg = $this->getLoopDepart($value['id'], $list);
                $arr = array_merge($arr, $gg);
            }
        }
        
        return $arr;
    }

    public function delete($id)
    {
        $exists = $this->where('company_id', '=', $this->userData['company_id'])->where('id', '=', $id)->find();
        if (empty($exists)) {
            throw new InvalidRequestException('查询不到该数据');
        }

        if ($exists['parent_id'] == '0') {
            throw new InvalidRequestException('顶级部门不能删除');
        }
        //部门下有员工，则无法删除
        $user = new User();
        $userList = $user->where('company_id', '=', $this->userData['company_id'])->where('department_id', '=', $id)->find();
        if ($userList) {
            throw new InvalidRequestException('该部门下已有员工，无法删除');
        }

        // 部门下有下级部门 无法删除
        $child = $this->getChildDepart($id, false);
        if (!empty($child)) {
            throw new InvalidRequestException('该部门下有子部门，无法删除');
        }

        $result = $this->where('id', '=', $id)->delete();

        return $exists;
    }

    protected function checkData($data)
    {
        $where = [];
        if (!empty($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }

        $where[] = ['company_id', '=', $this->userData['company_id']];
        $where[] = ['parent_id', '=', $data['parent_id']];

        $column = [
            ['column' => 'dept_name', 'msg' => '同级名称已存在', 'sign' => '='],
        ];

        foreach ($column as $value) {
            $where[] = [$value['column'], $value['sign'], $data[$value['column']]];
            if (!$this->check($where)) {
                throw new InvalidRequestException($value['msg']);
            }
            unset($where[$value['column']]);
        }
        return true;
    }
}
