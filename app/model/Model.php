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

    public function initData(array $userData = [])
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
            if (strpos($field, '.')) {
                $fld = explode('.', $field)[1];
            } else {
                $fld = $field;
            }

            if (
                !empty($query[$fld])
                || (isset($query[$fld]) && $query[$fld] == '0')
            ) {
                $this->where($field, $op, str_replace('%VALUE%', $query[$fld], $val));
            }
        }

        if (!empty($this->queryGroupField)) {
            $this->groupBy($this->queryGroupField);
        }

        foreach ($this->queryOrderField as $orderField) {
            [$field, $su] = $orderField;
            if (strpos($field, '.')) {
                $fld = explode('.', $field)[1];
            } else {
                $fld = $field;
            }
            if (isset($query['order_field']) && $query['order_field'] == $fld) {
                $this->orderBy($field, $query['order_way'] ?? 'asc');
            } else {
                $this->orderBy($field, $su);
            }
        }

        if ($isPage) {
            $page = !empty($query['page']) ?: 1;
            $pageSize = !empty($query['limit']) ? $query['limit'] : $this->pageSize;
            $start = ($page - 1) * $pageSize;
            $this->limit($start, $pageSize);
        }
        $result = $this->select();
        return $result;
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
