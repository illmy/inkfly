<?php

/**
 * jwt中间件验证
 * 
 */

namespace app\middleware;

use app\tools\Jwt as Jswt;
use elaborate\Response;
use app\exceptions\InvalidRequestException;

class Jwt
{
    public static function handle($request, \Closure $next)
    {
        $con = $request->controller();
        if (in_array($con, self::whiteListController())) {
            return $next($request);
        }

        $act = $request->action();
        if (in_array($act, self::whiteListAction())) {
            return $next($request);
        }

        $token = $request->header('X-Token');
        if (empty($token)) {
            $token = $request->param('X-Token');
        }
        $decoded = Jswt::decode($token);
        if (is_array($decoded)) {
            return $next($request);
        } else {
            throw new InvalidRequestException($decoded, 1001);
        }
    }

    private static function whiteListController()
    {
        $controller = [
            'Index', 'Test'
        ];
        return $controller;
    }

    private static function whiteListAction()
    {
        $action = [
            'login', 'test'
        ];
        return $action;
    }
}
