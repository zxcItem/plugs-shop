<?php

declare (strict_types=1);

namespace plugin\shop\model;

use plugin\account\model\Abs;
use think\model\relation\HasOne;

/**
 * 商城商品规格数据
 * @class PluginShopGoodsItem
 * @package plugin\shop\model
 */
class PluginShopGoodsItem extends Abs
{

    /**
     * 关联商品信息
     * @return \think\model\relation\HasOne
     */
    public function goods(): HasOne
    {
        return $this->hasOne(PluginShopGoods::class, 'code', 'gcode');
    }

    /**
     * 绑定商品信息
     * @return \think\model\relation\HasOne
     */
    public function bindGoods(): HasOne
    {
        return $this->goods()->bind([
            'gname'    => 'name',
            'gcover'   => 'cover',
            'gstatus'  => 'status',
            'gdeleted' => 'deleted'
        ]);
    }

    /**
     * 获取商品规格JSON数据
     * @param string $code
     * @return string
     */
    public static function itemsJson(string $code): string
    {
        return json_encode(self::itemsArray($code), 64 | 256);
    }

    /**
     * 获取商品规格数组
     * @param string $code
     * @return array
     */
    public static function itemsArray(string $code): array
    {
        return self::mk()->where(['gcode' => $code])->column([
            'gsku'            => 'gsku',
            'ghash'           => 'hash',
            'gspec'           => 'spec',
            'gcode'           => 'gcode',
            'gimage'          => 'image',
            'status'          => 'status',
            'price_cost'      => 'cost',
            'price_market'    => 'market',
            'price_selling'   => 'selling',
            'allow_balance'   => 'allow_balance',
            'allow_integral'  => 'allow_integral',
            'number_virtual'  => 'virtual',
            'number_express'  => 'express',
            'reward_balance'  => 'balance',
            'reward_integral' => 'integral',
        ], 'ghash');
    }
}