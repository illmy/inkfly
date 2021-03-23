<?php

/**
 * jwt中间件验证
 * 
 */

namespace app\middleware;

use app\tools\Jwt as Jswt;
use elaborate\Response;

class Jwt
{
    public static function handle($request, \Closure $next)
    {
        $act = $request->action();
        $action = ['login', 'test'];
        if (in_array($act, $action)) {
            return $next($request);
        }

        $access_token = $request->header('X-Token');

        $decoded = Jswt::decode($access_token);
        if (is_array($decoded)) {
            return $next($request);
        } else {
            $result = [
                'code' => 1001,
                'msg'  => $decoded,
                'data' => [],
            ];

            return Response::create($result, 'json');
        }
    }
}
