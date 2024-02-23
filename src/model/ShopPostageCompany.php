<?php

namespace plugin\shop\model;

use plugin\account\model\Abs;

/**
 * 快递公司模型
 */
class ShopPostageCompany extends Abs
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