<?php

declare (strict_types=1);

namespace plugin\shop;

use plugin\shop\command\Clear;
use think\admin\Plugin;

/**
 * 组件注册服务
 * @class Service
 * @package plugin\shop
 */
class Service extends Plugin
{
    /**
     * 定义插件名称
     * @var string
     */
    protected $appName = '通用商城';

    /**
     * 定义安装包名
     * @var string
     */
    protected $package = 'xiaochao/plugs-shop';

    /**
     * 插件服务注册
     * @return void
     */
    public function register(): void
    {
        $this->commands([Clear::class]);
    }

    /**
     * 用户模块菜单配置
     * @return array[]
     */
    public static function menu(): array
    {
        $code = app(static::class)->appCode;
        // 设置插件菜单
        return [
            [
                'name' => '商城管理',
                'subs' => [
                    ['name' => '商城参数管理', 'icon' => 'layui-icon layui-icon-set', 'node' => "{$code}/base.config/index"],
                    ['name' => '邀请海报设置', 'icon' => 'layui-icon layui-icon-cols', 'node' => "{$code}/base.poster/index"],
                    ['name' => '商品数据管理', 'icon' => 'layui-icon layui-icon-star', 'node' => "{$code}/shop.goods/index"],
                    ['name' => '订单数据管理', 'icon' => 'layui-icon layui-icon-template', 'node' => "{$code}/shop.order/index"],
                    ['name' => '订单发货管理', 'icon' => 'layui-icon layui-icon-transfer', 'node' => "{$code}/shop.send/index"],
                    ['name' => '快递公司管理', 'icon' => 'layui-icon layui-icon-website', 'node' => "{$code}/base.express.company/index"],
                    ['name' => '邮费模板管理', 'icon' => 'layui-icon layui-icon-template-1', 'node' => "{$code}/base.express.template/index"],
                ],
            ]
        ];
    }
}