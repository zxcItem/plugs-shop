<?php

declare (strict_types=1);

namespace plugin\shop\model;

use think\model\relation\HasOne;

/**
 * 商城订单详情模型
 * @class PluginShopOrderItem
 * @package plugin\shop\model
 */
class PluginShopOrderItem extends AbsUser
{

    /**
     * 关联订单信息
     * @return \think\model\relation\HasOne
     */
    public function main(): HasOne
    {
        return $this->hasOne(PluginShopOrder::class, 'order_no', 'order_no');
    }

    /**
     * 关联商品信息
     * @return \think\model\relation\HasOne
     */
    public function goods(): HasOne
    {
        return $this->hasOne(PluginShopGoods::class, 'code', 'gcode');
    }
}