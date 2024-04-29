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
     * @param ShopOrder $order 订单模型
     * @param PaymentRecord $payment 支付行为记录
     * @return boolean|void
     * @remark 订单状态(0已取消,1预订单,2待支付,3待审核,4待发货,5已发货,6已收货,7已评论)
     */
    public static function payment(ShopOrder $order, PaymentRecord $payment)
    {
        $orderNo = $payment->getAttr('order_no');
        $paidAmount = Payment::paidAmount($orderNo, true);

        // 提交支付凭证，只需更新订单状态
        $isVoucher = $payment->getAttr('channel_type') === Payment::VOUCHER;
        if ($isVoucher && $payment->getAttr('audit_status') === 1) return $order->save([
            'status' => 3,
            'payment_time' => date('Y-m-d H:i:s'),
            'payment_amount' => $paidAmount,
            'payment_status' => 1,
        ]);

        // 发起订单退款，标记订单已取消
        if (empty($paidAmount) && $payment->getAttr('refund_status')) {
            $order->save([
                'status' => 0,
                'payment_time' => $payment->getAttr('payment_time'),
                'payment_amount' => $paidAmount,
                'payment_status' => 1,
            ]);
            try { /* 取消订单余额积分奖励及反拥 */
                static::cancel($orderNo, true);
            } catch (\Exception $exception) {
                trace_file($exception);
            }
        }

        // 订单已经支付完成
        if ($paidAmount >= $order->getAttr('amount_real')) {
            // 已完成支付
            $order->save([
                'status' => $order->getAttr('delivery_type') ? 4 : 5,
                'payment_time' => $payment->getAttr('payment_time'),
                'payment_amount' => $paidAmount,
                'payment_status' => 1,
            ]);
            try { /* 奖励余额及积分 */
                static::confirm($orderNo);
            } catch (\Exception $exception) {
                trace_file($exception);
            }
        }
        // 凭证支付审核被拒绝
        if ($isVoucher && $payment->getAttr('audit_status') !== 1) {
            $map = ['channel_type' => Payment::VOUCHER, 'audit_status' => 1, 'order_no' => $orderNo];
            if (PaymentRecord::mk()->where($map)->findOrEmpty()->isEmpty()) {
                if ($order->getAttr('status') === 3) $order->save(['status' => 2]);
            }
        } else {
            $order->save(['payment_amount' => $paidAmount]);
        }
    }

    /**
     * 验证订单取消余额
     * @param string $orderNo 订单单号
     * @param boolean $syncRebate 更新返佣
     * @return string
     * @throws Exception
     */
    public static function cancel(string $orderNo, bool $syncRebate = false): string
    {
        $map = ['status' => 0, 'order_no' => $orderNo];
        $order = ShopOrder::mk()->where($map)->findOrEmpty();
        if ($order->isEmpty()) throw new Exception('订单状态异常');
        $code = "CZ{$order['order_no']}";
        // 取消余额奖励
        if ($order['reward_balance'] > 0) BalanceService::cancel($code);
        // 取消积分奖励
        if ($order['reward_integral'] > 0) IntegralService::cancel($code);
        return $code;
    }


    /**
     * 订单支付发放余额
     * @param string $orderNo
     * @param bool $unlock
     * @return string
     * @throws Exception
     */
    public static function confirm(string $orderNo,bool $unlock = false): string
    {
        $map = [['status', '>=', 4], ['order_no', '=', $orderNo]];
        $order = ShopOrder::mk()->where($map)->findOrEmpty();
        if ($order->isEmpty()) throw new Exception('订单状态异常');
        $code = "CZ{$order['order_no']}";
        // 确认奖励余额
        if ($order['reward_balance'] > 0 && ConfigService::get('enable_balance')) {
            $remark = "来自订单 {$order['order_no']} 奖励 {$order['reward_balance']} 余额";
            BalanceService::create($order['unid'], $code, '购物奖励', floatval($order['reward_balance']), $remark, $unlock);
        }
        // 确认奖励积分
        if ($order['reward_integral'] > 0 && ConfigService::get('enable_integral')) {
            $remark = "来自订单 {$order['order_no']} 奖励 {$order['reward_integral']} 积分";
            IntegralService::create($order['unid'], $code, '购物奖励', floatval($order['reward_integral']), $remark, $unlock);
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