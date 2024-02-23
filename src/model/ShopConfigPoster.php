<?php

namespace plugin\shop\model;

use plugin\account\model\Abs;
use plugin\account\service\Account;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;

/**
 * 推广海报
 */
class ShopConfigPoster extends Abs
{
    /**
     * 指定用户获取配置
     * @param string $device 指定终端类型
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function items($device = ''): array
    {
        // 指定设备终端授权数据筛选
        $query = self::mk()->where(static function (Query $query) use ($device) {
            $query->whereOr([['devices', 'like', "%,{$device},%"], ['devices', 'like', '%,-,%']]);
        });
        return $query->withoutField('sort,status,deleted')->select()->toArray();
    }

    /**
     * 获取授权终端设备
     * @param mixed $value
     * @return array
     */
    public function getDevicesAttr($value): array
    {
        return is_string($value) ? str2arr($value) : [];
    }

    /**
     * 格式化数据写入
     * @param mixed $value
     * @return string
     */
    public function setDevicesAttr($value): string
    {
        return is_array($value) ? arr2str($value) : $value;
    }

    /**
     * 格式化定位数据
     * @param mixed $value
     */
    public function getContentAttr($value): array
    {
        return $this->getExtraAttr($value);
    }

    public function setContentAttr($value): string
    {
        return $this->setExtraAttr($value);
    }

    /**
     * 数据名称转换处理
     * @return array
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        if (isset($data['devices'])) {
            $data['devices_names'] = [];
            $types = array_column(Account::types(), 'name', 'code');
            if (in_array('-', $data['devices'])) $data['devices_names'] = ['全部'];
            else foreach ($data['devices'] as $k) $data['devices_names'][] = $types[$k] ?? $k;
        }
        return $data;
    }
}