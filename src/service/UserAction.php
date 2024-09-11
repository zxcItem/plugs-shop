<?php

declare (strict_types=1);

namespace plugin\shop\service;

use plugin\account\model\PluginAccountUser;
use plugin\shop\model\PluginShopOrderCart;
use plugin\shop\model\PluginShopOrderItem;
use plugin\shop\model\PluginShopUserActionCollect;
use plugin\shop\model\PluginShopUserActionComment;
use plugin\shop\model\PluginShopUserActionHistory;
use think\admin\Storage;

/**
 * 用户行为数据服务
 * @class UserAction
 * @package plugin\shop\service
 */
abstract class UserAction
{
    /**
     * 设置行为数据
     * @param integer $unid 用户编号
     * @param string $gcode 商品编号
     * @param string $type 行为类型
     * @return array
     * @throws \think\db\exception\DbException
     */
    public static function set(int $unid, string $gcode, string $type): array
    {
        $data = ['unid' => $unid, 'gcode' => $gcode];
        if ($type === 'collect') {
            $model = PluginShopUserActionCollect::mk()->where($data)->findOrEmpty();
        } else {
            $model = PluginShopUserActionHistory::mk()->where($data)->findOrEmpty();
        }
        $data['sort'] = time();
        $data['times'] = $model->isExists() ? $model->getAttr('times') + 1 : 1;
        $model->save($data) && UserAction::recount($unid);
        return $model->toArray();
    }

    /**
     * 删除行为数据
     * @param integer $unid 用户编号
     * @param string $gcode 商品编号
     * @param string $type 行为类型
     * @return array
     * @throws \think\db\exception\DbException
     */
    public static function del(int $unid, string $gcode, string $type): array
    {
        $data = [['unid', '=', $unid], ['gcode', 'in', str2arr($gcode)]];
        if ($type === 'collect') {
            PluginShopUserActionCollect::mk()->where($data)->delete();
        } else {
            PluginShopUserActionHistory::mk()->where($data)->delete();
        }
        self::recount($unid);
        return $data;
    }

    /**
     * 清空行为数据
     * @param integer $unid 用户编号
     * @param string $type 行为类型
     * @return array
     * @throws \think\db\exception\DbException
     */
    public static function clear(int $unid, string $type): array
    {
        $data = [['unid', '=', $unid]];
        if ($type === 'collect') {
            PluginShopUserActionCollect::mk()->where($data)->delete();
        } else {
            PluginShopUserActionHistory::mk()->where($data)->delete();
        }
        self::recount($unid);
        return $data;
    }

    /**
     * 刷新用户行为统计
     * @param integer $unid 用户编号
     * @param array|null $data 非数组时更新数据
     * @return array [collect, history, mycarts]
     * @throws \think\db\exception\DbException
     */
    public static function recount(int $unid, ?array &$data = null): array
    {
        $isUpdate = !is_array($data);
        if ($isUpdate) $data = [];
        // 更新收藏及足迹数量和购物车
        $map = ['unid' => $unid];
        $data['mycarts_total'] = PluginShopOrderCart::mk()->where($map)->sum('number');
        $data['collect_total'] = PluginShopUserActionCollect::mk()->where($map)->count();
        $data['history_total'] = PluginShopUserActionHistory::mk()->where($map)->count();
        if ($isUpdate && ($user = PluginAccountUser::mk()->findOrEmpty($unid))->isExists()) {
            $user->save(['extra' => array_merge($user->getAttr('extra'), $data)]);
        }
        return [$data['collect_total'], $data['history_total'], $data['mycarts_total']];
    }

    /**
     * 写入商品评论
     * @param PluginShopOrderItem $item
     * @param string|float $rate
     * @param string $content
     * @param string $images
     * @return bool
     * @throws \think\admin\Exception
     */
    public static function comment(PluginShopOrderItem $item, $rate, string $content, string $images): bool
    {
        // 图片上传转存
        if (!empty($images)) {
            $images = explode('|', $images);
            foreach ($images as &$image) {
                $image = Storage::saveImage($image, 'comment')['url'];
            }
            $images = join('|', $images);
        }
        // 根据单号+商品规格查询评论
        $code = md5("{$item->getAttr('order_no')}#{$item->getAttr('ghash')}");
        return PluginShopUserActionComment::mk()->where(['code' => $code])->findOrEmpty()->save([
            'code'     => $code,
            'unid'     => $item->getAttr('unid'),
            'gcode'    => $item->getAttr('gcode'),
            'ghash'    => $item->getAttr('ghash'),
            'order_no' => $item->getAttr('order_no'),
            'rate'     => $rate,
            'images'   => $images,
            'content'  => $content,
        ]);
    }
}