<?php

declare (strict_types=1);

namespace plugin\shop\model;

use think\model\relation\HasOne;

/**
 * 用户访问行为数据
 * @class PluginShopUserActionHistory
 * @package plugin\shop\model
 */
class PluginShopUserActionHistory extends AbsUser
{
    /**
     * 关联商品信息
     * @return \think\model\relation\HasOne
     */
    public function goods(): HasOne
    {
        return $this->hasOne(PluginShopGoods::class, 'code', 'gcode');
    }
}