<?php

declare (strict_types=1);

namespace plugin\payment;

use plugin\account\Service as AccountService;
use think\admin\Plugin;


/**
 * 组件注册服务
 * @class Service
 * @package plugin\payment
 */
class Service extends Plugin
{
    /**
     * 定义插件名称
     * @var string
     */
    protected $appName = '支付管理';

    /**
     * 定义安装包名
     * @var string
     */
    protected $package = 'xiaochao/plugs-payment';

    /**
     * 支付模块菜单配置
     * @return array[]
     */
    public static function menu(): array
    {
        $code = app(static::class)->appCode;
        return [
            [
                'name' => '支付管理',
                'subs' => [
                    ['name' => '支付配置管理', 'icon' => 'layui-icon layui-icon-set', 'node' => "{$code}/config/index"],
                    ['name' => '支付行为管理', 'icon' => 'layui-icon layui-icon-edge', 'node' => "{$code}/record/index"],
                    ['name' => '支付退款管理', 'icon' => 'layui-icon layui-icon-firefox', 'node' => "{$code}/refund/index"],
                    ['name' => '余额明细管理', 'icon' => 'layui-icon layui-icon-rmb', 'node' => "{$code}/balance/index"],
                    ['name' => '积分明细管理', 'icon' => 'layui-icon layui-icon-diamond', 'node' => "{$code}/integral/index"],
                ],
            ]
        ];
    }
}