<?php 

namespace app\validate;

class User
{
    protected $loginRules = [
        'username' => 'require|length:4,16',
        'password' => 'require|length:6,16'
    ];

    protected $createRules = [
        'nickname' => 'require|length:1,16',
        'username' => 'require|regex:/^[a-zA-Z]{4,16}$/',
        'password' => 'require|regex:/^[a-zA-Z0-9_]{6,16}$/'
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