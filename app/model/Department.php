<?php

/**
 * 部门
 */

namespace app\model;

use elaborate\orm\Model;
use app\exceptions\InvalidRequestException;

class Department extends Model
{
    protected $table = 'departments';

    public function list()
    {
        $list = $this->where('company_id', '=', $this->username['company_id'])->find();
        return $list;
    }

    public function create(array $data = []):array
    {
        $data['company_id'] = $this->username['company_id'];
        $data['created_by'] = $this->username['username'];
        $data['created_at'] = date('Y-m-d H:i:s');
        $result = $this->insert($data);

        if ($result) {
            return $data;
        }

        throw new InvalidRequestException("新增失败");
    }

    protected function check()
    {

    }

    public function editor()
    {
        $data['updated_by'] = request()->user()->realname;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $exists = $this->firsts($where);
        if(!$exists) {
            throw new InvalidRequestException('查询不到该数据');
        }
        return $exists->update($data);

    }

    public function delete()
    {
        $department = $this->model::find($id);
        if (!isset($department->id)) {
            throw new InvalidRequestException('查询不到该数据');
        }
        //部门下有员工，则无法删除
        $exits = $this->checkIsContainUser($id);
        if ($exits) {
            throw new InvalidRequestException('该部门下已有员工，无法删除');
        }
        return $department->delete();

    }

}
