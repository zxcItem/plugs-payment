<?php

declare (strict_types=1);

namespace plugin\payment;

use plugin\payment\command\Trans;
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
     * 插件服务注册
     * @return void
     */
    public function register(): void
    {
        $this->commands([Trans::class]);
    }

    /**
     * 支付模块菜单配置
     * @return array[]
     */
    public static function menu(): array
    {
        $code = app(static::class)->appCode;
        // 设置插件菜单
        return [
            [
                'name' => '支付管理',
                'subs' => [
                    [
                        'name' => '支付管理',
                        'subs' => [
                            ['name' => '支付配置管理', 'icon' => 'layui-icon layui-icon-user', 'node' => "{$code}/config/index"],
                            ['name' => '支付行为管理', 'icon' => 'layui-icon layui-icon-edge', 'node' => "{$code}/record/index"],
                            ['name' => '支付退款管理', 'icon' => 'layui-icon layui-icon-firefox', 'node' => "{$code}/refund/index"],
                        ],
                    ],
                    [
                        'name' => '资金管理',
                        'subs' => [
                            ['name' => '资金统计报表', 'icon' => 'layui-icon layui-icon-chart', 'node' => "{$code}/portal/fund"],
                            ['name' => '账号余额管理', 'icon' => 'layui-icon layui-icon-cellphone', 'node' => "{$code}/balance/index"],
                            ['name' => '账号积分管理', 'icon' => 'layui-icon layui-icon-find-fill', 'node' => "{$code}/integral/index"],
                        ],
                    ],
                ]
            ]
        ];
    }
}