<?php

namespace app\model;

use app\exceptions\InvalidRequestException;

class Voting extends Model
{
    const VOTING_SCORE = [1, 2, 3, 4, 5];

    protected $table = 'voting_record';

    protected function beforeList($query = [])
    {

    }

    public function create(array $data = []): array
    {
        $data = [
            'year' => $data['year'],
            'quarter' => $data['quarter'],
            'user_id' => $this->userData['id'],
            'manager_id' => $data['manager_id']
        ];

        foreach ($$data as $key => $value) {
            $this->where($key, '=', $value);
        }
        $list = $this->find();
        if (!empty($list)) {
            throw new InvalidRequestException("本季度该经理已投票");
        }

        $data['voting_score'] = $data['voting_score'];
        $data['created_at'] = date('Y-m-d H:i:s');
        $result = $this->insert($data);

        if ($result) {
            return $data;
        }

        throw new InvalidRequestException("新增失败");
    }
}
