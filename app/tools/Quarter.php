<?php 

namespace app\tools;

/**
 * 返回开始时间到当前时间的季度
 */
class quarter
{
    protected $startYearMonth = '2020-01';

    protected $mapping = [
        '1' => '1',
        '2' => '1',
        '3' => '1',
        '4' => '2',
        '5' => '2',
        '6' => '2',
        '7' => '3',
        '8' => '3',
        '9' => '3',
        '10' => '4',
        '11' => '4',
        '12' => '4',
    ];

    public function getQuarter()
    {
        
    }
}