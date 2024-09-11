<?php

declare (strict_types=1);

namespace plugin\shop\model;

use plugin\shop\service\UserRefund;
use think\model\relation\HasOne;

/**
 * 商品订单售后模型
 * @class PluginShopOrderRefund
 * @package plugin\shop\model
 */
class PluginShopOrderRefund extends AbsUser
{
    /**
     * 获取订单信息
     * @return \think\model\relation\HasOne
     */
    public function orderinfo(): HasOne
    {
        return $this->hasOne(PluginShopOrder::class, 'order_no', 'order_no');
    }

    /**
     * 格式化售后图片
     * @param mixed $value
     * @return array
     */
    public function getImagesAttr($value): array
    {
        return is_string($value) ? str2arr($value, '|') : [];
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        if (isset($data['type'])) {
            $data['typename'] = UserRefund::types[$data['type']] ?? $data['type'];
        }
        if (isset($data['reason'])) {
            $data['reasonname'] = UserRefund::reasons[$data['reason']] ?? $data['reason'];
        }
        return $data;
    }
}