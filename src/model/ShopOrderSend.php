<?php

namespace plugin\shop\model;

use plugin\account\model\Abs;
use plugin\account\model\AccountUser;
use think\model\relation\HasOne;

/**
 * 商城订单配送模型
 */
class ShopOrderSend extends Abs
{
    /**
     * 关联用户数据
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(AccountUser::class, 'id', 'unid');
    }

    /**
     * 关联订单数据
     * @return HasOne
     */
    public function main(): HasOne
    {
        return $this->hasOne(ShopOrder::class, 'order_no', 'order_no')->with(['items']);
    }
}