<?php

declare (strict_types=1);

namespace plugin\shop\controller\api\auth;

use plugin\account\controller\api\auth;
use plugin\shop\model\PluginShopGoods;
use plugin\shop\model\PluginShopGoodsItem;
use plugin\shop\model\PluginShopOrderCart;
use plugin\shop\service\UserAction;
use think\admin\helper\QueryHelper;
use think\db\exception\DbException;
use think\db\Query;

/**
 * 购物车接口
 * @class Cart
 * @package plugin\shop\controller\api\auth
 */
class Cart extends Auth
{
    /**
     * 获取购买车数据
     * @return void
     */
    public function get()
    {
        PluginShopOrderCart::mQuery(null, function (QueryHelper $query) {
            $query->equal('ghash')->where(['unid' => $this->unid])->with([
                'goods' => static function (Query $query) {
                    $query->with('items');
                },
                'specs' => static function (Query $query) {
                    $query->withoutField('id,create_time,update_time');
                },
            ]);
            $this->success('获取购买车数据！', $query->order('id desc')->page(false, false));
        });
    }

    /**
     * 修改购买车数据
     * @return void
     * @throws DbException
     */
    public function set()
    {
        $data = $this->_vali([
            'unid.value'     => $this->unid,
            'ghash.require'  => '商品不能为空！',
            'number.require' => '数量不能为空！',
        ]);
        // 清理数量0的记录
        $map = ['unid' => $this->unid, 'ghash' => $data['ghash']];
        if ($data['number'] < 1) {
            PluginShopOrderCart::mk()->where($map)->delete();
            UserAction::recount($this->unid);
            $this->success('移除成功！');
        }
        // 检查商品是否存在
        $gspec = PluginShopGoodsItem::mk()->where(['ghash' => $data['ghash']])->findOrEmpty();
        $goods = PluginShopGoods::mk()->where(['code' => $gspec->getAttr('gcode')])->findOrEmpty();
        if ($goods->isEmpty() || $gspec->isEmpty()) $this->error('商品不存在！');
        // 保存商品数据
        $data += ['gcode' => $gspec['gcode'], 'gspec' => $gspec['gspec']];
        if (($cart = PluginShopOrderCart::mk()->where($map)->findOrEmpty())->save($data)) {
            UserAction::recount($this->unid);
            $this->success('保存成功！', $cart->refresh()->toArray());
        } else {
            $this->error('保存失败！');
        }
    }
}