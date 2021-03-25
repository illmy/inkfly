<?php

namespace app\model;

use app\exceptions\InvalidRequestException;
use app\tools\Auth;

class Menu extends Model
{
    /**
     * 树形菜单
     */
    const MENU_TREE = 1;
    /**
     * 普通二位数组
     */
    const MENU_ARRAY = 2;

    const STATUS_ABLE = 1;

    const STATUS_UNABLE = 0;

    const GROUP_ADMIN = 'admin';

    const IS_MENU = 1;

    const ISNT_MENU = 0;

    protected $table = 'menu';

    public function getMenus($uid, $retrunWay = self::MENU_TREE) {
        $field = ['id', 'pid', 'title', 'url', 'name', 'icon', 'jump'];
        $menu = $this->where('group', '=', self::GROUP_ADMIN)
                     ->where('status', '=', self::STATUS_ABLE)
                     ->where('is_menu', '=', self::IS_MENU)
                     ->field($field)
                     ->orderBy('pid,sort', 'asc')
                     ->select();

        $unsetIds  = [];
        foreach ($menu as $key => $item) {
            $result = (new Auth())->check(strtolower($item['url']), $uid);

            if (!$result || in_array($item['pid'], $unsetIds)) {
                $unsetIds[] = $item['id'];
                unset($menu[$key]);
                continue;
            }
        }
        $menu = array_values($menu);
        if ($retrunWay === self::MENU_ARRAY) {
            return $menu;
        }
        $menu = list_to_tree($menu);
        return $menu;
    }
}