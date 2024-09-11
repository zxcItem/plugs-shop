<?php

declare (strict_types=1);

namespace plugin\shop\model;

use plugin\account\model\Abs;
use think\model\relation\HasMany;
use think\model\relation\HasOne;

/**
 * 商城商品数据数据
 * @class PluginShopGoods
 * @package plugin\shop\model
 */
class PluginShopGoods extends Abs
{
    /**
     * 日志名称
     * @var string
     */
    protected $oplogName = '商品';

    /**
     * 日志类型
     * @var string
     */
    protected $oplogType = '通用商城管理';

    /**
     * 关联产品规格
     * @return \think\model\relation\HasMany
     */
    public function items(): HasMany
    {
        return static::mk()
            ->hasMany(PluginShopGoodsItem::class, 'gcode', 'code')
            ->withoutField('id,status,create_time,update_time')
            ->where(['status' => 1]);
    }

    /**
     * 关联商品评论数据
     * @return \think\model\relation\HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(PluginShopUserActionComment::class, 'gcode', 'code')->with('bindUser');
    }

    /**
     * 关联产品列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function lists(): array
    {
        $model = static::mk()->with('items')->withoutField('specs');
        return $model->order('sort desc,id desc')->where(['deleted' => 0])->select()->toArray();
    }

    /**
     * 标签处理
     * @param mixed $value
     * @return array
     */
    public function getMarksAttr($value): array
    {
        $ckey = 'PluginWemallGoodsMarkItems';
        $items = sysvar($ckey) ?: sysvar($ckey, PluginShopGoodsMark::items());
        return str2arr(is_array($value) ? arr2str($value) : $value, ',', $items);
    }

    /**
     * 处理商品分类数据
     * @param mixed $value
     * @return array
     */
    public function getCatesAttr($value): array
    {
        $ckey = 'PluginWemallGoodsCateItem';
        $cates = sysvar($ckey) ?: sysvar($ckey, PluginShopGoodsCate::items(true));
        $cateids = is_string($value) ? str2arr($value) : (array)$value;
        foreach ($cates as $cate) if (in_array($cate['id'], $cateids)) return $cate;
        return [];
    }

    /**
     * 设置轮播图片
     * @param mixed $value
     * @return string
     */
    public function setSliderAttr($value): string
    {
        return is_string($value) ? $value : (is_array($value) ? arr2str($value) : '');
    }

    /**
     * 获取轮播图片
     * @param mixed $value
     * @return array
     */
    public function getSliderAttr($value): array
    {
        return is_string($value) ? str2arr($value, '|') : [];
    }

    /**
     * 设置规格数据
     * @param mixed $value
     * @return string
     */
    public function setSpecsAttr($value): string
    {
        return $this->setExtraAttr($value);
    }

    /**
     * 获取规格数据
     * @param mixed $value
     * @return array
     */
    public function getSpecsAttr($value): array
    {
        return $this->getExtraAttr($value);
    }
}