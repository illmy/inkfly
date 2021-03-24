<?php 

namespace app\validate;

class Voting
{
    protected $votingRules = [

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