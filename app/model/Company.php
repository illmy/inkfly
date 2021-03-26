<?php

/**
 * 公司
 */

namespace app\model;

use app\exceptions\InvalidRequestException;

class Company extends Model
{
    protected $table = 'companies';

    public function logo(string $fileName, string $path = '')
    {
        $data = [
            'logo' => $path . $fileName
        ];
        $result = $this->where('id', '=', $this->userData['company_id'])->update($data);

        if ($result) {
            return $data;
        }

        throw new InvalidRequestException("设置logo失败");
    }
    
    public function info() 
    {
        $field = ['id', 'company_name', 'logo'];
        $exists = $this->where('id', '=', $this->userData['company_id'])
                        ->field($field)
                        ->find();

        if (empty($exists)) {
            throw new InvalidRequestException('公司不存在');
        }

        return $exists;
    }

}
