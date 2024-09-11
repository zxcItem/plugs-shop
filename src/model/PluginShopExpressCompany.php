<?php


declare (strict_types=1);

namespace plugin\shop\model;

use plugin\account\model\Abs;

/**
 * 商城快递公司数据
 * @class PluginShopExpressCompany
 * @package plugin\shop\model
 */
class PluginShopExpressCompany extends Abs
{
    /**
     * 获取快递公司数据
     * @return array
     */
    public static function items(): array
    {
        $map = ['status' => 1, 'deleted' => 0];
        return self::mk()->where($map)->order('sort desc,id desc')->column('name', 'code');
    }
}