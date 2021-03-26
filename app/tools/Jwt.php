<?php

/**
 * json web token 认证
 */

namespace app\tools;

use Firebase\JWT\JWT as Jswt;

class Jwt
{
    const KEY = 'liulong@infogo.com.cn';

    /**
     * jwt编码
     *
     * @param array $data
     * @param string $iss
     * @param integer $day
     * @return void
     */
    public static function encode($data = [], $iss = '', $day = 1)
    {
        $time = time();
        $token = [
            'iss' => $iss ?: $_SERVER['HTTP_HOST'],                        //签发人
            'exp' => strtotime(date('Ymd', strtotime("+${day} days"))),    //仅当天有效
            'iat' => $time,                                                //签发时间
            'nbf' => $time,                                                //生效时间
            'data' => $data                                                //额外数据
        ];
        return $jwt = Jswt::encode($token, self::KEY);
    }

    /**
     * jwt解码
     *
     * @param string $jwt
     * @return array|string
     */
    public static function decode($jwt = '')
    {
        try {
            $decoded = Jswt::decode($jwt, self::KEY, ['HS256']);
            $arr = (array)$decoded;
            return (array)$arr['data'];
        } catch (\Firebase\JWT\SignatureInvalidException $e) {  //签名不正确
            return '签名错误';
        } catch (\Firebase\JWT\BeforeValidException $e) {  // 签名在某个时间点之后才能用
            return '签名在某个时间点之后才能用';
        } catch (\Firebase\JWT\ExpiredException $e) {  // token过期
            return '签名已过期';
        } catch (\UnexpectedValueException $e) {  //签名不正确
            return '未携带签名或签名错误';
        } catch (\Exception $e) {  //其他错误
            return '签名未知错误';
        }
    }
}
