<?php

declare (strict_types=1);

namespace plugin\payment\model;

use plugin\account\model\Abs;
use plugin\account\model\PluginAccountUser;
use think\model\relation\HasOne;

/**
 * 用户支付退款模型
 * @class PluginPaymentRecord
 * @package plugin\payment\model
 */
class PluginPaymentRefund extends Abs
{
    /**
     * 关联用户数据
     * @return \think\model\relation\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(PluginAccountUser::class, 'id', 'unid');
    }

    /**
     * 关联子支付订单
     * @return \think\model\relation\HasOne
     */
    public function record(): HasOne
    {
        return $this->hasOne(PluginPaymentRecord::class, 'code', 'record_code');
    }

    /**
     * 格式化输出时间
     * @param mixed $value
     * @return string
     */
    public function getRefundTimeAttr($value): string
    {
        return format_datetime($value);
    }

    /**
     * 格式化输入时间
     * @param mixed $value
     * @return string
     */
    public function setRefundTimeAttr($value): string
    {
        return $this->setCreateTimeAttr($value);
    }
}