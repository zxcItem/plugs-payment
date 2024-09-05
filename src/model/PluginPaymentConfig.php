<?php

declare (strict_types=1);

namespace plugin\payment\model;

use plugin\account\model\Abs;

/**
 * 用户支付参数模型
 * @class PluginPaymentConfig
 * @package plugin\payment\model
 */
class PluginPaymentConfig extends Abs
{
    protected $oplogName = '商城支付配置';
    protected $oplogType = '商城支付配置';

    /**
     * 格式化数据格式
     * @param mixed $value
     * @return string
     */
    public function setContentAttr($value): string
    {
        return $this->setExtraAttr($value);
    }

    /**
     * 格式化数据格式
     * @param mixed $value
     * @return array
     */
    public function getContentAttr($value): array
    {
        return $this->getExtraAttr($value);
    }
}