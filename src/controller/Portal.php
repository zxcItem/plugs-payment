<?php

namespace plugin\payment\controller;

use plugin\payment\model\PluginPaymentBalance;
use plugin\payment\model\PluginPaymentIntegral;
use think\admin\Controller;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

/**
 * 用户数据统计表
 * @class Portal
 * @package plugin\payment\controller
 */
class Portal extends Controller
{

    /**
     * 积分余额统计
     * @auth true
     * @menu true
     * @return void
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function fund()
    {
        $this->title = '积分余额统计';
        $this->balanceTotal = PluginPaymentBalance::mk()->whereRaw("amount>0")->sum('amount');
        $this->balanceCostTotal = PluginPaymentBalance::mk()->whereRaw("amount<0")->sum('amount');
        $this->integralTotal = PluginPaymentIntegral::mk()->whereRaw("amount>0")->sum('amount');
        $this->integralCostTotal = PluginPaymentIntegral::mk()->whereRaw("amount<0")->sum('amount');

        // 近十天的用户及交易趋势
        if (empty($this->accountAmount = $this->app->cache->get('accountAmount', []))) {
            $field = ['count(1)' => 'count', 'substr(create_time,1,10)' => 'mday'];

            // 统计余额数据
            $model = PluginPaymentBalance::mk()->field($field + ['sum(case when amount>0 then amount else 0 end)' => 'amount1', 'sum(case when amount<0 then amount else 0 end)' => 'amount2']);
            $balances = $model->whereTime('create_time', '-10 days')->where(['deleted' => 0])->group('mday')->select()->column(null, 'mday');

            // 统计积分数据
            $model = PluginPaymentIntegral::mk()->field($field + ['sum(case when amount>0 then amount else 0 end)' => 'amount1', 'sum(case when amount<0 then amount else 0 end)' => 'amount2']);
            $integrals = $model->whereTime('create_time', '-10 days')->where(['deleted' => 0])->group('mday')->select()->column(null, 'mday');

            // 数据格式转换
            foreach ($balances as &$balance) $balance = $balance instanceof Model ? $balance->toArray() : $balance;
            foreach ($integrals as &$integral) $integral = $integral instanceof Model ? $integral->toArray() : $integral;
            // 组装15天的统计数据
            for ($i = 30; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i}days"));
                $this->accountAmount[] = [
                    '当天日期' => date('m-d', strtotime("-{$i}days")),
                    '剩余余额' => PluginPaymentBalance::mk()->whereRaw("create_time<='{$date} 23:59:59' and deleted=0")->sum('amount'),
                    '剩余积分' => PluginPaymentIntegral::mk()->whereRaw("create_time<='{$date} 23:59:59' and deleted=0")->sum('amount'),
                    '充值余额' => ($balances[$date] ?? [])['amount1'] ?? 0,
                    '消费余额' => ($balances[$date] ?? [])['amount2'] ?? 0,
                    '充值积分' => ($integrals[$date] ?? [])['amount1'] ?? 0,
                    '消费积分' => ($integrals[$date] ?? [])['amount2'] ?? 0,
                ];
            }
            $this->app->cache->set('accountAmount', $this->accountAmount, 60);
        }
        $this->fetch();
    }
}