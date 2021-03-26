<?php 

namespace app\controller;

use elaborate\Controller;
use app\exceptions\InvalidRequestException;
use app\tools\Jwt;

class Base extends Controller
{
    protected $requestParam = [];

    protected $userData = [];

    protected function initialize()
    {
        $token = $this->request->header('X-Token');
        if (empty($token)) {
            $token = $this->request->param('X-Token');
        }
        $this->userData = Jwt::decode($token);
        $this->requestParam = $this->request->param('', '', 'addslashes,htmlspecialchars');

        if (isset($this->requestParam['X-Token'])) {
            unset($this->requestParam['X-Token']);
        }
    }

    protected function success(array $data = [], string $msg = '')
    {
        $data = [
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
            'count' => count($data)
        ];
        
        return json($data);
    }

    protected function validate(array $rules = [], array $data = [])
    {
        $require = function($name, $value) {
            if (empty($value) && $value != '0') {
                return $name . '参数不能为空';
            } else {
                return true;
            }
        };

        $length = function($name, $rule, $value) {
            [$min, $max] = explode(',', $rule);
            if (strlen($value) < $min || strlen($value) > $max) {
                return $name . "参数长度必须在{$min}和{$max}之间";
            } else {
                return true;
            }
        };

        $number = function($name, $value) {
            if (!is_numeric($value)) {
                return $name . '参数必须是数字';
            } else {
                return true;
            }
        };

        $regex = function($name, $rule, $value) {
            if (!preg_match($rule, $value)) {
                return $name . '参数不规范';
            } else {
                return true;
            }
        };

        foreach ($rules as $key => $val) {
            $childRule = explode('|', $val);
            foreach ($childRule as $child) {
                if ((!isset($data[$key]) || strlen($data[$key] == 0)) && !in_array('require', $childRule)) {
                    continue;
                }
                if (strpos($child, ':')) {
                    [$name, $rule] = explode(':', $child);
                    $result = $$name($key, $rule, $data[$key] ?? '');
                } else {
                    $result = $$child($key, $data[$key] ?? '');
                }

                if ($result !== true) {
                    throw new InvalidRequestException($result);
                }
            }
        }

        return true;
    }

}
