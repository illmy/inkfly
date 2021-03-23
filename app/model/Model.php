<?php

/**
 * 模型基类
 */

namespace app\model;

use elaborate\orm\Model as ModelBase;

class Model extends ModelBase
{
    protected $userData = [];

    protected $pageSize = 20;

    protected $queryWhereField = [];

    protected $queryShowField = [];

    protected $queryOrderField = [];

    protected $queryGroupField = '';

    public function initUser(array $userData = [])
    {
        $this->userData = $userData;

        return $this;
    }

    public function list(array $query = [], $isPage = true)
    {
        $this->beforeList($query);
        $this->field($this->queryShowField);
        foreach ($this->queryWhereField as $whereField) {
            
            [$field, $op, $val] = $whereField;
            if (!empty($query[$field]) || $query[$field] == '0') {
                $this->where($field, $op, str_replace('%VALUE%', [$query[$field]], $val));
            }
        }

        if (!empty($this->queryGroupField)) {
            $this->groupBy($this->queryGroupField);
        }

        foreach ($this->queryOrderField as $orderField) {
            [$field, $su] = $orderField;
            if ($query['order_field'] == $field) {
                $this->orderBy($su . $field, $query['order_way'] ?? 'asc');
            }
        }
        if ($isPage) {
            $page = !empty($query['page']) ?: 1;
            $pageSize = !empty($query['page']) ?: $this->pageSize;
            $start = ($page - 1) * $pageSize;
            $this->limit($start, $pageSize);
        }
        return $this->select();
    }

    protected function beforeList($query = [])
    {

    }

    /**
     * 判断数据是否存在
     *
     * @param string $where
     * @return bool
     */
    protected function check($where = [])
    {
        foreach ($where as $value) {
            [$field, $op, $val] = $value;
            $this->where($field, $op, $val);
        }

        $list = $this->find();
        if ($list) {
            return false;
        } else {
            return true;
        }
    }
}
