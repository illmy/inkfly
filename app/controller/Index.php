<?php 

namespace app\controller;

class Index extends Base
{
    public function index()
    {
        $html = <<<html
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>layuiAdmin pro - 通用后台管理模板系统（单页面专业版）</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="adminspa/start/layui/css/layui.css" media="all">
  <script>
  /^http(s*):\/\//.test(location.href) || alert('请先部署到 localhost 下再访问');
  </script>
</head>
<body>
  <div id="LAY_app"></div>
  <script src="adminspa/start/layui/layui.js"></script>
  <script>
  layui.config({
    base: '/adminspa/src/' //指定 layuiAdmin 项目路径，本地开发用 src，线上用 dist
    ,version: '1.6.1'
  }).use('index');
  </script>
</body>
</html>
html;
        
        echo $html;
    }

    public function getMenus()
    {
        $menus = [
            [
                'id' => 10000,
                'pid' => 0,
                'title' => '首页',
                'url' => 'javascript:void(5);',
                'name' => 'console',
                'icon' => 'layui-icon-home',
                'jump' => "",
            ],
            [
                'id' => 10001,
                'pid' => 0,
                'title' => '部门管理',
                'url' => 'javascript:void(5);',
                'name' => 'console',
                'icon' => 'layui-icon-home',
                'jump' => "",
            ],
            [
                'id' => 10002,
                'pid' => 0,
                'title' => '排名列表',
                'url' => 'javascript:void(5);',
                'name' => 'console',
                'icon' => 'layui-icon-home',
                'jump' => "",
            ],
            [
                'id' => 10003,
                'pid' => 0,
                'title' => '投票列表',
                'url' => 'javascript:void(5);',
                'name' => 'console',
                'icon' => 'layui-icon-home',
                'jump' => "",
            ]
        ];

        return $this->success($menus);
    }
}