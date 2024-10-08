<?php

use think\migration\Migrator;

class InstallPayment extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->_create_plugin_payment_address();
        $this->_create_plugin_payment_config();
        $this->_create_plugin_payment_record();
        $this->_create_plugin_payment_refund();
    }

    /**
     * 插件-支付-地址
     * @class PluginPaymentAddress
     * @table plugin_payment_address
     * @return void
     */
    private function _create_plugin_payment_address()
    {

        // 当前数据表
        $table = 'plugin_payment_address';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '插件-支付-地址',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '主账号ID'])
            ->addColumn('type', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '默认状态(0普通,1默认)'])
            ->addColumn('idcode', 'string', ['limit' => 180, 'default' => '', 'null' => true, 'comment' => '身体证证号'])
            ->addColumn('idimg1', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '身份证正面'])
            ->addColumn('idimg2', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '身份证反面'])
            ->addColumn('user_name', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '收货人姓名'])
            ->addColumn('user_phone', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '收货人手机'])
            ->addColumn('region_prov', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '地址-省份'])
            ->addColumn('region_city', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '地址-城市'])
            ->addColumn('region_area', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '地址-区域'])
            ->addColumn('region_addr', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '地址-详情'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(1已删,0未删)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('type', ['name' => 'i368e636cc_type'])
            ->addIndex('unid', ['name' => 'i368e636cc_unid'])
            ->addIndex('deleted', ['name' => 'i368e636cc_deleted'])
            ->addIndex('user_phone', ['name' => 'i368e636cc_user_phone'])
            ->addIndex('create_time', ['name' => 'i368e636cc_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 插件-支付-配置
     * @class PluginPaymentConfig
     * @table plugin_payment_config
     * @return void
     */
    private function _create_plugin_payment_config()
    {

        // 当前数据表
        $table = 'plugin_payment_config';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '插件-支付-配置',
        ])
            ->addColumn('type', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => '支付类型'])
            ->addColumn('code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '通道编号'])
            ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '支付名称'])
            ->addColumn('cover', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '支付图标'])
            ->addColumn('remark', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '支付说明'])
            ->addColumn('content', 'text', ['default' => NULL, 'null' => true, 'comment' => '支付参数'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '支付状态(1使用,0禁用)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('type', ['name' => 'if27d5755e_type'])
            ->addIndex('code', ['name' => 'if27d5755e_code'])
            ->addIndex('sort', ['name' => 'if27d5755e_sort'])
            ->addIndex('status', ['name' => 'if27d5755e_status'])
            ->addIndex('deleted', ['name' => 'if27d5755e_deleted'])
            ->addIndex('create_time', ['name' => 'if27d5755e_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 插件-支付-行为
     * @class PluginPaymentRecord
     * @table plugin_payment_record
     * @return void
     */
    private function _create_plugin_payment_record()
    {

        // 当前数据表
        $table = 'plugin_payment_record';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '插件-支付-行为',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '主账号编号'])
            ->addColumn('usid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '子账号编号'])
            ->addColumn('code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '发起支付号'])
            ->addColumn('order_no', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '原订单编号'])
            ->addColumn('order_name', 'string', ['limit' => 255, 'default' => '', 'null' => true, 'comment' => '原订单标题'])
            ->addColumn('order_amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '原订单金额'])
            ->addColumn('channel_type', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => '支付通道类型'])
            ->addColumn('channel_code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '支付通道编号'])
            ->addColumn('payment_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '支付生效时间'])
            ->addColumn('payment_trade', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '平台交易编号'])
            ->addColumn('payment_status', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '支付状态(0未付,1已付,2取消)'])
            ->addColumn('payment_amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '实际支付金额'])
            ->addColumn('payment_coupon', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '平台优惠券金额'])
            ->addColumn('payment_images', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '凭证支付图片'])
            ->addColumn('payment_remark', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '支付状态备注'])
            ->addColumn('payment_notify', 'text', ['default' => NULL, 'null' => true, 'comment' => '支付通知内容'])
            ->addColumn('audit_user', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '审核用户(系统用户ID)'])
            ->addColumn('audit_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '审核时间'])
            ->addColumn('audit_status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '审核状态(0已拒,1待审,2已审)'])
            ->addColumn('audit_remark', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '审核描述'])
            ->addColumn('refund_status', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '退款状态(0未退,1已退)'])
            ->addColumn('refund_amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '累计退款'])
            ->addColumn('refund_payment', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '退回金额'])
            ->addColumn('refund_balance', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '退回余额'])
            ->addColumn('refund_integral', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '退回积分'])
            ->addColumn('used_payment', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '支付金额'])
            ->addColumn('used_balance', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '扣除余额'])
            ->addColumn('used_integral', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '扣除积分'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('unid', ['name' => 'id72e373f8_unid'])
            ->addIndex('usid', ['name' => 'id72e373f8_usid'])
            ->addIndex('code', ['name' => 'id72e373f8_code'])
            ->addIndex('order_no', ['name' => 'id72e373f8_order_no'])
            ->addIndex('create_time', ['name' => 'id72e373f8_create_time'])
            ->addIndex('audit_status', ['name' => 'id72e373f8_audit_status'])
            ->addIndex('channel_type', ['name' => 'id72e373f8_channel_type'])
            ->addIndex('channel_code', ['name' => 'id72e373f8_channel_code'])
            ->addIndex('payment_trade', ['name' => 'id72e373f8_payment_trade'])
            ->addIndex('refund_status', ['name' => 'id72e373f8_refund_status'])
            ->addIndex('payment_status', ['name' => 'id72e373f8_payment_status'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 插件-支付-退款
     * @class PluginPaymentRefund
     * @table plugin_payment_refund
     * @return void
     */
    private function _create_plugin_payment_refund()
    {

        // 当前数据表
        $table = 'plugin_payment_refund';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '插件-支付-退款',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '主账号编号'])
            ->addColumn('usid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '子账号编号'])
            ->addColumn('code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '发起支付号'])
            ->addColumn('record_code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '子支付编号'])
            ->addColumn('refund_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '完成时间'])
            ->addColumn('refund_trade', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '交易编号'])
            ->addColumn('refund_status', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '支付状态(0未付,1已付,2取消)'])
            ->addColumn('refund_amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '退款金额'])
            ->addColumn('refund_account', 'string', ['limit' => 180, 'default' => '', 'null' => true, 'comment' => '退回账号'])
            ->addColumn('refund_scode', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => '状态编码'])
            ->addColumn('refund_remark', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '退款备注'])
            ->addColumn('refund_notify', 'text', ['default' => NULL, 'null' => true, 'comment' => '通知内容'])
            ->addColumn('used_payment', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '退回金额'])
            ->addColumn('used_balance', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '退回余额'])
            ->addColumn('used_integral', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '退回积分'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('unid', ['name' => 'icef9ec8c0_unid'])
            ->addIndex('usid', ['name' => 'icef9ec8c0_usid'])
            ->addIndex('code', ['name' => 'icef9ec8c0_code'])
            ->addIndex('record_code', ['name' => 'icef9ec8c0_record_code'])
            ->addIndex('create_time', ['name' => 'icef9ec8c0_create_time'])
            ->addIndex('refund_trade', ['name' => 'icef9ec8c0_refund_trade'])
            ->addIndex('refund_status', ['name' => 'icef9ec8c0_refund_status'])
            ->addIndex('refund_account', ['name' => 'icef9ec8c0_refund_account'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }
}
