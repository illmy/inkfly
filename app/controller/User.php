<?php 

namespace app\controller;

use app\model\Menu;

class User extends Base
{
    public function info(Menu $menu)
    {
        $list = $menu->list();
        $data = [
            'name' => $this->userData['username'],
            'roles' => ['admin']
        ];
        return $this->success($data);
    }
}