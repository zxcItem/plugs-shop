<?php


declare (strict_types=1);

namespace plugin\shop\service;

use plugin\account\model\AccountUser;
use plugin\shop\model\ShopActionComment;
use plugin\shop\model\ShopOrderCart;
use plugin\shop\model\ShopActionCollect;
use plugin\shop\model\ShopActionHistory;
use plugin\shop\model\ShopOrderItem;
use think\admin\Storage;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

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
     * @throws DbException
     */
    public static function set(int $unid, string $gcode, string $type): array
    {
        $data = ['unid' => $unid, 'gcode' => $gcode];
        if ($type === 'collect') {
            $model = ShopActionCollect::mk()->where($data)->findOrEmpty();
        } else {
            $model = ShopActionHistory::mk()->where($data)->findOrEmpty();
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
     * @throws DbException
     */
    public static function del(int $unid, string $gcode, string $type): array
    {
        $data = [['unid', '=', $unid], ['gcode', 'in', str2arr($gcode)]];
        if ($type === 'collect') {
            ShopActionCollect::mk()->where($data)->delete();
        } else {
            ShopActionHistory::mk()->where($data)->delete();
        }
        self::recount($unid);
        return $data;
    }

    /**
     * 清空行为数据
     * @param integer $unid 用户编号
     * @param string $type 行为类型
     * @return array
     * @throws DbException
     */
    public static function clear(int $unid, string $type): array
    {
        $data = [['unid', '=', $unid]];
        if ($type === 'collect') {
            ShopActionCollect::mk()->where($data)->delete();
        } else {
            ShopActionHistory::mk()->where($data)->delete();
        }
        self::recount($unid);
        return $data;
    }

    /**
     * 刷新用户行为统计
     * @param integer $unid 用户编号
     * @param array|null $data 非数组时更新数据
     * @return array [collect, history, mycarts]
     * @throws DbException
     */
    public static function recount(int $unid, ?array &$data = null): array
    {
        $isUpdate = !is_array($data);
        if ($isUpdate) $data = [];
        // 更新收藏及足迹数量和购物车
        $map = ['unid' => $unid];
        $data['mycarts_total'] = ShopOrderCart::mk()->where($map)->sum('number');
        $data['collect_total'] = ShopActionCollect::mk()->where($map)->count();
        $data['history_total'] = ShopActionHistory::mk()->where($map)->count();
        if ($isUpdate && ($user = AccountUser::mk()->findOrEmpty($unid))->isExists()) {
            $user->save(['extra' => array_merge($user->getAttr('extra'), $data)]);
        }
        return [$data['collect_total'], $data['history_total'], $data['mycarts_total']];
    }

    /**
     * 写入商品评论
     * @param ShopOrderItem $item
     * @param string|float $rate
     * @param string $content
     * @param string $images
     * @return bool
     * @throws \think\admin\Exception
     */
    public static function comment(ShopOrderItem $item, $rate, string $content, string $images): bool
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
        return ShopActionComment::mk()->where(['code' => $code])->findOrEmpty()->save([
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