<?php

namespace plugin\shop\model;

use think\model\relation\HasOne;

/**
 * 商城订单商品模型
 */
class ShopOrderItem extends AbsUser
{
    /**
     * 关联订单信息
     * @return HasOne
     */
    public function main(): HasOne
    {
        return $this->hasOne(ShopOrder::class, 'order_no', 'order_no');
    }
}