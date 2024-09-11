<?php

declare (strict_types=1);

namespace plugin\shop\model;

use plugin\account\model\Abs;

/**
 * 商城商品标签数据
 * @class PluginShopGoodsMark
 * @package plugin\shop\model
 */
class PluginShopGoodsMark extends Abs
{
    /**
     * 获取所有标签
     * @return array
     */
    public static function items(): array
    {
        return static::mk()->where(['status' => 1])->order('sort desc,id desc')->column('name');
    }
}