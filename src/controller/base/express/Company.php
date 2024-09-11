<?php

declare (strict_types=1);

namespace plugin\shop\controller\base\express;

use plugin\shop\model\PluginShopExpressCompany;
use plugin\shop\service\ExpressService;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;

/**
 * 快递公司管理
 * @class Company
 * @package plugin\shop\controller\base\express
 */
class Company extends Controller
{
    /**
     * 快递公司管理
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $this->type = $this->get['type'] ?? 'index';
        PluginShopExpressCompany::mQuery()->layTable(function () {
            $this->title = '快递公司管理';
        }, function (QueryHelper $query) {
            $query->like('name,code')->equal('status')->dateBetween('create_time');
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
        });
    }

    /**
     * 添加快递公司
     * @auth true
     */
    public function add()
    {
        $this->title = '添加快递公司';
        PluginShopExpressCompany::mForm('form');
    }

    /**
     * 编辑快递公司
     * @auth true
     */
    public function edit()
    {
        $this->title = '编辑快递公司';
        PluginShopExpressCompany::mForm('form');
    }

    /**
     * 修改快递公司状态
     * @auth true
     */
    public function state()
    {
        PluginShopExpressCompany::mSave($this->_vali([
            'status.in:0,1'  => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除快递公司
     * @auth true
     */
    public function remove()
    {
        PluginShopExpressCompany::mDelete();
    }

    /**
     * 同步快递公司
     * @auth true
     */
    public function sync()
    {
        try {
            $result = ExpressService::company();
            if (empty($result['code'])) $this->error($result['info']);
            foreach ($result['data'] as $vo) PluginShopExpressCompany::mUpdate([
                'name' => $vo['title'], 'code' => $vo['code_2'], 'deleted' => 0,
            ], 'code');
            $this->success('同步快递公司成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error('同步快递公司数据失败！');
        }
    }
}