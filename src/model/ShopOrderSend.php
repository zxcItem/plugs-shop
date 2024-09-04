<?php

namespace plugin\shop\model;

use plugin\account\model\AccountUser;
use think\model\relation\HasOne;

/**
 * 商城订单配送模型
 */
class ShopOrderSend extends AbsUser
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

    /**
     * 设置发货时间
     * @param mixed $value
     * @return string
     */
    public function setExpressTimeAttr($value): string
    {
        return $this->setCreateTimeAttr($value);
    }

    /**
     * 获取发货时间
     * @param mixed $value
     * @return string
     */
    public function getExpressTimeAttr($value): string
    {
        return $this->getCreateTimeAttr($value);
    }
}