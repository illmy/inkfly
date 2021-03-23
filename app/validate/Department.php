<?php 

namespace app\validate;

class Department
{
    protected $createRules = [
        'dept_name' => 'require|length:4,16',
        'parent_id' => 'number',
        'manager_id' => 'number'
    ];

    public function getRules(string $name = '')
    {
        if (!isset($this->$name)) {
            throw new \Exception('规则不存在');
        }

        return $this->$name;
    }

    public function __get($name)
    {
        return $this->getRules($name);
    }

}