<?php


declare (strict_types=1);

namespace plugin\payment\controller;

use plugin\account\model\AccountUser;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 用户资产管理
 * @class Master
 * @package plugin\payment\controller
 */
class Master extends Controller
{
    /**
     * 用户资产管理
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        AccountUser::mQuery()->layTable(function () {
            $this->title = '用户资产管理';
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => 1]);
            $query->like('code,phone,email,username,nickname')->dateBetween('create_time');
        });
    }
}