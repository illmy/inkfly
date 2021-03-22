<?php 

namespace app\controller;

use app\model\Index as IndexModel;

class Index extends Base
{
    public function index(IndexModel $model)
    {
        // $list = $this->app->db->table('users')->where('id', '=', '1')->find();
        // var_dump($list);
        $json = ['ces' => 'ok'];
        $list = $model->list();
        var_dump($list);
        return json($json);
    }

    public function list()
    {
        $json = ['ces' => 'okgggg'];
        $username = $this->request->param('username');
        echo $username;
        return json($json);
    }
}