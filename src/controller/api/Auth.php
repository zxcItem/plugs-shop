<?php

declare (strict_types=1);

namespace plugin\shop\controller\api;

use plugin\account\controller\api\Auth as AccountAuth;
use think\exception\HttpResponseException;

/**
 * 接口授权抽象类
 * @class Auth
 * @package plugin\shop\controller\api
 */
abstract class Auth extends AccountAuth
{

    /**
     * 控制器初始化
     * @return void
     */
    protected function initialize()
    {
        try {
            parent::initialize();
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}