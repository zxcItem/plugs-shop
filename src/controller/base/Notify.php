<?php

namespace plugin\shop\controller\base;

use plugin\shop\model\PluginShopConfigNotify;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 系统通知管理
 * Class Notify
 * @package plugin\shop\controller\base
 */
class Notify extends Controller
{
    /**
     * 系统通知管理
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $this->type = $this->get['type'] ?? 'index';
        PluginShopConfigNotify::mQuery()->layTable(function () {
            $this->title = '系统通知管理';
        }, function (QueryHelper $query) {
            $query->like('name,code')->equal('status')->dateBetween('create_time');
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
        });
    }

    /**
     * 添加系统通知
     * @auth true
     */
    public function add()
    {
        $this->title = '添加系统通知';
        PluginShopConfigNotify::mForm('form');
    }

    /**
     * 编辑系统通知
     * @auth true
     */
    public function edit()
    {
        $this->title = '编辑系统通知';
        PluginShopConfigNotify::mForm('form');
    }

    /**
     * 表单结果处理
     * @param boolean $state
     */
    protected function _form_result(bool $state)
    {
        if ($state) {
            $this->success('内容保存成功！', 'javascript:history.back()');
        }
    }

    /**
     * 修改通知状态
     * @auth true
     */
    public function state()
    {
        PluginShopConfigNotify::mSave($this->_vali([
            'status.in:0,1'  => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除系统通知
     * @auth true
     */
    public function remove()
    {
        PluginShopConfigNotify::mDelete();
    }
}