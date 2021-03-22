<?php 

namespace app\validate;

class User
{
    protected $loginRules = [
        'username' => 'require|length:4,16',
        'password' => 'require|length:6,16'
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