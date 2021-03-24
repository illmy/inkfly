<?php 

namespace app\tools;

/**
 * 返回开始时间到当前时间的季度
 */
class Quarter
{
    protected static $startYearMonth = '2020-01';

    protected static $mapping = [
        '01' => '1',
        '02' => '1',
        '03' => '1',
        '04' => '2',
        '05' => '2',
        '06' => '2',
        '07' => '3',
        '08' => '3',
        '09' => '3',
        '10' => '4',
        '11' => '4',
        '12' => '4',
    ];

    protected static $data = [];

    public static function getQuarter()
    {
        $endYearMonth = date('Y-m');
        $yearMonth = self::$startYearMonth;
        while ($yearMonth <= $endYearMonth) {
            $timeYearMonth = strtotime($yearMonth);
            $year = date("Y", $timeYearMonth);
            $month = date("m", $timeYearMonth);
            $quarter = self::$mapping[$month];
            $mark = $year . $quarter;
            $arr = ['year' => $year, 'quarter' => $quarter, 'year_quarter' => $mark];
            self::$data[$mark] = $arr;

            $yearMonth = date("Y-m", strtotime("+1 month", $timeYearMonth));
        }

        return self::$data;
    }

    public static function getNowQuarter(string $nowDate = '')
    {
        if (empty($nowDate)) {
            $year = date("Y");
            $month = date("m");
        } else {
            $strtime = strtotime($nowDate);
            $year = date("Y", $strtime);
            $month = date("m", $strtime);
        }
        $yearQuarter = $year . self::$mapping[$month];

        return ['year' => $year, 'quarter' => self::$mapping[$month], 'year_quarter' => $yearQuarter];
    }
}