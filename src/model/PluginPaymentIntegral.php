<?php

declare (strict_types=1);

namespace plugin\payment\model;

use plugin\account\model\Abs;
use plugin\account\model\PluginAccountUser;
use think\model\relation\HasOne;

/**
 * 用户积分模型
 * @class PluginPaymentIntegral
 * @package plugin\payment\model
 */
class PluginPaymentIntegral extends Abs
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