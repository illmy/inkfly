<?php 

namespace app\controller;

use app\model\Voting as VoteModel;
use app\validate\Voting as VoteValidate;

class Voting extends Base
{
    public function store(VoteModel $model, VoteValidate $validate)
    {
        // 参数验证
        $this->validate($validate->votingRules, $this->requestParam);
        $result = $model->initData($this->userData)->create($this->requestParam);

        return $this->success($result);
    }

    public function ranking(VoteModel $model)
    {
        $result = $model->initData($this->userData)->rankingList($this->requestParam);

        return $this->success($result);
    }

    public function rdetails(VoteModel $model)
    {
        $result = $model->initData($this->userData)->rankingListDetails($this->requestParam);

        return $this->success($result);
    }

    public function voting(VoteModel $model)
    {
        $result = $model->initData($this->userData)->votingList($this->requestParam);

        return $this->success($result);
    }

    public function vdetails(VoteModel $model)
    {
        $result = $model->initData($this->userData)->votingListDetails($this->requestParam);

        return $this->success($result);
    }
}