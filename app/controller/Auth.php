<?php 

namespace app\controller;

use app\model\User;
use app\validate\User as UserValidate;
use app\tools\Jwt;

class Auth extends Base
{
    public function Login(User $user, UserValidate $validate)
    {
        // 参数验证
        $this->validate($validate->loginRules, $this->requestParam);
        extract($this->requestParam);

        $result = $user->login($username, $password);

        $accessToken = Jwt::encode($result);

        $res = [
            'token' => $accessToken,
            'user_info' => $result
        ];

        return $this->success($res);
    }

    public function logout()
    {
        return $this->success([]);
    }
}
