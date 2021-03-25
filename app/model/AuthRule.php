<?php

namespace app\model;

/**
 * 权限规则模型
 */
class AuthRule extends Model
{
    const RULE_URL = 1;
    const RULE_MAIN = 2;

    protected $table = 'auth_rule';

    /**
     * 获取规则列表
     * @Author   liulong                  <335111164@qq.com>
     * @DateTime 2018-03-02T15:09:52+0800
     * @param    string                   $where             [description]
     * @param    string                   $order             [description]
     * @param    string                   $field             [description]
     * @param    [type]                   $limit             [description]
     * @return   [type]                                      [description]
     */
    public function getRulesList(array $where = [], array $order = [], array $field = [])
    {
        foreach ($where as $value) {
            [$fld, $op, $val] = $value;
            $this->where($fld, $op, $val);
        }

        if (!empty($order)) {
            [$left, $right] = $order;
            $this->orderBy($left, $right);
        }
        
        $this->field($field);

        $list = $this->select();

        return $list;
    }

}
