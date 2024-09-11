<?php

declare (strict_types=1);

namespace plugin\shop\controller\shop\goods;

use plugin\shop\model\PluginShopGoodsMark;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 商品标签管理
 * @class Mark
 * @package plugin\shop\controller\shop\goods
 */
class Mark extends Controller
{
    /**
     * 商品标签管理
     * @auth true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        PluginShopGoodsMark::mQuery($this->get)->layTable(function () {
            $this->title = '商品标签管理';
        }, static function (QueryHelper $query) {
            $query->like('name')->equal('status')->dateBetween('create_time');
        });
    }

    /**
     * 添加商品标签
     * @auth true
     */
    public function add()
    {
        PluginShopGoodsMark::mForm('form');
    }

    /**
     * 编辑商品标签
     * @auth true
     */
    public function edit()
    {
        PluginShopGoodsMark::mForm('form');
    }

    /**
     * 修改商品标签状态
     * @auth true
     */
    public function state()
    {
        PluginShopGoodsMark::mSave();
    }

    /**
     * 删除商品标签
     * @auth true
     */
    public function remove()
    {
        PluginShopGoodsMark::mDelete();
    }

    /**
     * 商品标签选择kkd
     * @login true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function select()
    {
        $this->get['status'] = 1;
        $this->get['deleted'] = 0;
        $this->index();
    }
}