<?php

namespace app\model;

use app\exceptions\InvalidRequestException;
use app\tools\Quarter;

class Voting extends Model
{
    const VOTING_SCORE = [1, 2, 3, 4, 5];

    protected $table = 'voting_record';

    protected $queryWhereField = [
        
    ];

    protected $queryShowField = ['*'];

    protected $queryOrderField = [
        
    ];

    protected $queryGroupField = '';

    protected function beforeList($query = [])
    {

    }

    public function create(array $data = []): array
    {
        if (!in_array($data['voting_score'], self::VOTING_SCORE)) {
            throw new InvalidRequestException("投票分数不存在");
        }
        $quarter = Quarter::getQuarter();
        if (!isset($quarter[$data['year'] . $data['quarter']])) {
            throw new InvalidRequestException("季度不存在");
        }

        $userList  = (new User())->where('id', '=', $data['manager_id'])->find();
        if (empty($userList)) {
            throw new InvalidRequestException("经理不存在");
        }

        $manList  = (new Department())->where('manager_id', '=', $data['manager_id'])->find();
        if (empty($manList)) {
            throw new InvalidRequestException("不是经理");
        }

        $init = [
            'year' => $data['year'],
            'quarter' => $data['quarter'],
            'user_id' => $this->userData['id'],
            'manager_id' => $data['manager_id']
        ];

        foreach ($init as $key => $value) {
            $this->where($key, '=', $value);
        }
        $list = $this->find();
        if (!empty($list)) {
            throw new InvalidRequestException("本季度该经理已投票");
        }

        $init['company_id'] = $this->userData['company_id'];
        $init['year_quarter'] = $data['year'] . $data['quarter'];
        $init['voting_score'] = $data['voting_score'];
        $init['created_at'] = date('Y-m-d H:i:s');
        $result = $this->insert($init);

        if ($result) {
            $init['id'] = $result;
            return $init;
        }

        throw new InvalidRequestException("新增失败");
    }

    public function rankingList(array $query = [])
    {
        if (!empty($query['year_quarter'])) {
            [$query['year'], $query['quarter']] = explode('-', $query['year_quarter']);
        }
        $companyId = $this->userData['company_id'];
        $year = $query['year'] ?? date("Y");
        $quarter = $query['quarter'] ?? Quarter::getNowQuarter()['quarter'];
        $sql = <<<sql
        SELECT
    u.id,
	u.nickname,
	sum( v.voting_score ) as voting_score,
	v.year,
	v.quarter 
FROM
	voting_record AS v
	LEFT JOIN users AS u ON u.id = v.manager_id 
WHERE
	v.company_id = {$companyId} 
	AND v.year = {$year} 
	AND v.quarter = {$quarter} 
GROUP BY
	v.manager_id
ORDER BY 
    voting_score desc
sql;        
        $list = $this->query($sql);
        $listColumn = array_column($list, null, 'id');
        // 获取所有部门经理
        $user = (new User())->getAllManager();

        foreach ($user as $value) {
            if (!isset($listColumn[$value['id']])) {
                $push = [
                    'id' => $value['id'],
                    'nickname' => $value['nickname'],
                    'voting_score' => 0,
                    'year' => $year,
                    'quarter' => $quarter
                ];
                array_push($list, $push);
            }
        }
        return $list;
    }

    public function rankingListDetails(array $query = [])
    {
        $quarter = Quarter::getQuarter();
        if (!isset($quarter[$query['year'] . $query['quarter']])) {
            throw new InvalidRequestException("季度不存在");
        }
        $manList  = (new Department())->where('manager_id', '=', $query['manager_id'])->find();
        if (empty($manList)) {
            throw new InvalidRequestException("不是经理");
        }
        $this->alias('v')->leftJoin('users as u', 'u.id', 'v.user_id');
        $this->queryWhereField = [
            ['u.id', '=', '%VALUE%'],
            ['v.manager_id', '=', '%VALUE%'],
            ['v.company_id', '=', '%VALUE%'],
            ['v.year', '=', '%VALUE%'],
            ['v.quarter', '=', '%VALUE%']
        ];
    
        $this->queryShowField = ['u.nickname', 'v.voting_score', 'v.created_at', 'v.year', 'v.quarter'];
        
        $this->queryOrderField = [
            ['v.voting_score', 'desc']
        ];
        $list = [
            'company_id' => $this->userData['company_id'],
            'manager_id' => $query['manager_id'],
            'year' => $query['year'],
            'quarter' => $query['quarter'],
        ];

        $list = array_merge($query, $list);

        $list = $this->list($list);

        return $list;
    }

    public function votingList()
    {
        $this->queryWhereField = [
            ['company_id', '=', '%VALUE%'],
            ['user_id', '=', '%VALUE%']
        ];
        $this->queryGroupField = 'year_quarter';
        $this->queryShowField = ['count(year_quarter) as qcount', 'sum(voting_score) as qsum', 'year', 'quarter', 'year_quarter'];
        $this->queryOrderField = [
            ['id', 'desc']
        ];
        $query = [
            'company_id' => $this->userData['company_id'],
            'user_id' => $this->userData['id']
        ];
        $list = $this->list($query);
        $list = $this->dealVoting($list);
        return $list;
    }

    private function dealVoting(array $list = [])
    {
        $quarter = Quarter::getQuarter();
        $list = array_column($list, null, 'year_quarter');
        foreach ($quarter as $key => &$value) {
            if (isset($list[$key])) {
                $value = $list[$key];
            } else {
                $value['qcount'] = 0;
                $value['qsum'] = 0;
            }
        }
        $quarter = array_reverse($quarter);
        return $quarter;
    }

    public function votingListDetails(array $query)
    {
        $this->alias('v')->leftJoin('users as u', 'u.id', 'v.manager_id');
        $this->queryWhereField = [
            ['v.user_id', '=', '%VALUE%'],
            ['v.year', '=', '%VALUE%'],
            ['v.quarter', '=', '%VALUE%'],
        ];
        $this->queryShowField = ['u.nickname', 'v.voting_score', 'v.created_at', 'v.year', 'v.quarter', 'v.manager_id'];
        $this->queryOrderField = [
            ['v.voting_score', 'desc'] 
        ];
        $query = [
            'user_id' => $this->userData['id'],
            'year' => $query['year'],
            'quarter' => $query['quarter']
        ];
        $list = $this->list($query);

        return $list;
    }

    public function manager()
    {
        // 获取所有部门经理
        $user = (new User())->getAllManager();

        return $user;
    }
}
