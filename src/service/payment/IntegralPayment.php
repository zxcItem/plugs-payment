<?php


declare (strict_types=1);

namespace plugin\payment\service\payment;

use plugin\account\service\contract\AccountInterface;
use plugin\payment\service\contract\PaymentInterface;
use plugin\payment\service\contract\PaymentResponse;
use plugin\payment\service\contract\PaymentUsageTrait;
use plugin\payment\service\IntegralService;
use plugin\payment\service\Payment;
use think\admin\Exception;
use think\Response;

/**
 * 账户积分支付方式
 * @class IntegralPayment
 * @package plugin\payment\service\payment
 */
class IntegralPayment implements PaymentInterface
{
    use PaymentUsageTrait;

    /**
     * 初始化支付配置
     * @return $this
     */
    public function init(): PaymentInterface
    {
        return $this;
    }

    /**
     * 订单信息查询
     * @param string $pcode
     * @return array
     */
    public function query(string $pcode): array
    {
        return [];
    }

    /**
     * 支付通知处理
     * @param array $data
     * @param ?array $notify
     * @return Response
     */
    public function notify(array $data = [], ?array $notify = null): Response
    {
        return response('SUCCESS');
    }

    /**
     * 发起支付退款
     * @param string $pcode 支付单号
     * @param string $amount 退款金额
     * @param string $reason 退款原因
     * @return array [状态, 消息]
     */
    public function refund(string $pcode, string $amount, string $reason = ''): array
    {
        try {
            $this->app->db->transaction(static function () use ($pcode, $reason, $amount) {
                // 记录退款
                $record = static::syncRefund($pcode, $rcode, $amount, $reason);
                // 退回积分
                $remark = "来自订单 {$record->getAttr('order_no')} 退回积分";
                $integral = floatval($amount) / floatval($record->getAttr('payment_amount')) * $record->getAttr('used_integral');
                IntegralService::create($record->getAttr('unid'), $rcode, '账号积分退还', floatval($integral), $remark, true);
            });
            return [1, '发起退款成功！'];
        } catch (\Exception $exception) {
            return [0, $exception->getMessage()];
        }
    }

    /**
     * 创建支付订单
     * @param AccountInterface $account 支付账号
     * @param string $orderNo 交易订单单号
     * @param string $orderTitle 交易订单标题
     * @param string $orderAmount 订单支付金额（元）
     * @param string $payAmount 本次交易积分
     * @param string $payRemark 交易订单描述
     * @param string $payReturn 支付回跳地址
     * @param string $payImages 支付凭证图片
     * @return PaymentResponse
     * @throws Exception
     */
    public function create(AccountInterface $account, string $orderNo, string $orderTitle, string $orderAmount, string $payAmount, string $payRemark = '', string $payReturn = '', string $payImages = ''): PaymentResponse
    {
        try {
            $unid = $this->withUserUnid($account);
            $integral = IntegralService::recount($unid);
            if ($payAmount > $integral['usable']) throw new Exception('可抵扣的积分不足');
            $realAmount = $this->checkLeaveAmount($orderNo, sprintf('%01.2f', IntegralService::ratio(floatval($payAmount))), $orderAmount);
            [$data, $payCode] = [[], Payment::withPaymentCode()];
            $this->app->db->transaction(function () use (&$data, $unid, $realAmount, $orderNo, $orderAmount, $orderTitle, $payCode, $payAmount, $payRemark) {
                // 创建支付行为
                $this->createAction($orderNo, $orderTitle, $orderAmount, $payCode, strval($realAmount), '', '0.00', $payAmount);
                // 扣除积分金额
                $payRemark = $payRemark ?: "抵扣订单 {$orderNo} 金额 {$realAmount} 元";
                IntegralService::create($unid, "DK{$payCode}", $orderTitle, -floatval($payAmount), $payRemark, true);
                // 更新支付行为
                $data = $this->updateAction($payCode, "DK{$payCode}", strval($realAmount), '账户积分支付');
            });
            // 刷新用户积分
            IntegralService::recount($unid);
            // 返回支付结果
            return $this->res->set(true, '积分抵扣完成！', $data);
        } catch (Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }
}