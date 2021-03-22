<?php 

namespace app\controller;

use elaborate\Controller;
use app\exceptions\InvalidRequestException;

class Base extends Controller
{
    protected $requestParam = [];

    protected $validateErrorMsg = '';

    protected function initialize()
    {
        $this->requestParam = $this->request->param('', '', 'htmlspecialchars,addsalashes');
    }

    protected function getRequestParam(string $name = '', $default = null) 
    {

    }

    protected function success(array $data = [], string $msg = '')
    {
        $data = [
            'code' => 0,
            'msg' => $msg,
            'data' => $data
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

        foreach ($rules as $key => $val) {
            $childRule = explode('|', $val);
            foreach ($childRule as $child) {
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
