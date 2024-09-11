<?php

declare (strict_types=1);

namespace plugin\shop\model;

use think\model\relation\HasOne;

/**
 * 商城订单发货模型
 * @class PluginShopOrderSender
 * @package plugin\shop\model
 */
class PluginShopOrderSender extends AbsUser
{
    /**
     * 关联订单数据
     * @return \think\model\relation\HasOne
     */
    public function main(): HasOne
    {
        return $this->hasOne(PluginShopOrder::class, 'order_no', 'order_no')->with(['items']);
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