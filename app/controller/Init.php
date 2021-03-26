<?php  

namespace app\controller;

use app\model\Menu;
use app\model\User;
use app\model\Company;

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

    public function user(User $model, Company $company)
    {
        $result = $model->initData($this->userData)->info($this->userData['id']);

        $cmp = $company->initData($this->userData)->info();

        $result['logo'] = $cmp['logo'];

        return $this->success($result);
    }
}