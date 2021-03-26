<?php

namespace app\controller;

use app\tools\Upload as UploadTool;
use app\exceptions\InvalidRequestException;
use app\model\Company;

class Upload extends Base
{
    public function move()
    {
        $file = new UploadTool();
        $result = $file->move();

        if (!$result) {
            var_dump($file->getErrors());
            throw new InvalidRequestException($file->getErrors()); 
        }
        $this->notiy($result);
        return $this->success($result, '上传文件成功');
    }

    private function notiy($result)
    {
        $result = (new Company())->initData($this->userData)->logo($result['name'], $result['path']);
    }
}
