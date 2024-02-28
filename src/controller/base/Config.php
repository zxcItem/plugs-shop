<?php

declare (strict_types=1);

namespace plugin\shop\controller\base;

use plugin\shop\service\ConfigService;
use think\admin\Controller;
use think\admin\Exception;

/**
 * 应用参数配置
 * @class Config
 * @package plugin\shop\controller\base
 */
class Config extends Controller
{

    /**
     * 跳转规则定义
     * @var string[]
     */
    protected $rules = [
        '#'  => ['name' => '不跳转'],
        'LK' => ['name' => '自定义链接'],
        'NL' => ['name' => '文章内容列表'],
        'NP' => ['name' => '文章内容详情', 'node' => 'plugin-shop/base.news/select'],
        'SL' => ['name' => '商城商品列表'],
        'SP' => ['name' => '商品内容详情', 'node' => 'plugin-shop/shop.goods/select'],
    ];

    /**
     * 商城参数配置
     * @auth true
     * @menu true
     * @return void
     * @throws Exception
     */
    public function index()
    {
        $this->title = '商城参数配置';
        $this->data = ConfigService::get();
        $this->pages = ConfigService::$pageTypes;
        $this->fetch();
    }

    /**
     * 修改参数配置
     * @auth true
     * @return void
     * @throws Exception
     */
    public function params()
    {
        if ($this->request->isGet()) {
            $this->vo = ConfigService::get();
            $this->fetch('index_params');
        } else {
            ConfigService::set($this->request->post());
            $this->success('配置更新成功！');
        }
    }

    /**
     * 修改协议内容
     * @auth true
     * @return void
     * @throws Exception
     */
    public function content()
    {
        $input = $this->_vali(['code.require' => '编号不能为空！']);
        $title = ConfigService::pageTypes($input['code']) ?? null;
        if (empty($title)) $this->error('无效的内容编号！');
        if ($this->request->isGet()) {
            $this->title = "编辑{$title}";
            $this->data = ConfigService::getPage($input['code']);
            $this->fetch('index_content');
        } elseif ($this->request->isPost()) {
            ConfigService::setPage($input['code'], $this->request->post());
            $this->success('内容保存成功！', 'javascript:history.back()');
        }
    }

    /**
     * 首页轮播
     * @auth true
     * @return void
     * @throws Exception
     */
    public function slider()
    {
        $input = $this->_vali(['code.require' => '编号不能为空！']);
        $title = ConfigService::pageTypes($input['code']) ?? null;
        if (empty($title)) $this->error('无效的内容编号！');
        if ($this->request->isGet()) {
            $this->title = "编辑{$title}";
            $this->data = ConfigService::getPage($input['code']);
            $this->fetch('index_slider');
        } elseif ($this->request->isPost()) {
            if (is_string(input('data'))) {
                $data = json_decode(input('data'), true) ?: [];
            } else {
                $data = $this->request->post();
            }
            ConfigService::setPage($input['code'], $data);
            $this->success('内容保存成功！', 'javascript:history.back()');
        }
    }

}