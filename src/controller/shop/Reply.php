<?php


declare (strict_types=1);

namespace plugin\shop\controller\shop;

use plugin\account\model\AccountUser;
use plugin\shop\model\ShopActionComment;
use plugin\shop\model\ShopGoods;
use think\admin\Controller;
use think\admin\helper\QueryHelper;

/**
 * 商品评论管理
 * @class Reply
 * @package plugin\wemall\controller\shop
 */
class Reply extends Controller
{
    /**
     * 商品评论管理
     * @auth true
     * @menu true
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $this->type = $this->request->get('type', 'index');
        ShopActionComment::mQuery()->layTable(function () {
            $this->title = '商品评论管理';
        }, function (QueryHelper $query) {
            // 用户查询
            $db = AccountUser::mQuery()->like('phone|nickname#user_keys')->db();
            if ($db->getOptions('where')) $query->whereRaw("unid in {$db->field('id')->buildSql()}");
            // 商品查询
            $db = ShopGoods::mQuery()->like('code|name#goods_keys')->db();
            if ($db->getOptions('where')) $query->whereRaw("gcode in {$db->field('code')->buildSql()}");
            // 数据过滤
            $query->like('order_no')->where(['status' => intval($this->type === 'index'), 'deleted' => 0]);
            $query->with(['bindUser', 'bindGoods'])->dateBetween('create_time');
        });
    }

    /**
     * 修改评论内容
     * @auth true
     * @return void
     */
    public function edit()
    {
        ShopActionComment::mQuery()->with(['user', 'goods', 'orderinfo'])->mForm('form');
    }

    /**
     * 修改评论状态
     * @auth true
     */
    public function state()
    {
        ShopActionComment::mSave($this->_vali([
            'status.in:0,1'  => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }
}