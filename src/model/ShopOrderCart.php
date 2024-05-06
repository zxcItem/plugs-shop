<?php

namespace plugin\shop\model;

use think\model\relation\HasOne;

/**
 * 商城订单购物车模型
 */
class ShopOrderCart extends AbsUser
{
    /**
     * 关联产品数据
     * @return HasOne
     */
    public function goods(): HasOne
    {
        return $this->hasOne(ShopGoods::class, 'code', 'gcode');
    }

    /**
     * 关联规格数据
     * @return HasOne
     */
    public function specs(): HasOne
    {
        return $this->hasOne(ShopGoodsItem::class, 'ghash', 'ghash');
    }
}