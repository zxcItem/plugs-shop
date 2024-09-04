<?php


namespace plugin\shop\model;


use think\model\relation\HasOne;

/**
 * 商城用户评论
 * Class ShopActionComment
 * @package plugin\shop\model
 */
class ShopActionComment extends AbsUser
{
    /**
     * 关联商品信息
     * @return HasOne
     */
    public function goods(): HasOne
    {
        return $this->hasOne(ShopGoods::class, 'code', 'gcode');
    }

    /**
     * 关联订单数据
     * @return HasOne
     */
    public function orderinfo(): HasOne
    {
        return $this->hasOne(ShopOrder::class, 'order_no', 'order_no');
    }

    /**
     * 绑定商品信息
     * @return HasOne
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