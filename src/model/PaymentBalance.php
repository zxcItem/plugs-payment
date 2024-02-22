<?php

namespace plugin\payment\model;

use plugin\account\model\Abs;

/**
 * 用户余额模型
 */
class PaymentBalance extends Abs
{
    /**
     * 余额扩展数据
     * @var array[]
     */
    public static $Types = [
        ['value' => '充值余额', 'amount' => 0, 'name' => 'balance_total'],
        ['value' => '剩余余额', 'amount' => 0, 'name' => 'balance_usable'],
        ['value' => '锁定余额', 'amount' => 0, 'name' => 'balance_lock'],
        ['value' => '支出余额', 'amount' => 0, 'name' => 'balance_used'],
    ];
}