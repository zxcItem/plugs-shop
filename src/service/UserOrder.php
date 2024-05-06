<?php


declare (strict_types=1);

namespace plugin\shop\service;

use plugin\payment\model\PaymentAddress;
use plugin\payment\model\PaymentRecord;
use plugin\payment\service\BalanceService;
use plugin\payment\service\IntegralService;
use plugin\payment\service\Payment;

use plugin\shop\model\ShopOrder;
use plugin\shop\model\ShopOrderItem;
use plugin\shop\model\ShopOrderSend;
use think\admin\Exception;
use think\admin\Library;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 商城订单数据服务
 * @class OrderService
 * @package plugin\shop\service
 */
class UserOrder
{
    /**
     * 获取随减金额
     * @return float
     * @throws Exception
     */
    public static function reduct(): float
    {
        $config = sysdata('plugin.shop.config');
        if (empty($config['enable_reduct'])) return 0.00;
        $min = intval(($config['reduct_min'] ?? 0) * 100);
        $max = intval(($config['reduct_max'] ?? 0) * 100);
        return mt_rand($min, $max) / 100;
    }

    /**
     * 同步订单关联商品的库存
     * @param string $orderNo 订单编号
     * @return boolean
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function stock(string $orderNo): bool
    {
        $map = ['order_no' => $orderNo];
        $codes = ShopOrderItem::mk()->where($map)->column('gcode');
        foreach (array_unique($codes) as $code) GoodsService::stock($code);
        return true;
    }

    /**
     * 获取订单模型
     * @param ShopOrder|string $order
     * @param ?integer $unid 动态绑定变量
     * @param ?string $orderNo 动态绑定变量
     * @return ShopOrder
     * @throws Exception
     */
    public static function widthOrder($order, ?int &$unid = 0, ?string &$orderNo = ''): ShopOrder
    {
        if (is_string($order)) {
            $order = ShopOrder::mk()->where(['order_no' => $order])->findOrEmpty();
        }
        if ($order instanceof ShopOrder) {
            [$unid, $orderNo] = [intval($order->getAttr('unid')), $order->getAttr('order_no')];
            return $order;
        }
        throw new Exception("无效订单对象！");
    }


    /**
     * 更新订单收货地址
     * @param ShopOrder $order
     * @param PaymentAddress $address
     * @return boolean
     * @throws Exception
     */
    public static function perfect(ShopOrder $order, PaymentAddress $address): bool
    {
        $unid = $order->getAttr('unid');
        $orderNo = $order->getAttr('order_no');
        // 根据地址计算运费
        $map1 = ['order_no' => $orderNo, 'status' => 1, 'deleted' => 0];
        $map2 = ['order_no' => $order->getAttr('order_no'), 'unid' => $unid];
        [$amount, $tCount, $tCode, $remark] = ExpressService::amount(
            ShopOrderItem::mk()->where($map1)->column('delivery_code'),
            $address->getAttr('region_prov'), $address->getAttr('region_city'),
            (int)ShopOrderItem::mk()->where($map2)->sum('delivery_count')
        );
        // 创建订单发货信息
        $extra = [
            'delivery_code' => $tCode, 'delivery_count' => $tCount, 'unid' => $unid,
            'delivery_remark' => $remark, 'delivery_amount' => $amount, 'status' => 1,
        ];
        $extra['order_no'] = $orderNo;
        $extra['address_id'] = $address->getAttr('id');
        // 收货人信息
        $extra['user_name'] = $address->getAttr('user_name');
        $extra['user_phone'] = $address->getAttr('user_phone');
        $extra['user_idcode'] = $address->getAttr('idcode');
        $extra['user_idimg1'] = $address->getAttr('idimg1');
        $extra['user_idimg2'] = $address->getAttr('idimg2');
        // 收货地址信息
        $extra['region_prov'] = $address->getAttr('region_prov');
        $extra['region_city'] = $address->getAttr('region_city');
        $extra['region_area'] = $address->getAttr('region_area');
        $extra['region_addr'] = $address->getAttr('region_addr');
        $extra['extra'] = $extra;
        ShopOrderSend::mk()->where(['order_no' => $orderNo])->findOrEmpty()->save($extra);
        // 组装更新订单数据
        $update = ['status' => 2, 'amount_express' => $extra['delivery_amount']];
        // 重新计算订单金额
        $update['amount_real'] = $order->getAttr('amount_discount') + $amount - $order->getAttr('amount_reduct');
        $update['amount_total'] = $order->getAttr('amount_goods') + $amount;
        // 支付金额不能为零
        if ($update['amount_real'] <= 0) $update['amount_real'] = 0.00;
        if ($update['amount_total'] <= 0) $update['amount_total'] = 0.00;
        // 更新用户订单数据
        if ($order->save($update)) {
            // 触发订单确认事件
            Library::$sapp->event->trigger('PluginWemallOrderPerfect', $order);
            // 返回处理成功数据
            return true;
        } else {
            return false;
        }
    }

    /**
     * 更新订单支付状态
     * @param ShopOrder|string $order 订单模型
     * @param PaymentRecord $payment 支付行为记录
     * @return bool|string|void|null
     * @throws Exception
     * @remark 订单状态(0已取消,1预订单,2待支付,3待审核,4待发货,5已发货,6已收货,7已评论)
     */
    public static function change($order, PaymentRecord $payment)
    {
        $order = self::widthOrder($order);
        if ($order->isEmpty()) return null;

        // 同步订单支付统计
        $ptotal = Payment::totalPaymentAmount($payment->getAttr('order_no'));
        $order->appendData([
            'payment_time'    => $payment->getAttr('create_time'),
            'payment_amount'  => $ptotal['amount'] ?? 0,
            'amount_payment'  => $ptotal['payment'] ?? 0,
            'amount_balance'  => $ptotal['balance'] ?? 0,
            'amount_integral' => $ptotal['integral'] ?? 0,
        ], true);

        // 订单已经支付完成
        if ($order->getAttr('payment_amount') >= $order->getAttr('amount_real')) {
            // 已完成支付，更新订单状态
            $status = $order->getAttr('delivery_type') ? 4 : 5;
            $order->save(['status' => $status, 'payment_status' => 1]);
            // 确认完成支付，发放余额积分奖励及升级返佣
            return static::confirm($order);
        }

        // 退款或部分退款，仅更新订单支付统计
        if ($payment->getAttr('refund_status')) {
            return $order->save();
        }

        // 提交支付凭证，只需更新订单状态为【待审核】
        $isVoucher = $payment->getAttr('channel_type') === Payment::VOUCHER;
        if ($isVoucher && $payment->getAttr('audit_status') === 1) return $order->save([
            'status' => 3, 'payment_status' => 1,
        ]);

        // 凭证支付审核被拒绝，订单回滚到未支付状态
        if ($isVoucher && $payment->getAttr('audit_status') === 0) {
            if ($order->getAttr('status') === 3) $order->save(['status' => 2]);
        } else {
            $order->save();
        }
    }

    /**
     * 取消订单撤销奖励
     * @param ShopOrder|string $order
     * @param boolean $setRebate 更新返佣
     * @return string
     */
    public static function cancel($order, bool $setRebate = false): string
    {
        try { /* 创建用户奖励 */
            $order = UserReward::cancel($order, $code);
        } catch (\Exception $exception) {
            trace_file($exception);
        }
        return $code;
    }


    /**
     * 支付成功发放奖励
     * @param ShopOrder|string $order
     * @return string
     */
    public static function confirm($order): string
    {
        try { /* 创建用户奖励 */
            UserReward::create($order, $code);
        } catch (\Exception $exception) {
            trace_file($exception);
        }
        // 返回奖励单号
        return $code;
    }

    /**
     * 订单收货 解锁积分余额
     * @param string $order
     * @return void
     */
    public static function orderConfirm(string $order)
    {
        ShopOrderSend::mk()->where(['order_no'=>$order])->save(['status'=>3]);
        try { /* 解锁订单余额积分奖励 */
            static::confirm($order, true);
        } catch (\Exception $exception) {
            trace_file($exception);
        }
    }

}