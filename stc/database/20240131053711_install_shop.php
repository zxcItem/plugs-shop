<?php

use plugin\shop\Service;
use think\admin\extend\PhinxExtend;
use think\migration\Migrator;

/**
 * 商城数据表设计
 */
class InstallShop extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->_create_insertMenu();
        $this->_create_shop_news_item();        // 文章内容
        $this->_create_shop_config_poster();    // 用户推广海报
        $this->_create_shop_config_notify();    // 系统通知
        $this->_create_shop_goods();            // 商城商品
        $this->_create_shop_goods_cate();       // 商品分类
        $this->_create_shop_goods_item();       // 商城商品规格
        $this->_create_shop_goods_mark();       // 商城商品标签
        $this->_create_shop_goods_stock();      // 商城商品库存
        $this->_create_shop_order();            // 商城订单
        $this->_create_shop_order_cart();       // 商城订单购物车
        $this->_create_shop_order_item();       // 商城订单商品
        $this->_create_shop_order_refund();     // 商城订单退款
        $this->_create_shop_order_send();       // 商城订单配送
        $this->_create_shop_action_collect();   // 商城-用户-收藏
        $this->_create_shop_action_history();   // 商城-用户-足迹
        $this->_create_shop_action_search();    // 商城-用户-搜索
        $this->_create_shop_express_company();  // 快递公司
        $this->_create_shop_express_template(); // 快递模板
    }

    /**
     * 创建菜单
     * @return void
     */
    protected function _create_insertMenu()
    {
        PhinxExtend::write2menu([
            [
                'name' => '商城管理',
                'subs' => Service::menu(),
            ],
        ], ['node' => 'plugin-shop/base.config/index']);
    }

    /**
     * 商城-配置-海报
     * @class ShopConfigPoster
     * @table shop_config_poster
     * @return void
     */
    private function _create_shop_config_poster()
    {

        // 当前数据表
        $table = 'shop_config_poster';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-配置-海报',
        ])
            ->addColumn('code', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => '推广编号'])
            ->addColumn('name', 'string', ['limit' => 180, 'default' => '', 'null' => true, 'comment' => '推广标题'])
            ->addColumn('levels', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '用户等级'])
            ->addColumn('devices','string',['limit' => 500, 'default' => '', 'null' => true, 'comment' => '接口通道'])
            ->addColumn('image', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '推广图片'])
            ->addColumn('content', 'text', ['default' => NULL, 'null' => true, 'comment' => '二维位置'])
            ->addColumn('remark', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '推广描述'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '激活状态(0无效,1有效)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(1已删,0未删)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('code', ['name' => 'idx_shop_config_poster_code'])
            ->addIndex('sort', ['name' => 'idx_shop_config_poster_sort'])
            ->addIndex('name', ['name' => 'idx_shop_config_poster_name'])
            ->addIndex('status', ['name' => 'idx_shop_config_poster_status'])
            ->addIndex('deleted', ['name' => 'idx_shop_config_poster_deleted'])
            ->addIndex('create_time', ['name' => 'idx_shop_config_poster_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 创建数据对象
     * @class ShopNewsItem
     * @table shop_news_item
     * @return void
     */
    private function _create_shop_news_item()
    {

        // 当前数据表
        $table = 'shop_news_item';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '数据-文章-内容',
        ])
            ->addColumn('code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '文章编号'])
            ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '文章标题'])
            ->addColumn('mark', 'string', ['limit' => 200, 'default' => '', 'null' => true, 'comment' => '文章标签'])
            ->addColumn('cover', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '文章封面'])
            ->addColumn('remark', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '备注说明'])
            ->addColumn('content', 'text', ['default' => null, 'null' => true, 'comment' => '文章内容'])
            ->addColumn('num_like', 'integer', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '文章点赞数'])
            ->addColumn('num_read', 'integer', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '文章阅读数'])
            ->addColumn('num_collect', 'integer', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '文章收藏数'])
            ->addColumn('num_comment', 'integer', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '文章评论数'])
            ->addColumn('sort', 'integer', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '文章状态(1使用,0禁用)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删,1已删)'])
            ->addColumn('create_time', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间'])
            ->addIndex('code', ['name' => 'idx_shop_news_item_code'])
            ->addIndex('status', ['name' => 'idx_shop_news_item_status'])
            ->addIndex('deleted', ['name' => 'idx_shop_news_item_deleted'])
            ->save();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 20, 'identity' => true]);
    }

    /**
     * 创建数据对象
     * @class ShopConfigNotify
     * @table shop_config_notify
     * @return void
     */
    private function _create_shop_config_notify()
    {

        // 当前数据表
        $table = 'shop_config_notify';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '数据-基础-通知',
        ])
            ->addColumn('type', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => '消息类型'])
            ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '消息名称'])
            ->addColumn('content', 'text', ['default' => null, 'null' => true, 'comment' => '消息内容'])
            ->addColumn('num_read', 'integer', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '阅读次数'])
            ->addColumn('sort', 'integer', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '消息状态(1使用,0禁用)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态'])
            ->addColumn('create_time', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true, 'comment' => '创建时间'])
            ->addIndex('type', ['name' => 'idx_shop_config_notify_type'])
            ->addIndex('status', ['name' => 'idx_shop_config_notify_status'])
            ->addIndex('deleted', ['name' => 'idx_shop_config_notify_deleted'])
            ->save();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 20, 'identity' => true]);
    }
    
    /**
     * 商品分类
     * @class ShopGoodsCate
     * @table shop_goods_cate
     * @return void
     */
    private function _create_shop_goods_cate()
    {
        // 当前数据表
        $table = 'shop_goods_cate';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-商品-分类',
        ])
            ->addColumn('pid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '上级分类'])
            ->addColumn('name', 'string', ['limit' => 180, 'default' => '', 'null' => true, 'comment' => '分类名称'])
            ->addColumn('cover', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '分类图标'])
            ->addColumn('remark', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '分类描述'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '使用状态'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('pid', ['name' => 'idx_shop_goods_cate_pid'])
            ->addIndex('sort', ['name' => 'idx_shop_goods_cate_sort'])
            ->addIndex('status', ['name' => 'idx_shop_goods_cate_status'])
            ->addIndex('deleted', ['name' => 'idx_shop_goods_cate_deleted'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 商城商品
     * @class ShopGoods
     * @table shop_goods
     * @return void
     */
    private function _create_shop_goods()
    {

        // 当前数据表
        $table = 'shop_goods';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-商品-内容',
        ])
            ->addColumn('code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '商品编号'])
            ->addColumn('name', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '商品名称'])
            ->addColumn('marks', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '商品标签'])
            ->addColumn('cates', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '分类编号'])
            ->addColumn('cover', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '商品封面'])
            ->addColumn('slider', 'text', ['default' => NULL, 'null' => true, 'comment' => '轮播图片'])
            ->addColumn('specs', 'text', ['default' => NULL, 'null' => true, 'comment' => '商品规格(JSON)'])
            ->addColumn('content', 'text', ['default' => NULL, 'null' => true, 'comment' => '商品详情'])
            ->addColumn('remark', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '商品描述'])
            ->addColumn('stock_total', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '商品库存统计'])
            ->addColumn('stock_sales', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '商品销售统计'])
            ->addColumn('stock_virtual', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '商品虚拟销量'])
            ->addColumn('price_selling', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '最低销售价格'])
            ->addColumn('price_market', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '最低市场价格'])
            ->addColumn('allow_integral', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '最大积分兑换'])
            ->addColumn('allow_balance', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '最大余额支付'])
            ->addColumn('rebate_type', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '参与返佣(0无需返佣,1需要返佣)'])
            ->addColumn('delivery_code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '物流运费模板'])
            ->addColumn('limit_lowvip', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '限制购买等级(0不限制,其他限制)'])
            ->addColumn('limit_maxnum', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '最大购买数量(0不限制,其他限制)'])
            ->addColumn('level_upgrade', 'biginteger', ['limit' => 20, 'default' => -1, 'null' => true, 'comment' => '购买升级等级(-1非入会,0不升级,其他升级)'])
            ->addColumn('discount_id', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '折扣方案编号(0无折扣,其他折扣)'])
            ->addColumn('num_read', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '访问阅读统计'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '列表排序权重'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '商品状态(1使用,0禁用)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删,1已删)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('code', ['name' => 'idx_plugin_wemall_goods_code'])
            ->addIndex('rebate_type', ['name' => 'idx_shop_goods_rebate_type'])
            ->addIndex('discount_id', ['name' => 'idx_shop_goods_discount_id'])
            ->addIndex('level_upgrade', ['name' => 'idx_shop_goods_level_upgrade'])
            ->addIndex('sort', ['name' => 'idx_shop_goods_sort'])
            ->addIndex('status', ['name' => 'idx_shop_goods_status'])
            ->addIndex('deleted', ['name' => 'idx_shop_goods_deleted'])
            ->addIndex('create_time', ['name' => 'idx_shop_goods_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 商城商品规格
     * @class ShopGoodsItem
     * @table shop_goods_item
     * @return void
     */
    private function _create_shop_goods_item()
    {

        // 当前数据表
        $table = 'shop_goods_item';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-商品-规格',
        ])
            ->addColumn('gsku', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '商品SKU'])
            ->addColumn('ghash', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '商品哈希'])
            ->addColumn('gcode', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '商品编号'])
            ->addColumn('gspec', 'string', ['limit' => 180, 'default' => '', 'null' => true, 'comment' => '商品规格'])
            ->addColumn('gunit', 'string', ['limit' => 10, 'default' => '件', 'null' => true, 'comment' => '商品单位'])
            ->addColumn('gimage', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '商品图片'])
            ->addColumn('stock_sales', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '销售数量'])
            ->addColumn('stock_total', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '商品库存'])
            ->addColumn('price_cost', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '进货成本'])
            ->addColumn('price_selling', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '销售价格'])
            ->addColumn('price_market', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '市场价格'])
            ->addColumn('allow_integral', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '兑换积分'])
            ->addColumn('allow_balance', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '余额支付'])
            ->addColumn('reward_balance', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '奖励余额'])
            ->addColumn('reward_integral', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '奖励积分'])
            ->addColumn('number_virtual', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '虚拟销量'])
            ->addColumn('number_express', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '计件系数'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '商品状态'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('status', ['name' => 'idx_shop_goods_item_status'])
            ->addIndex('gcode', ['name' => 'idx_shop_goods_item_gcode'])
            ->addIndex('gspec', ['name' => 'idx_shop_goods_item_gspec'])
            ->addIndex('ghash', ['name' => 'idx_shop_goods_item_ghash'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 商城商品标签
     * @class ShopGoodsMark
     * @table shop_goods_mark
     * @return void
     */
    private function _create_shop_goods_mark()
    {

        // 当前数据表
        $table = 'shop_goods_mark';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-商品-标签',
        ])
            ->addColumn('name', 'string', ['limit' => 180, 'default' => '', 'null' => true, 'comment' => '标签名称'])
            ->addColumn('remark', 'string', ['limit' => 200, 'default' => '', 'null' => true, 'comment' => '标签描述'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '标签状态(1使用,0禁用)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('sort', ['name' => 'idx_shop_goods_mark_sort'])
            ->addIndex('status', ['name' => 'idx_shop_goods_mark_status'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 商城商品库存
     * @class ShopGoodsStock
     * @table shop_goods_stock
     * @return void
     */
    private function _create_shop_goods_stock()
    {

        // 当前数据表
        $table = 'shop_goods_stock';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-商品-库存',
        ])
            ->addColumn('batch_no', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '操作批量'])
            ->addColumn('ghash', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '商品哈希'])
            ->addColumn('gcode', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '商品编号'])
            ->addColumn('gspec', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '商品规格'])
            ->addColumn('gstock', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '入库数量'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '数据状态(1使用,0禁用)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删,1已删)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('ghash', ['name' => 'idx_shop_goods_stock_ghash'])
            ->addIndex('status', ['name' => 'idx_shop_goods_stock_status'])
            ->addIndex('deleted', ['name' => 'idx_shop_goods_stock_deleted'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 商城订单
     * @class ShopOrder
     * @table shop_order
     * @return void
     */
    private function _create_shop_order()
    {

        // 当前数据表
        $table = 'shop_order';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-订单-内容',
        ])
            ->addColumn('unid','biginteger',['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '用户编号'])
            ->addColumn('puid1','biginteger',['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '上1级代理'])
            ->addColumn('puid2','biginteger',['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '上2级代理'])
            ->addColumn('puid3','biginteger',['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '上3级代理'])
            ->addColumn('order_no','string',['limit' => 20, 'default' => '', 'null' => true, 'comment' => '订单单号'])
            ->addColumn('order_ps','string',['limit' => 500, 'default' => '', 'null' => true, 'comment' => '订单备注'])
            ->addColumn('amount_cost','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '商品成本'])
            ->addColumn('amount_real','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '实际金额'])
            ->addColumn('amount_total','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '订单金额'])
            ->addColumn('amount_goods','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '商品金额'])
            ->addColumn('amount_profit','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '销售利润'])
            ->addColumn('amount_reduct','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '随机减免'])
            ->addColumn('amount_balance','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '余额支付'])
            ->addColumn('amount_integral','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '积分抵扣'])
            ->addColumn('amount_payment','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '金额支付'])
            ->addColumn('amount_express','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '快递费用'])
            ->addColumn('amount_discount','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '折扣后金额'])
            ->addColumn('allow_balance','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '最大余额支付'])
            ->addColumn('allow_integral','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '最大积分抵扣'])
            ->addColumn('ratio_integral','decimal',['precision' => 20, 'scale' => 6, 'default' => '0.000000', 'null' => true, 'comment' => '积分兑换比例'])
            ->addColumn('number_goods','biginteger',['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '商品数量'])
            ->addColumn('number_express','biginteger',['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '快递计数'])
            ->addColumn('rebate_amount','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '返利金额'])
            ->addColumn('reward_balance','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '奖励余额'])
            ->addColumn('reward_integral','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '奖励积分'])
            ->addColumn('payment_time','datetime',['default' => NULL, 'null' => true, 'comment' => '支付时间'])
            ->addColumn('payment_status','integer',['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '支付状态(0未支付,1有支付)'])
            ->addColumn('payment_amount','decimal',['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '实际支付'])
            ->addColumn('delivery_type','integer',['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '物流类型(0无配送,1需配送)'])
            ->addColumn('cancel_time','string',['limit' => 20, 'default' => '', 'null' => true, 'comment' => '取消时间'])
            ->addColumn('cancel_status','integer',['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '取消状态'])
            ->addColumn('cancel_remark','string',['limit' => 200, 'default' => '', 'null' => true, 'comment' => '取消描述'])
            ->addColumn('deleted_time','string',['limit' => 20, 'default' => '', 'null' => true, 'comment' => '删除时间'])
            ->addColumn('deleted_status','integer',['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删,1已删)'])
            ->addColumn('deleted_remark','string',['limit' => 255, 'default' => '', 'null' => true, 'comment' => '删除描述'])
            ->addColumn('refund_code','string',['limit' => 20, 'default' => NULL, 'null' => true, 'comment' => '售后单号'])
            ->addColumn('refund_status','integer',['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '售后状态(0未售后,1预订单,2待审核,3待退货,4已退货,5待退款,6已退款,7已完成)'])
            ->addColumn('status','integer',['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '流程状态(0已取消,1预订单,2待支付,3待审核,4待发货,5已发货,6已收货,7已评论)'])
            ->addColumn('create_time','datetime',['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time','datetime',['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('unid', ['name' => 'i4914b9e88_unid'])
            ->addIndex('puid1', ['name' => 'i4914b9e88_puid1'])
            ->addIndex('puid2', ['name' => 'i4914b9e88_puid2'])
            ->addIndex('puid3', ['name' => 'i4914b9e88_puid3'])
            ->addIndex('status', ['name' => 'i4914b9e88_status'])
            ->addIndex('order_no', ['name' => 'i4914b9e88_order_no'])
            ->addIndex('create_time', ['name' => 'i4914b9e88_create_time'])
            ->addIndex('refund_code', ['name' => 'i4914b9e88_refund_code'])
            ->addIndex('delivery_type', ['name' => 'i4914b9e88_delivery_type'])
            ->addIndex('cancel_status', ['name' => 'i4914b9e88_cancel_status'])
            ->addIndex('refund_status', ['name' => 'i4914b9e88_refund_status'])
            ->addIndex('deleted_status', ['name' => 'i4914b9e88_deleted_status'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 商城订单购物车
     * @class ShopOrderCart
     * @table shop_order_cart
     * @return void
     */
    private function _create_shop_order_cart()
    {

        // 当前数据表
        $table = 'shop_order_cart';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-订单-购物车',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '用户编号'])
            ->addColumn('ghash', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '规格哈希'])
            ->addColumn('gcode', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '商品编号'])
            ->addColumn('gspec', 'string', ['limit' => 180, 'default' => '', 'null' => true, 'comment' => '商品规格'])
            ->addColumn('number', 'biginteger', ['limit' => 20, 'default' => 1, 'null' => true, 'comment' => '商品数量'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('unid', ['name' => 'idx_shop_order_cart_unid'])
            ->addIndex('gcode', ['name' => 'idx_shop_order_cart_gcode'])
            ->addIndex('gspec', ['name' => 'idx_shop_order_cart_gspec'])
            ->addIndex('ghash', ['name' => 'idx_shop_order_cart_ghash'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 商城订单商品
     * @class ShopOrderItem
     * @table shop_order_item
     * @return void
     */
    private function _create_shop_order_item()
    {

        // 当前数据表
        $table = 'shop_order_item';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-订单-商品',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '用户编号'])
            ->addColumn('gsku', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '商品SKU'])
            ->addColumn('ghash', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '商品哈希'])
            ->addColumn('gcode', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '商品编号'])
            ->addColumn('gspec', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '商品规格'])
            ->addColumn('gunit', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '商品单凭'])
            ->addColumn('gname', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '商品名称'])
            ->addColumn('gcover', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '商品封面'])
            ->addColumn('order_no', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '订单单号'])
            ->addColumn('stock_sales', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '包含商品数量'])
            ->addColumn('amount_cost', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '商品成本单价'])
            ->addColumn('price_market', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '商品市场单价'])
            ->addColumn('price_selling', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '商品销售单价'])
            ->addColumn('total_price_cost', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '商品成本总价'])
            ->addColumn('total_price_market', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '商品市场总价'])
            ->addColumn('total_price_selling', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '商品销售总价'])
            ->addColumn('total_allow_balance', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '最大余额支付'])
            ->addColumn('total_allow_integral', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '最大兑换总分'])
            ->addColumn('total_reward_balance', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '商品奖励余额'])
            ->addColumn('total_reward_integral', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '商品奖励积分'])
            ->addColumn('level_code', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '用户等级序号'])
            ->addColumn('level_name', 'string', ['limit' => 30, 'default' => '', 'null' => true, 'comment' => '用户等级名称'])
            ->addColumn('level_upgrade', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '购买升级等级(-1非入会,0不升级,其他升级)'])
            ->addColumn('rebate_type', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '参与返佣状态(0不返,1返佣)'])
            ->addColumn('rebate_amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '参与返佣金额'])
            ->addColumn('delivery_code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '快递邮费模板'])
            ->addColumn('delivery_count', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '快递计费基数'])
            ->addColumn('discount_id', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '优惠方案编号'])
            ->addColumn('discount_rate', 'decimal', ['precision' => 20, 'scale' => 6, 'default' => '100.000000', 'null' => true, 'comment' => '销售价格折扣'])
            ->addColumn('discount_amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '商品优惠金额'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '商品状态(1使用,0禁用)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删,1已删)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('unid', ['name' => 'idx_shop_order_item_unid'])
            ->addIndex('gsku', ['name' => 'idx_shop_order_item_gsku'])
            ->addIndex('gcode', ['name' => 'idx_shop_order_item_gcode'])
            ->addIndex('gspec', ['name' => 'idx_shop_order_item_gspec'])
            ->addIndex('ghash', ['name' => 'idx_shop_order_item_ghash'])
            ->addIndex('status', ['name' => 'idx_shop_order_item_status'])
            ->addIndex('deleted', ['name' => 'idx_shop_order_item_deleted'])
            ->addIndex('order_no', ['name' => 'idx_shop_order_item_order_no'])
            ->addIndex('rebate_type', ['name' => 'idx_shop_order_item_rebate_type'])
            ->addIndex('discount_id', ['name' => 'idx_shop_order_item_discount_id'])
            ->addIndex('delivery_code', ['name' => 'idx_shop_order_item_delivery_code'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 创建数据对象
     * @class ShopOrderRefund
     * @table shop_order_refund
     * @return void
     */
    private function _create_shop_order_refund()
    {

        // 当前数据表
        $table = 'shop_order_refund';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-售后-订单',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '用户编号'])
            ->addColumn('type', 'biginteger', ['limit' => 20, 'default' => 1, 'null' => true, 'comment' => '申请类型(1退货退款,2仅退款)'])
            ->addColumn('code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '售后单号'])
            ->addColumn('order_no', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '订单单号'])
            ->addColumn('reason', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '退款原因'])
            ->addColumn('number', 'biginteger', ['limit' => 20, 'default' => 1, 'null' => true, 'comment' => '退货数量'])
            ->addColumn('amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '申请金额'])
            ->addColumn('payment_amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '退款支付'])
            ->addColumn('balance_amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '退款余额'])
            ->addColumn('integral_amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '退款积分'])
            ->addColumn('payment_code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '退款单号'])
            ->addColumn('balance_code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '退回单号'])
            ->addColumn('integral_code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '退回单号'])
            ->addColumn('phone', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '联系电话'])
            ->addColumn('images', 'text', ['default' => NULL, 'null' => true, 'comment' => '申请图片'])
            ->addColumn('content', 'text', ['default' => NULL, 'null' => true, 'comment' => '申请说明'])
            ->addColumn('remark', 'string', ['limit' => 180, 'default' => NULL, 'null' => true, 'comment' => '操作描述'])
            ->addColumn('express_no', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '快递单号'])
            ->addColumn('express_code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '快递公司'])
            ->addColumn('express_name', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => '快递名称'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '流程状态(0已取消,1预订单,2待审核,3待退货,4已退货,5待退款,6已退款,7已完成)'])
            ->addColumn('status_at', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '状态变更时间'])
            ->addColumn('status_ds', 'string', ['limit' => 200, 'default' => '', 'null' => true, 'comment' => '状态变更描述'])
            ->addColumn('admin_by', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '后台用户'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('unid', ['name' => 'i3c826a8cd_unid'])
            ->addIndex('type', ['name' => 'i3c826a8cd_type'])
            ->addIndex('code', ['name' => 'i3c826a8cd_code'])
            ->addIndex('status', ['name' => 'i3c826a8cd_status'])
            ->addIndex('order_no', ['name' => 'i3c826a8cd_order_no'])
            ->addIndex('create_time', ['name' => 'i3c826a8cd_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 商城订单配送
     * @class ShopOrderSend
     * @table shop_order_send
     * @return void
     */
    private function _create_shop_order_send()
    {

        // 当前数据表
        $table = 'shop_order_send';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-订单-配送',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '商城用户编号'])
            ->addColumn('order_no', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '商城订单单号'])
            ->addColumn('address_id', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '配送地址编号'])
            ->addColumn('user_idcode', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '收货人证件号码'])
            ->addColumn('user_idimg1', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '收货人证件正面'])
            ->addColumn('user_idimg2', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '收货人证件反面'])
            ->addColumn('user_name', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => '收货人联系名称'])
            ->addColumn('user_phone', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '收货人联系手机'])
            ->addColumn('region_prov', 'string', ['limit' => 30, 'default' => '', 'null' => true, 'comment' => '配送地址的省份'])
            ->addColumn('region_city', 'string', ['limit' => 30, 'default' => '', 'null' => true, 'comment' => '配送地址的城市'])
            ->addColumn('region_area', 'string', ['limit' => 30, 'default' => '', 'null' => true, 'comment' => '配送地址的区域'])
            ->addColumn('region_addr', 'string', ['limit' => 255, 'default' => '', 'null' => true, 'comment' => '配送的详细地址'])
            ->addColumn('delivery_code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '配送模板编号'])
            ->addColumn('delivery_count', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '快递计费基数'])
            ->addColumn('delivery_amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '配送计算金额'])
            ->addColumn('delivery_remark', 'string', ['limit' => 255, 'default' => '', 'null' => true, 'comment' => '配送计算描述'])
            ->addColumn('express_time', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '快递发送时间'])
            ->addColumn('express_code', 'string', ['limit' => 80, 'default' => '', 'null' => true, 'comment' => '快递运送单号'])
            ->addColumn('express_remark', 'string', ['limit' => 255, 'default' => '', 'null' => true, 'comment' => '快递发送备注'])
            ->addColumn('company_code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '快递公司编码'])
            ->addColumn('company_name', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '快递公司名称'])
            ->addColumn('extra', 'text', ['default' => NULL, 'null' => true, 'comment' => '原始数据'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '发货状态(1待发货,2已发货,3已收货)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删,1已删)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('unid', ['name' => 'idx_shop_order_send_unid'])
            ->addIndex('status', ['name' => 'idx_shop_order_send_status'])
            ->addIndex('deleted', ['name' => 'idx_shop_order_send_deleted'])
            ->addIndex('order_no', ['name' => 'idx_shop_order_send_order_no'])
            ->addIndex('create_time', ['name' => 'idx_shop_order_send_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 商城-用户-收藏
     * @class ShopActionCollect
     * @table shop_action_collect
     * @return void
     */
    private function _create_shop_action_collect()
    {

        // 当前数据表
        $table = 'shop_action_collect';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-用户-收藏',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '用户编号'])
            ->addColumn('gcode', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '商品编号'])
            ->addColumn('times', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '记录次数'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('unid', ['name' => 'idx_shop_action_collect_unid'])
            ->addIndex('sort', ['name' => 'idx_shop_action_collect_sort'])
            ->addIndex('gcode', ['name' => 'idx_shop_action_collect_gcode'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 商城-用户-足迹
     * @class ShopActionHistory
     * @table shop_action_history
     * @return void
     */
    private function _create_shop_action_history()
    {

        // 当前数据表
        $table = 'shop_action_history';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-用户-足迹',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '用户编号'])
            ->addColumn('gcode', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '商品编号'])
            ->addColumn('times', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '记录次数'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('sort', ['name' => 'idx_shop_action_history_sort'])
            ->addIndex('unid', ['name' => 'idx_shop_action_history_unid'])
            ->addIndex('gcode', ['name' => 'idx_shop_action_history_gcode'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 商城-用户-搜索
     * @class ShopActionSearch
     * @table shop_action_search
     * @return void
     */
    private function _create_shop_action_search()
    {

        // 当前数据表
        $table = 'shop_action_search';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '商城-用户-搜索',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '用户编号'])
            ->addColumn('keys', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '关键词'])
            ->addColumn('times', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '搜索次数'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('keys', ['name' => 'idx_shop_action_search_keys'])
            ->addIndex('unid', ['name' => 'idx_shop_action_search_unid'])
            ->addIndex('sort', ['name' => 'idx_shop_action_search_sort'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 快递公司
     * @class ShopExpressCompany
     * @table shop_express_company
     * @return void
     */
    private function _create_shop_express_company()
    {

        // 当前数据表
        $table = 'shop_express_company';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '数据-快递-公司',
        ])
            ->addColumn('code', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => '公司代码'])
            ->addColumn('name', 'string', ['limit' => 180, 'default' => '', 'null' => true, 'comment' => '公司名称'])
            ->addColumn('remark', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '公司描述'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '激活状态(0无效,1有效)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(1已删,0未删)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('code', ['name' => 'idx_shop_express_company_code'])
            ->addIndex('sort', ['name' => 'idx_shop_express_company_sort'])
            ->addIndex('name', ['name' => 'idx_shop_express_company_name'])
            ->addIndex('status', ['name' => 'idx_shop_express_company_status'])
            ->addIndex('deleted', ['name' => 'idx_shop_express_company_deleted'])
            ->addIndex('create_time', ['name' => 'idx_shop_express_company_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 快递模板
     * @class ShopExpressTemplate
     * @table shop_express_template
     * @return void
     */
    private function _create_shop_express_template()
    {

        // 当前数据表
        $table = 'shop_express_template';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '数据-快递-模板',
        ])
            ->addColumn('code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '模板编号'])
            ->addColumn('name', 'string', ['limit' => 180, 'default' => '', 'null' => true, 'comment' => '模板名称'])
            ->addColumn('normal', 'text', ['default' => NULL, 'null' => true, 'comment' => '默认规则'])
            ->addColumn('content', 'text', ['default' => NULL, 'null' => true, 'comment' => '模板规则'])
            ->addColumn('company', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '快递公司'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '激活状态(0无效,1有效)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(1已删,0未删)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('code', ['name' => 'idx_shop_express_template_code'])
            ->addIndex('name', ['name' => 'idx_shop_express_template_name'])
            ->addIndex('sort', ['name' => 'idx_shop_express_template_sort'])
            ->addIndex('status', ['name' => 'idx_shop_express_template_status'])
            ->addIndex('deleted', ['name' => 'idx_shop_express_template_deleted'])
            ->addIndex('create_time', ['name' => 'idx_shop_express_template_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

}
