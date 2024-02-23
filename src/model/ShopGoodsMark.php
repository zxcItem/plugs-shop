<?php

namespace plugin\shop\model;

use plugin\account\model\Abs;

/**
 * 商城商品标签模型
 */
class ShopGoodsMark extends Abs
{
    /**
     * 获取所有标签
     * @return array
     */
    public static function items(): array
    {
        return static::mk()->where(['status' => 1])->order('sort desc,id desc')->column('name');
    }

    /**
     * 格式化创建时间
     * @param string $value
     * @return string
     */
    public function getCreateAtAttr(string $value): string
    {
        return format_datetime($value);
    }
}