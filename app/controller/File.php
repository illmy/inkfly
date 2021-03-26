<?php

namespace app\controller;

use app\exceptions\InvalidRequestException;
use SplFileObject;
use elaborate\Response;

class File extends Base
{
    /**
     * 常用扩展名与content-type对应关系
     * @var array
     */
    protected $extToTpye = [
        'doc' => 'application/msword','gif' => 'image/gif','html' => 'text/html','img' => 'application/x-img',
        'jpe' => 'image/jpeg','jpeg' => 'image/jpeg','jpg' => 'application/x-jpg','png' => 'application/x-png',
        'wav' => 'audio/wav','mp3' => 'audio/mp3','xls' => 'application/vnd.ms-excel','csv' => 'text/csv','xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document','pdf' => 'application/pdf',
        'zip' => 'application/octet-stream','rar' => 'application/octet-stream',
        'json' => 'application/json',
    ];

    protected $extToMethod = [
        'doc' => 'wordToHtml','gif' => 'readFile','html' => 'readFile','img' => 'readFile',
        'jpe' => 'readFile','jpeg' => 'readFile','jpg' => 'readFile','png' => 'readFile',
        'wav' => 'readFile','mp3' => 'readFile','xls' => 'excelToHtml','xlsx' => 'excelToHtml',
        'docx' => 'wordToHtml','pdf' => 'readFile'
    ];

    public function read()
    {
        //接受参数 文件目录path 文件名filename 自定义文件名 setname
        $data = $this->requestParam;
        if (empty($data['filename'])) {
            throw new InvalidRequestException('文件不存在');
        }
        if (!empty($data['path'])) {
            $file = $data['path'] . DIRECTORY_SEPARATOR . $data['filename'];
        } else {
            $file = $data['filename'];
        }

        //判断文件是否存在
        if (!file_exists($file)) {
            throw new InvalidRequestException('文件不存在');
        }
        $Object = new SplFileObject($file, 'r');
        //文件扩展名
        $filext = $Object->getExtension();
        if (!isset($this->extToTpye[$filext])) {
            throw new InvalidRequestException('未设置content-type');
        }
        if (!empty($data['setname'])) {
            $setname = $data['setname'];
        } else {
            $setname = $data['filename'];
        }

        if ($Object->getSize() <= 0) {
            throw new InvalidRequestException('文件错误');
        }
        //文件内容
        $fileContent = $Object->fread($Object->getSize());
        return Response::create($fileContent)->contentType($this->extToTpye[$filext], "; charset=utf-8")
            ->header(['Content-disposition' => 'attachment; filename=' . $setname])
            ->header(['Content-Length' => $Object->getSize()])
            ->header(['Accept-Ranges' => 'bytes']);
    }
}
