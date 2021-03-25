<?php

namespace app\model;

class AuthGroup extends Model
{
    const STATUS_ABLE = 1;
    const STATUS_UNABLE = 0;

    const MODULE_ADMIN = 'admin';

    protected $table = 'auth_group';

    /**
     * 返回用户所属用户组信息
     * @param  int    $uid 用户id
     * @return array  用户所属的用户组 
     * array(
     *   array('userid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     * ...)
     */
    public function getUserGroup($uid)
    {
        static $groups = [];
        if (isset($groups[$uid]))
            return $groups[$uid];

        $field = ['ac.user_id', 'ac.group_id', 'ag.title', 'ag.description', 'ag.rules'];
        $list = $this->table('auth_group_access as ac')
             ->join('auth_group as ag', 'ag.id', 'ac.group_id')
             ->where('ac.user_id', '=', $uid)
             ->where('ag.status', '=', self::STATUS_ABLE)
             ->field()
             ->select();
        
        $groups[$uid] = $list;
        return $groups[$uid];
    }
}
