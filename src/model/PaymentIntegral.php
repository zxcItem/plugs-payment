<?php

declare (strict_types=1);

namespace plugin\payment\model;

use plugin\account\model\Abs;
use plugin\account\model\AccountUser;
use think\model\relation\HasOne;

/**
 * 用户余额模型
 */
class PaymentIntegral extends Abs
{
    /**
     * 余额扩展数据
     * @var array[]
     */
    public static $Types = [
        ['value' => '充值积分', 'amount' => 0, 'name' => 'integral_total'],
        ['value' => '锁定积分', 'amount' => 0, 'name' => 'integral_lock'],
        ['value' => '剩余积分', 'amount' => 0, 'name' => 'integral_usable'],
        ['value' => '支出积分', 'amount' => 0, 'name' => 'integral_used'],
    ];

    /**
     * 关联用户数据
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(AccountUser::class, 'id', 'unid');
    }

    /**
     * 格式化输出时间
     * @param mixed $value
     * @return string
     */
    public function getCancelTimeAttr($value): string
    {
        return format_datetime($value);
    }

    public function setCancelTimeAttr($value): string
    {
        return $this->setCreateTimeAttr($value);
    }

    /**
     * 格式化输出时间
     * @param mixed $value
     * @return string
     */
    public function getUnlockTimeAttr($value): string
    {
        return format_datetime($value);
    }

    public function setUnlockTimeAttr($value): string
    {
        return $this->setCreateTimeAttr($value);
    }
}