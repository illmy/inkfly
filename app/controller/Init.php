<?php  

namespace app\controller;

use app\model\Menu;
use app\model\User;

/**
 * 用于前端初始化，跳过权限认证
 */
class init extends Base
{
    public function menus(Menu $model)
    {
        $result = $model->initData($this->userData)->getMenus($this->userData['id']);

        return $this->success($result);
    }

    public function user(User $model)
    {
        $result = $model->initData($this->userData)->info($this->userData['id']);

        return $this->success($result);
    }
}