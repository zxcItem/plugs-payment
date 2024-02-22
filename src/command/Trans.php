<?php

declare (strict_types=1);

namespace plugin\payment\command;

use plugin\payment\service\BalanceService;
use app\wechat\service\WechatService;
use plugin\account\service\Account;
use plugin\account\model\AccountBind;
use plugin\payment\model\PaymentTransfer;
use think\admin\Command;
use think\admin\Exception;
use think\admin\storage\LocalStorage;
use think\console\Input;
use think\console\Output;
use think\db\exception\DbException;
use think\Model;
use WeChat\Exceptions\InvalidDecryptException;
use WeChat\Exceptions\InvalidResponseException;
use WeChat\Exceptions\LocalCacheException;
use WePay\Transfers;
use WePay\TransfersBank;

/**
 * 用户提现处理
 * @class Trans
 * @package plugin\payment\command
 */
class Trans extends Command
{
    /**
     * 用户提现配置
     * @return void
     */
    protected function configure()
    {
        $this->setName('payment:trans');
        $this->setDescription('执行提现打款操作');
    }

    /**
     * 执行微信提现操作
     * @param Input $input
     * @param Output $output
     * @throws Exception
     * @throws DbException
     */
    protected function execute(Input $input, Output $output)
    {
        $now = date('Y-m-d H:i:s');
        $map = [['type', 'in', ['wechat_banks', 'wechat_wallet']], ['status', 'in', [3, 4]]];
        [$total, $count, $error] = [PaymentTransfer::mk()->where($map)->count(), 0, 0];
        /** @var PaymentTransfer $item */
        foreach (PaymentTransfer::mk()->where($map)->cursor() as $model) try {
            $this->queue->message($total, ++$count, sprintf('开始处理订单 %s 提现', $model->getAttr('code')));
            if ($model->getAttr('status') === 3) {
                $this->queue->message($total, $count, sprintf('尝试处理订单 %s 打款', $model->getAttr('code')), 1);
                if ($model->getAttr('type') === 'wechat_banks') {
                    [$config, $result] = $this->createTransferBank($model);
                } else {
                    [$config, $result] = $this->createTransferWallet($model);
                }
                if ($result['return_code'] === 'SUCCESS' && $result['result_code'] === 'SUCCESS') {
                    $model->save([
                        'status'      => 4,
                        'appid'       => $config['appid'],
                        'openid'      => $config['openid'],
                        'trade_no'    => $result['partner_trade_no'],
                        'trade_time'  => $result['payment_time'] ?? $now,
                        'change_time' => $now,
                        'change_desc' => '创建微信提现成功',
                    ]);
                } else {
                    $model->save(['change_time' => $now, 'change_desc' => $result['err_code_des'] ?? '线上提现失败']);
                }
            } elseif ($model->getAttr('status') === 4) {
                $this->queue->message($total, $count, sprintf('刷新提现订单 %s 状态', $model->getAttr('code')), 1);
                $model->getAttr('type') === 'wechat_banks' ? $this->queryTransferBank($model) : $this->queryTransferWallet($model);
            }
        } catch (\Exception $exception) {
            $error++;
            $this->queue->message($total, $count, sprintf('处理提现订单 %s 失败, %s', $model->getAttr('code'), $exception->getMessage()), 1);
            $model->save(['change_time' => $now, 'change_desc' => $exception->getMessage()]);
        }
        $this->setQueueSuccess(sprintf('此次共处理 %d 笔提现操作, 其中有 %d 笔处理失败。', $total, $error));
    }

    /**
     * 尝试提现转账到银行卡
     * @param PaymentTransfer $model
     * @return array [config, result]
     * @throws InvalidDecryptException
     * @throws InvalidResponseException
     * @throws LocalCacheException
     * @throws Exception
     */
    private function createTransferBank(PaymentTransfer $model): array
    {
        $config = $this->getConfig($model->getAttr('unid'));
        return [$config, TransfersBank::instance($config)->create([
            'partner_trade_no' => $model->getAttr('code'),
            'enc_bank_no'      => $model->getAttr('bank_code'),
            'enc_true_name'    => $model->getAttr('bank_user'),
            'bank_code'        => $model->getAttr('bank_wseq'),
            'amount'           => intval($model->getAttr('amount') - $model->getAttr('charge_amount')) * 100,
            'desc'             => '微信银行卡提现',
        ])];
    }

    /**
     * 获取微信提现参数
     * @param int $unid
     * @return array
     * @throws Exception
     */
    private function getConfig(int $unid): array
    {
        $data = sysdata('payment.transfer.wxpay');
        if (empty($data)) throw new Exception('未配置微信提现商户！');
        // 商户证书文件处理
        $local = LocalStorage::instance();
        if (!$local->has($file1 = "{$data['wechat_mch_id']}_key.pem", true)) {
            $local->set($file1, $data['wechat_mch_key_text'], true);
        }
        if (!$local->has($file2 = "{$data['wechat_mch_id']}_cert.pem", true)) {
            $local->set($file2, $data['wechat_mch_cert_text'], true);
        }
        // 获取用户支付信息
        [$appid, $openid] = $this->withAppidAndOpenid($unid, $data['wechat_type']);
        return [
            'appid'      => $appid,
            'openid'     => $openid,
            'mch_id'     => $data['wechat_mch_id'],
            'mch_key'    => $data['wechat_mch_key'],
            'ssl_key'    => $local->path($file1, true),
            'ssl_cer'    => $local->path($file2, true),
            'cache_path' => syspath('runtime/wechat'),
        ];
    }

    /**
     * 根据配置获取用户OPENID
     * @param integer $unid 用户编号
     * @param string $type 授权类型 (normal|wxapp|wechat)
     * @return array|null
     * @throws Exception
     */
    private function withAppidAndOpenid(int $unid, string $type = 'normal'): ?array
    {
        // 获取用户 Openid
        $map = [['unid', '=', $unid]];
        if (in_array($type, [Account::WXAPP, Account::WECHAT])) {
            $map[] = ['type', '=', $type];
        } else {
            $map[] = ['openid', '<>', ''];
        }
        $openid = AccountBind::mk()->where($map)->value('openid');
        if (empty($openid)) throw new Exception("无法读取打款数据！");

        // 获取公众号 Appid
        $appid1 = WechatService::getAppid();
        $appid2 = sysdata('plugin.wechat.wxapp')['appid'];
        if ($type === Account::WXAPP) return [$appid2, $openid];
        if ($type === Account::WECHAT) return [$appid1, $openid];
        return [$appid1, $openid];
    }

    /**
     * 尝试提现转账到微信钱包
     * @param PaymentTransfer $model
     * @return array [config, result]
     * @throws InvalidResponseException
     * @throws LocalCacheException
     * @throws Exception
     */
    private function createTransferWallet(PaymentTransfer $model): array
    {
        $config = $this->getConfig($model->getAttr('unid'));
        return [$config, Transfers::instance($config)->create([
            'openid'           => $config['openid'],
            'amount'           => intval($model->getAttr('amount') - $model->getAttr('charge_amount')) * 100,
            'partner_trade_no' => $model->getAttr('code'),
            'spbill_create_ip' => '127.0.0.1',
            'check_name'       => 'NO_CHECK',
            'desc'             => '微信余额提现！',
        ])];
    }

    /**
     * 查询更新提现打款状态
     * @param PaymentTransfer $model
     * @throws InvalidResponseException
     * @throws LocalCacheException
     * @throws Exception
     */
    private function queryTransferBank(PaymentTransfer $model)
    {
        $config = $this->getConfig($model->getAttr('unid'));
        [$config['appid'], $config['openid']] = [$model->getAttr('appid'), $model->getAttr('openid')];
        $result = TransfersBank::instance($config)->query($model->getAttr('trade_no'));
        if ($result['return_code'] === 'SUCCESS' && $result['result_code'] === 'SUCCESS') {
            if ($result['status'] === 'SUCCESS') {
                $model->save([
                    'status'      => 5,
                    'appid'       => $config['appid'],
                    'openid'      => $config['openid'],
                    'trade_time'  => $result['pay_succ_time'] ?: date('Y-m-d H:i:s'),
                    'change_time' => date('Y-m-d H:i:s'),
                    'change_desc' => '微信提现打款成功',
                ]);
                BalanceService::unlock($model->getAttr('code'));
            }
            if (in_array($result['status'], ['FAILED', 'BANK_FAIL'])) {
                $model->save([
                    'status'      => 0,
                    'change_time' => date('Y-m-d H:i:s'),
                    'change_desc' => '微信提现打款失败',
                ]);
                // 刷新用户可提现余额
                BalanceService::cancel($model->getAttr('code'));
            }
            BalanceService::recount($model->getAttr('unid'));
        }
    }

    /**
     * 查询更新提现打款状态
     * @param PaymentTransfer $model
     * @throws InvalidResponseException
     * @throws LocalCacheException
     * @throws Exception
     */
    private function queryTransferWallet(PaymentTransfer $model)
    {
        $config = $this->getConfig($model->getAttr('unid'));
        [$config['appid'], $config['openid']] = [$model->getAttr('appid'), $model->getAttr('openid')];
        $result = Transfers::instance($config)->query($model->getAttr('trade_no'));
        if ($result['return_code'] === 'SUCCESS' && $result['result_code'] === 'SUCCESS') {
            $model->save([
                'status'      => 5,
                'appid'       => $config['appid'],
                'openid'      => $config['openid'],
                'trade_time'  => $result['payment_time'],
                'change_time' => date('Y-m-d H:i:s'),
                'change_desc' => '微信提现打款成功！',
            ]);
            BalanceService::unlock($model->getAttr('code'));
            BalanceService::recount($model->getAttr('unid'));
        }
    }
}