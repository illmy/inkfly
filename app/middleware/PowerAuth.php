<?php

/**
 * 权限验证
 */

namespace app\middleware;

use app\tools\Jwt as Jswt;
use app\tools\Auth;
use elaborate\Response;
use app\exceptions\InvalidRequestException;

class PowerAuth
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

        $rule = strtolower($con . '/' . $act);
        $result = (new Auth())->check($rule, $decoded['id']);

        if ($result) {
            return $next($request);
        } else {
            throw new InvalidRequestException('未授权访问', 1002);
        }
    }

    private static function whiteListController()
    {
        $controller = [
            'Index', 'Test', 'Init'
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
