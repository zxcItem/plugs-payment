<?php

declare (strict_types=1);

namespace app\payment\controller;

use plugin\account\model\AccountUser;
use plugin\payment\service\BalanceService;
use plugin\payment\model\PaymentBalance;
use think\admin\Controller;
use think\admin\Exception;
use think\admin\extend\CodeExtend;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;

/**
 * 用户余额管理
 * @class Balance
 * @package plugin\payment\controller
 */
class Balance extends Controller
{
    /**
     * 用户余额管理
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $this->type = $this->get['type'] ?? 'index';
        PaymentBalance::mQuery()->layTable(function () {
            $this->title = '用户余额管理';
            $this->balanceTotal = PaymentBalance::mk()->where(['cancel' => 0, 'deleted' => 0])->whereRaw("amount>0")->sum('amount');
            $this->balanceCount = PaymentBalance::mk()->where(['cancel' => 0, 'deleted' => 0])->whereRaw("amount<0")->sum('amount');
        }, function (QueryHelper $query) {
            $query->with(['user'])->like('code,remark')->dateBetween('create_time');
            $query->where(['deleted' => 0, 'cancel' => intval($this->type !== 'index')]);
            $db = AccountUser::mQuery()->like('email|nickname|username|phone#user')->db();
            if ($db->getOptions('where')) $query->whereRaw("unid in {$db->field('id')->buildSql()}");
        });
    }

    /**
     * 交易锁定处理
     * @auth true
     */
    public function unlock()
    {
        try {
            $data = $this->_vali([
                'code.require'   => '单号不能为空！',
                'unlock.require' => '状态不能为空！'
            ]);
            BalanceService::unlock($data['code'], intval($data['unlock']));
            $this->success('交易操作成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 交易状态处理
     * @auth true
     */
    public function cancel()
    {
        try {
            $data = $this->_vali([
                'code.require'   => '单号不能为空！',
                'cancel.require' => '状态不能为空！'
            ]);
            BalanceService::cancel($data['code'], intval($data['cancel']));
            $this->success('交易操作成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 余额充值
     * @auth true
     * @throws Exception
     */
    public function add()
    {
        $data = $this->_vali([
            'unid.require'   => '用户UID不能为空！',
            'code.value'     => CodeExtend::uniqidDate(16, 'CZ'),
            'name.default'   => '平台充值',
            'amount.default' => 0,
            'remark.default' => ''
        ]);
        if ($this->request->isGet()){
            $this->user = AccountUser::extraItem(intval($data['unid']),PaymentBalance::$Types);
            if (empty($this->user)) $this->error('待充值的用户不存在！');
            PaymentBalance::mForm('form');
        }else{
            BalanceService::create(intval($data['unid']),$data['code'],$data['name'],floatval($data['amount']),$data['remark'],true);
            $this->success('余额充值成功！');
        }
    }

    /**
     * 删除余额记录
     * @auth true
     */
    public function remove()
    {
        try {
            $data = $this->_vali([
                'code.require' => '单号不能为空！',
            ]);
            BalanceService::remove($data['code']);
            $this->success('交易操作成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}