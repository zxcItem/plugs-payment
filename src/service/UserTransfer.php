<?php

declare (strict_types=1);

namespace plugin\payment\service;

use plugin\payment\model\PaymentTransfer;
use think\admin\Exception;

/**
 * 用户提现数据服务
 * @class UserTransfer
 * @package plugin\payment\service
 */
class UserTransfer
{
    /**
     * 提现方式配置
     * @var array
     */
    protected static $types = [
        'wechat_wallet'  => '提现到微信零钱（线上）',
        'wechat_qrcode'  => '提现到微信收款码（线下）',
        'alipay_qrcode'  => '提现到支付宝收款码（线下）',
        'alipay_account' => '提现到支付宝账户（线下）',
        'transfer_banks' => '提现到银行卡账户（线下）',
    ];

    /**
     * 获取转账类型名称
     * @param string|null $name
     * @return array|string
     */
    public static function types(?string $name = null)
    {
        return is_null($name) ? self::$types : (self::$types[$name] ?? $name);
    }

    /**
     * 同步刷新用户返佣
     * @param integer $unid
     * @return array [total, count, audit, locks]
     */
    public static function amount(int $unid): array
    {
        if ($unid > 0) {
            $locks = abs(PaymentTransfer::mk()->whereRaw("unid='{$unid}' and status=3")->sum('amount'));
            $total = abs(PaymentTransfer::mk()->whereRaw("unid='{$unid}' and status>=1")->sum('amount'));
            $count = abs(PaymentTransfer::mk()->whereRaw("unid='{$unid}' and status>=4")->sum('amount'));
            $audit = abs(PaymentTransfer::mk()->whereRaw("unid='{$unid}' and status>=1 and status<3")->sum('amount'));
        } else {
            $locks = abs(PaymentTransfer::mk()->whereRaw("status=3")->sum('amount'));
            $total = abs(PaymentTransfer::mk()->whereRaw("status>=1")->sum('amount'));
            $count = abs(PaymentTransfer::mk()->whereRaw("status>=4")->sum('amount'));
            $audit = abs(PaymentTransfer::mk()->whereRaw("status>=1 and status<3")->sum('amount'));
        }
        return [$total, $count, $audit, $locks];
    }

    /**
     * 获取提现配置
     * @param ?string $name
     * @return array|string
     * @throws Exception
     */
    public static function config(?string $name = null)
    {
        $ckey = 'payment.transfer.config';
        $data = sysvar($ckey) ?: sysvar($ckey, sysdata($ckey));
        return is_null($name) ? $data : ($data[$name] ?? '');
    }

    /**
     * 获取转账配置
     * @param ?string $name
     * @return array|string
     * @throws Exception
     */
    public static function payment(?string $name = null)
    {
        $ckey = 'payment.transfer.wxpay';
        $data = sysvar($ckey) ?: sysvar($ckey, sysdata($ckey));
        return is_null($name) ? $data : ($data[$name] ?? '');
    }
}