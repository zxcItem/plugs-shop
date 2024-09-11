<?php

declare (strict_types=1);

namespace plugin\shop\model;

use think\model\relation\HasOne;

/**
 * 用户评论数据模型
 * @class PluginShopUserActionComment
 * @package plugin\shop\model
 */
class PluginShopUserActionComment extends AbsUser
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
     * 关联订单数据
     * @return \think\model\relation\HasOne
     */
    public function orderinfo(): HasOne
    {
        return $this->hasOne(PluginShopOrder::class, 'order_no', 'order_no');
    }

    /**
     * 绑定商品信息
     * @return \think\model\relation\HasOne
     */
    public function bindGoods(): HasOne
    {
        return $this->goods()->bind([
            'goods_name' => 'name',
            'goods_code' => 'code',
        ]);
    }

    /**
     * 格式化图片格式
     * @param mixed $value
     * @return array
     */
    public function getImagesAttr($value): array
    {
        return str2arr($value ?? '', '|');
    }
}