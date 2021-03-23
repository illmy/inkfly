<?php 

namespace app\model;

use app\exceptions\InvalidRequestException;

class Voting extends Model
{
    const VOTING_SCORE = [1, 2, 3, 4, 5];

    public function create(array $data = []):array
    {
        $data['company_id'] = $this->username['company_id'];
        $data['created_by'] = $this->username['username'];
        $data['created_at'] = date('Y-m-d H:i:s');
        $result = $this->insert($data);

        if ($result) {
            return $data;
        }

        throw new InvalidRequestException("新增失败");
    }
}