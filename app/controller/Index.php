<?php 

namespace app\controller;

use elaborate\Application;

class Index 
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function index()
    {
        $list = $this->app->db->table('users')->where('id', '=', '1')->find();
        var_dump($list);
        $list = $this->app->db->table('users')->where('id', '=', '2')->where('id', '=', '3')->find();
        var_dump($list);
        $json = ['ces' => 'ok'];
        return json($json);
    }
}