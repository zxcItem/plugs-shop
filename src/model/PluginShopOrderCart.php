<?php

declare (strict_types=1);

namespace plugin\shop\model;

use think\model\relation\HasOne;

class PluginShopOrderCart extends AbsUser
{
    /**
     * 关联产品数据
     * @return \think\model\relation\HasOne
     */
    public function goods(): HasOne
    {
        return $this->hasOne(PluginShopGoods::class, 'code', 'gcode');
    }

    /**
     * 关联规格数据
     * @return \think\model\relation\HasOne
     */
    public function specs(): HasOne
    {
        return $this->hasOne(PluginShopGoodsItem::class, 'ghash', 'ghash');
    }
}