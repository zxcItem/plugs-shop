<?php

declare (strict_types=1);

namespace plugin\shop\controller\shop;

use plugin\shop\model\PluginShopExpressTemplate;
use plugin\shop\model\PluginShopGoods;
use plugin\shop\model\PluginShopGoodsCate;
use plugin\shop\model\PluginShopGoodsItem;
use plugin\shop\model\PluginShopGoodsMark;
use plugin\shop\model\PluginShopGoodsStock;
use plugin\shop\service\ConfigService;
use plugin\shop\service\GoodsService;
use think\admin\Controller;
use think\admin\Exception;
use think\admin\extend\CodeExtend;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;

/**
 * 商品数据管理
 * @class Goods
 * @package plugin\shop\controller\shop
 */
class Goods extends Controller
{
    /**
     * 商品数据管理
     * @auth true
     * @menu true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $this->type = $this->request->get('type', 'index');
        PluginShopGoods::mQuery($this->get)->layTable(function () {
            $this->title = '商品数据管理';
            $this->cates = PluginShopGoodsCate::items();
            $this->marks = PluginShopGoodsMark::items();
            $this->deliverys = PluginShopExpressTemplate::items(true);
            $this->enableBalance = ConfigService::get('enable_balance');
            $this->enableIntegral = ConfigService::get('enable_integral');
        }, function (QueryHelper $query) {
            $query->withoutField('specs,content')->like('code|name#name')->like('marks,cates', ',');
            $query->equal('status')->dateBetween('create_time');
            $query->where(['status' => intval($this->type === 'index'), 'deleted' => 0]);
        });
    }

    /**
     * 商品选择器
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

    /**
     * 添加商品数据
     * @auth true
     */
    public function add()
    {
        $this->mode = 'add';
        $this->title = '添加商品数据';
        PluginShopGoods::mForm('form', 'code');
    }

    /**
     * 编辑商品数据
     * @auth true
     */
    public function edit()
    {
        $this->mode = 'edit';
        $this->title = '编辑商品数据';
        PluginShopGoods::mForm('form', 'code');
    }

    /**
     * 复制编辑商品
     * @auth true
     */
    public function copy()
    {
        $this->mode = 'copy';
        $this->title = '复制编辑商品';
        PluginShopGoods::mForm('form', 'code');
    }

    /**
     * 表单数据处理
     * @param array $data
     */
    protected function _copy_form_filter(array &$data)
    {
        if ($this->request->isPost()) {
            $data['code'] = CodeExtend::uniqidNumber(16, 'G');
        }
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws Exception
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function _form_filter(array &$data)
    {
        if (empty($data['code'])) {
            $data['code'] = CodeExtend::uniqidNumber(16, 'G');
        }
        if ($this->request->isGet()) {
            $this->marks = PluginShopGoodsMark::items();
            $this->cates = PluginShopGoodsCate::items(true);

            $this->deliverys = PluginShopExpressTemplate::items(true);
            $this->enableBalance = ConfigService::get('enable_balance');
            $this->enableIntegral = ConfigService::get('enable_integral');
            $data['marks'] = $data['marks'] ?? [];
            $data['cates'] = $data['cates'] ?? [];
            $data['specs'] = json_encode($data['specs'] ?? [], 64 | 256);
            $data['items'] = PluginShopGoodsItem::itemsJson($data['code']);
            $data['slider'] = is_array($data['slider'] ?? []) ? join('|', $data['slider'] ?? []) : '';
            $data['delivery_code'] = $data['delivery_code'] ?? 'FREE';
        } elseif ($this->request->isPost()) try {
            if (empty($data['cover'])) $this->error('商品图片不能为空！');
            if (empty($data['slider'])) $this->error('轮播图片不能为空！');
            // 商品规格保存
            [$count, $items] = [0, json_decode($data['items'], true)];
            $data['marks'] = arr2str($data['marks'] ?? []);
            foreach ($items as $item) if ($item['status'] > 0) {
                $count++;
                $data['price_market'] = min($data['price_market'] ?? $item['market'], $item['market']);
                $data['price_selling'] = min($data['price_selling'] ?? $item['selling'], $item['selling']);
                $data['allow_balance'] = max($data['allow_balance'] ?? $item['allow_balance'], $item['allow_balance']);
                $data['allow_integral'] = max($data['allow_integral'] ?? $item['allow_integral'], $item['allow_integral']);
            }
            if (empty($count)) $this->error('无效的的商品价格信息！');
            $this->app->db->transaction(static function () use ($data, $items) {
                // 标识所有规格无效
                PluginShopGoodsItem::mk()->where(['gcode' => $data['code']])->update(['status' => 0]);
                $model = PluginShopGoods::mk()->where(['code' => $data['code']])->findOrEmpty();
                $model->{$model->isExists() ? 'onAdminUpdate' : 'onAdminInsert'}($data['code']);
                $model->save($data);
                // 更新或写入商品规格
                foreach ($items as $item) PluginShopGoodsItem::mUpdate([
                    'gsku'            => $item['gsku'],
                    'ghash'           => $item['hash'],
                    'gcode'           => $data['code'],
                    'gspec'           => $item['spec'],
                    'gimage'          => $item['image'],
                    'status'          => $item['status'] ? 1 : 0,
                    'price_cost'      => $item['cost'],
                    'price_market'    => $item['market'],
                    'price_selling'   => $item['selling'],
                    'allow_balance'   => $item['allow_balance'],
                    'allow_integral'  => $item['allow_integral'],
                    'number_virtual'  => $item['virtual'],
                    'number_express'  => $item['express'],
                    'reward_balance'  => $item['balance'],
                    'reward_integral' => $item['integral'],
                ], 'ghash', ['gcode' => $data['code']]);
            });
            // 刷新产品库存
            GoodsService::stock($data['code']);
            $this->success('商品编辑成功！', 'javascript:history.back()');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 商品库存入库
     * @auth true
     * @return void
     */
    public function stock()
    {
        $input = $this->_vali(['code.require' => '商品不能为空哦！']);
        if ($this->request->isGet()) {
            $this->vo = PluginShopGoods::mk()->where($input)->with('items')->findOrEmpty()->toArray();
            empty($this->vo) ? $this->error('无效的商品！') : $this->fetch();
        } else try {
            [$data, $post, $batch] = [[], $this->request->post(), CodeExtend::uniqidDate(12, 'B')];
            if (isset($post['gcode']) && is_array($post['gcode'])) {
                foreach (array_keys($post['gcode']) as $key) if ($post['gstock'][$key] > 0) $data[] = [
                    'batch_no' => $batch,
                    'ghash'    => $post['ghash'][$key],
                    'gcode'    => $post['gcode'][$key],
                    'gspec'    => $post['gspec'][$key],
                    'gstock'   => $post['gstock'][$key],
                ];
                empty($data) || PluginShopGoodsStock::mk()->saveAll($data);
            }
            GoodsService::stock($input['code']);
            $this->success('库存更新成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 商品上下架
     * @auth true
     */
    public function state()
    {
        PluginShopGoods::mSave($this->_vali([
            'status.in:0,1'  => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]), 'code');
    }

    /**
     * 删除商品数据
     * @auth true
     */
    public function remove()
    {
        PluginShopGoods::mSave($this->_vali([
            'code.require'  => '编号不能为空！',
            'deleted.value' => 1
        ]), 'code');
    }
}