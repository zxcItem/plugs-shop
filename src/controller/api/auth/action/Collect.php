<?php

declare (strict_types=1);

namespace plugin\shop\controller\api\auth\action;

use plugin\shop\controller\api\Auth;
use plugin\shop\model\PluginShopGoods;
use plugin\shop\model\PluginShopUserActionCollect;
use plugin\shop\service\UserAction;
use think\admin\helper\QueryHelper;
use think\db\Query;

/**
 * 用户收藏数据
 * @class Collect
 * @package plugin\shop\controller\api\auth\action
 */
class Collect extends Auth
{
    /**
     * 提交搜索记录
     * @return void
     * @throws \think\db\exception\DbException
     */
    public function set()
    {
        $data = $this->_vali([
            'unid.value'    => $this->unid,
            'gcode.require' => '商品不能为空！'
        ]);
        $map = ['code' => $data['gcode'], 'deleted' => 0];
        $goods = PluginShopGoods::mk()->where($map)->findOrEmpty();
        if ($goods->isExists()) {
            UserAction::set($this->unid, $data['gcode'], 'collect');
            $this->success('收藏成功！');
        } else {
            $this->error('收藏失败！');
        }
    }

    /**
     * 获取我的搜索记录
     * @return void
     */
    public function get()
    {
        PluginShopUserActionCollect::mQuery(null, function (QueryHelper $query) {
            // 关联商品信息
            $query->order('sort desc')->with(['goods' => function (Query $query) {
                $query->field('code,name,cover,stock_sales,stock_virtual,price_selling,status,deleted');
            }]);
            // 搜索商品信息
            $db = PluginShopGoods::mQuery()->like('name#keys');
            $query->whereRaw("gcode in {$db->field('code')->buildSql()}");
            $query->where(['unid' => $this->unid])->like('gcode');
            [$page, $limit] = [intval(input('page', 1)), intval(input('limit', 10))];
            $this->success('我的收藏记录！', $query->page($page, false, false, $limit));
        });
    }

    /**
     * 删除收藏记录
     * @return void
     * @throws \think\db\exception\DbException
     */
    public function del()
    {
        $data = $this->_vali(['gcode.require' => '商品不能为空！']);
        UserAction::del($this->unid, $data['gcode'], 'collect');
        $this->success('删除记录成功！');
    }

    /**
     * 清空收藏记录
     * @return void
     * @throws \think\db\exception\DbException
     */
    public function clear()
    {
        PluginShopUserActionCollect::mk()->where(['unid' => $this->unid])->delete();
        UserAction::clear($this->unid, 'collect');
        $this->success('清理记录成功！');
    }
}