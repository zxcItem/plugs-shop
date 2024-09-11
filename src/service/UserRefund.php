<?php


declare (strict_types=1);

namespace plugin\shop\service;

use plugin\shop\model\PluginShopOrderRefund;
use think\admin\Exception;

/**
 * 商城售后管理
 * @class UserRefund
 * @package plugin\shop\service
 */
abstract class UserRefund
{
    // 售后状态(前端使用)
    public const states1 = [
        '已取消', '预订单', '待审核', '待退货', '已退货', '待退款', '已退货', '已完成'
    ];

    // 售后状态(后台使用)
    public const states2 = [
        '未售后', '预订单', '待审核', '待退货', '已退货', '待退款', '已退货', '已完成'
    ];

    // 售后类型
    public const types = [
        1 => '我要退货退款',
        2 => '我要退款 ( 无需退货 )'
    ];

    // 退货原因
    public const reasons = [
        'R1' => '不喜欢、效果不好',
        'R2' => '商品成分描述不符',
        'R3' => '大小尺寸与商品描述不符',
        'R4' => '颜色、款式、包装与描述不符',
        'R5' => '枯萎、死亡',
        'R6' => '收到商品少件(含少配件)',
        'R7' => '商品破损或污渍',
        'R8' => '商家发错货',
        'R9' => '其他原因'
    ];

    /**
     * 动态获取售后模型
     * @param array $map
     * @param callable $fn
     * @return \plugin\shop\model\PluginShopOrderRefund
     * @throws \think\admin\Exception
     */
    public static function withRefund(array $map, callable $fn): PluginShopOrderRefund
    {
        $refund = PluginShopOrderRefund::mk()->where($map)->findOrEmpty();
        if ($refund->isEmpty()) throw new Exception('无效售后单！');
        if (is_callable($fn) && is_array($result = $fn($refund))) {
            if (isset($result['status']) !== $refund->getAttr('status')) {
                if (($order = $refund->orderinfo()->findOrEmpty())->isExists()) {
                    $order->save(['refund_status' => $refund->getAttr('status')]);
                }
            }
            $refund->save($result);
        }
        return $refund;
    }
}