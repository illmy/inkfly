<?php 

namespace app\controller;

use app\model\User;
use app\validate\User as UserValidate;
use app\tools\Jwt;
use app\model\Menu;
use app\exceptions\InvalidRequestException;

class Auth extends Base
{
    public function Login(User $user, UserValidate $validate, Menu $mObj)
    {
        // 参数验证
        $this->validate($validate->loginRules, $this->requestParam);
        extract($this->requestParam);

        $result = $user->login($username, $password);

        $accessToken = Jwt::encode($result);

        $menus = $mObj->getMenus($result['id'], Menu::MENU_ARRAY);
        if (empty($menus)) {
            throw new InvalidRequestException('未授权访问', 1002);
        }

        $res = [
            'token' => $accessToken,
            'user_info' => $result,
            'console' => !empty($menus) ? $menus[0]['jump'] : '',
        ];

        return $this->success($res);
    }

    public function logout()
    {
        return $this->success([]);
    }
}
