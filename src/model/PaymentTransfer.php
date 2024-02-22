<?php

declare (strict_types=1);

namespace plugin\payment\model;

use plugin\account\model\Abs;
use plugin\account\model\AccountUser;
use plugin\payment\service\UserTransfer;
use think\model\relation\HasOne;

/**
 * 用户提现模型
 * @class PaymentTransfer
 * @package plugin\payment\model
 */
class PaymentTransfer extends Abs
{
    /**
     * 自动显示类型名称
     * @return array
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        if (isset($data['type'])) {
            $data['type_name'] = UserTransfer::types($data['type']);
        }
        return $data;
    }

    /**
     * 关联用户数据
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(AccountUser::class, 'id', 'unid');
    }
}