<?php

namespace plugin\shop\model;

use plugin\account\model\Abs;

/**
 * 快递模板模型
 */
class ShopPostageTemplate extends Abs
{
    /**
     * 获取快递模板数据
     * @param boolean $allow
     * @return array
     */
    public static function items(bool $allow = false): array
    {
        $items = $allow ? [
            'NONE' => ['code' => 'NONE', 'name' => '无需发货', 'normal' => '', 'content' => '[]', 'company' => ['_' => '虚拟产品']],
            'FREE' => ['code' => 'FREE', 'name' => '免费包邮', 'normal' => '', 'content' => '[]', 'company' => ['ALL' => '发货时选快递公司']],
        ] : [];
        $query = self::mk()->where(['status' => 1, 'deleted' => 0])->order('sort desc,id desc');
        foreach ($query->field('code,name,normal,content,company')->cursor() as $tmpl) $items[$tmpl->getAttr('code')] = $tmpl->toArray();
        return $items;
    }

    /**
     * 写入快递公司
     * @param mixed $value
     * @return string
     */
    public function setCompanyAttr($value): string
    {
        return is_array($value) ? arr2str($value) : $value;
    }

    /**
     * 快递公司处理
     * @param mixed $value
     * @return array
     */
    public function getCompanyAttr($value): array
    {
        [$express, $skey] = [[], 'plugin.shop.companys'];
        $companys = sysvar($skey) ?: sysvar($skey, ShopPostageCompany::items());
        foreach (is_string($value) ? str2arr($value) : (array)$value as $key) {
            if (isset($companys[$key])) $express[$key] = $companys[$key];
        }
        return $express;
    }

    /**
     * 格式化规则数据
     * @param mixed $value
     * @return array
     */
    public function getNormalAttr($value): array
    {
        return $this->getExtraAttr($value);
    }

    public function setNormalAttr($value): string
    {
        return $this->setExtraAttr($value);
    }

    /**
     * 格式化规则数据
     * @param mixed $value
     * @return array
     */
    public function getContentAttr($value): array
    {
        return $this->getExtraAttr($value);
    }

    public function setContentAttr($value): string
    {
        return $this->setExtraAttr($value);
    }
}