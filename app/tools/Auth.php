<?php

namespace app\tools;

/**
 * 权限认证类
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth=new Auth();  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
 *      $auth=new Auth();  $auth->check('规则1,规则2','用户id','and') 
 *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
 * 3，一个用户可以属于多个角色(auth_group_access表 定义了用户所属角色)。我们需要设置每个角色拥有哪些规则(auth_group 定义了角色权限)
 * 
 * 4，支持规则表达式。
 *      在auth_rule 表中定义一条规则时，如果type为1， condition字段就可以定义规则表达式。 如定义{score}>5  and {score}<100  表示用户的分数在5-100之间时这条规则才会通过。
 */

//数据库
/*
-- ----------------------------
-- auth_rule，规则表，
-- id:主键，name：规则唯一标识, title：规则中文名称 status 状态：为1正常，为0禁用，condition：规则表达式，为空表示存在就验证，不为空表示按照条件验证
-- ----------------------------
 DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE `auth_rule` (  
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,  
    `name` char(80) NOT NULL DEFAULT '',  
    `title` char(20) NOT NULL DEFAULT '',  
    `type` tinyint(1) NOT NULL DEFAULT '1',    
    `status` tinyint(1) NOT NULL DEFAULT '1',  
    `condition` char(100) NOT NULL DEFAULT '',  # 规则附件条件,满足附加条件的规则,才认为是有效的规则
    PRIMARY KEY (`id`),  
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
-- ----------------------------
-- auth_group 角色表， 
-- id：主键， title:角色中文名称， rules：角色拥有的规则id， 多个规则","隔开，status 状态：为1正常，为0禁用
-- ----------------------------
 DROP TABLE IF EXISTS `auth_group`;
CREATE TABLE `auth_group` ( 
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT, 
    `title` char(100) NOT NULL DEFAULT '', 
    `status` tinyint(1) NOT NULL DEFAULT '1', 
    `rules` char(80) NOT NULL DEFAULT '', 
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
-- ----------------------------
-- auth_group_access 角色明细表
-- uid:用户id，group_id：角色id
-- ----------------------------
DROP TABLE IF EXISTS `auth_group_access`;
CREATE TABLE `auth_group_access` (  
    `user_id` mediumint(8) unsigned NOT NULL,  
    `group_id` mediumint(8) unsigned NOT NULL, 
    UNIQUE KEY `uid_group_id` (`uid`,`group_id`),  
    KEY `uid` (`uid`), 
    KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */

class Auth
{
    /**
     * 配置项
     *
     * @var array
     */
    protected $config = [
        'auth_on'           => true,                            //认证开关
        'auth_type'         => 1,                               //认证方式 1实时认证；2 登录认证
        'type'              => 1,                               //保存偏移
        'model'             => 'url',                           //执行check模式
        'relation'          => 'or',                            //如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
        'get_group'         => 'app\model\AuthGroup',           //角色
        'get_rule'          => 'app\model\AuthRule',            //规则
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 检测权限
     *
     * @param string|array $name  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param int          $uid   认证用户的id
     * @return bool
     */
    public function check($name, $uid)
    {
        if (!$this->config['auth_on']) {
            return true;
        }
        $authList = $this->getAuthList($uid); //获取用户需要验证的所有有效规则列表

        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }
        $relation = $this->config['relation'];
        $list = [];         //保存验证通过的规则名

        foreach ($authList as $auth) {
            if (in_array($auth, $name)) {
                $list[] = $auth;
            }
        }
        if ($relation == 'or' and !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation == 'and' and empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * 根据用户id获取角色,返回值为数组
     * @param  int    uid  用户id
     * @return array       用户所属的角色 
     * array(
     *   array('uid'=>'用户id','group_id'=>'角色id','title'=>'角色名称','rules'=>'角色拥有的规则id,多个,号隔开'),
     * ...)   
     */
    public function getGroups($uid)
    {
        $authGroup = new $this->config['get_group']();
        return $authGroup->getUserGroup($uid);
    }

    /**
     * 读取角色所有权限规则
     *
     * @param array $where
     * @return array
     */
    public function getRulesList(array $where = [])
    {
        $authRule = new $this->config['get_rule']();
        $rules = $authRule->getRulesList($where, ['id', 'asc'], ['name']);

        return $rules;
    }

    /**
     * 获得权限列表
     * @param integer $uid  用户id
     * @param integer $type 
     */
    protected function getAuthList($uid)
    {
        static $_authList = []; //保存用户验证通过的权限列表

        if (isset($_authList[$uid])) {
            return $_authList[$uid];
        }

        //读取用户所属角色
        $groups = $this->getGroups($uid);
        $ids = []; //保存用户所属角色设置的所有权限规则id
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid] = [];
            return [];
        }
        $ids = implode(',', $ids);
        $map = [
            ['id', 'in', "{$ids}"],
            ['type', '=', 1],
            ['status', '=', 1]
        ];
        //读取角色所有权限规则
        $rules = $this->getRulesList($map);
        //循环规则，判断结果。
        $authList = [];   //
        foreach ($rules as $rule) {
            $authList[] = strtolower($rule['name']);
        }
        $_authList[$uid] = $authList;

        return array_unique($authList);
    }
}
