delimiter ;;

drop function if exists serial_last;;
create function serial_last(
    _serial_key varchar(20)
) returns bigint no sql comment '上一个流水号'
begin
    return json_extract(@serial_last,concat('$.',_serial_key));
end;;

drop table if exists external_test;;
create table external_test(
    id int unsigned not null comment 'id',
    data varchar(50) not null comment '交易流水号',
    time int unsigned not null comment '成功时间',
    primary key(id)
) comment '测试表';;

drop table if exists external_import_launch;;
create table external_import_launch(
    import_serial bigint unsigned not null comment '转入单号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
	  external_type varchar(10) not null comment '三方平台：fg-FunGaming',
    launch_data json not null comment '外接口数据',
    launch_money double not null comment '转入金额',
    launch_time int unsigned not null comment '提交时间',
	primary key(import_serial)
) comment '从三方平台转入额度';;

drop table if exists external_import_success;;
create table external_import_success(
    import_serial bigint unsigned not null comment '转入单号',
    success_deal_serial bigint unsigned not null comment '交易流水号',
    success_time int unsigned not null comment '成功时间',
    success_data json not null comment '外接口数据',
	primary key(import_serial)
) comment '转入成功';;

drop table if exists external_import_failure;;
create table external_import_failure(
    import_serial bigint unsigned not null comment '转入单号',
    failure_time int unsigned not null comment '失败时间',
    failure_data json not null comment '外接口数据',
	primary key(import_serial)
) comment '转入失败';;

drop table if exists staff_deposit;;
create table staff_deposit(
    deal_serial bigint unsigned not null comment '入款交易流水号',
    staff_id int unsigned not null comment '员工id',
    staff_name varchar(20) not null comment '员工姓名',
    staff_deposit_id int unsigned not null comment '手工存入操作id',
    deposit_type tinyint not null comment '存入项目：0-手工存入，1-取消出款，2-活动优惠',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    money double not null comment '入款金额',
    deposit_audit double not null comment '入款稽核',
    coupon_audit double not null comment '活动稽核',
    memo varchar(100) not null comment '备注',
    deposit_time int unsigned not null comment '入款时间',
    index(user_id,deposit_time),
    primary key(deal_serial)
) comment '手工存入交易';;

drop table if exists external_audit;;
create table external_audit(
	audit_serial bigint unsigned not null comment '稽核单号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
	external_type varchar(10) not null comment '三方平台：fg-FunGaming',
	external_data json not null comment '外接口数据',
	game_key varchar(20) not null comment '游戏key',
	play_time int unsigned not null comment '游戏时间',
	audit_deal_serial bigint unsigned not null comment '交易流水号',
	audit_amount double not null comment '打码金额',
    audit_time int unsigned not null comment '稽核时间',
	primary key(audit_serial)
) comment '三方平台打码稽核';;

drop table if exists external_export_launch;;
create table external_export_launch(
    export_serial bigint unsigned not null comment '转出单号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
	external_type varchar(10) not null comment '三方平台：fg-FunGaming',
    launch_data json not null comment '外接口数据',
    launch_money double not null comment '转出金额',
    launch_deal_serial bigint unsigned not null comment '交易流水号',
    launch_time int unsigned not null comment '提交时间',
	primary key(export_serial)
) comment '转出额度到三方平台';;

drop table if exists external_export_success;;
create table external_export_success(
    export_serial bigint unsigned not null comment '转出单号',
    success_time int unsigned not null comment '成功时间',
    success_data json not null comment '外接口数据',
	primary key(export_serial)
) comment '转出成功';;

drop table if exists external_export_failure;;
create table external_export_failure(
    export_serial bigint unsigned not null comment '转出单号',
    failure_deal_serial bigint unsigned not null comment '交易流水号',
    failure_time int unsigned not null comment '失败时间',
    failure_data json not null comment '外接口数据',
	primary key(export_serial)
) comment '转出失败';;

drop table if exists bet_normal;;
create table bet_normal(
    bet_serial bigint unsigned not null comment '投注单号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    game_key varchar(20) not null comment '彩种key',
    rule_list json not null comment '投注内容数组[{"play_key":"k","number":"1,2","price":2,"quantity":1,"rebate":0},...]',
    period varchar(20) not null comment '期号',
    multiple int unsigned not null comment '倍数',
    primary key(bet_serial)
) comment '普通投注';;

drop table if exists bet_chase;;
create table bet_chase(
    bet_serial bigint unsigned not null comment '投注单号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    game_key varchar(20) not null comment '彩种key',
    rule_list json not null comment '投注内容数组[{"play_key":"k","number":"1,2","price":2,"quantity":1,"rebate":0},...]',
    period_list json not null comment '期号数组[{"period","20181212001","multiple",1},...]',
    chase_mode json not null comment '追号模式{"type":"multiple","step":2,"multiple",2}',
    stop_mode tinyint not null comment '0-追不停，1-中奖即停',
    primary key(bet_serial)
) comment '追号投注';;

drop table if exists bet_settle;;
create table bet_settle(
    bet_serial bigint unsigned not null comment '投注单号',
    period varchar(20) not null comment '期号',
    status tinyint not null comment '1:正常开奖，-1:注单取消/追号停止,-2:期号取消',
    unit_list json not null comment '结算内容数组[{"rule_id":1,"result":1,"bet":10,"bonus":0,"rebate":1,"revert":0},...]',
    primary key(bet_serial,period)
) comment '投注结算';;

drop table if exists bet_form;;
create table bet_form(
    bet_serial bigint unsigned not null comment '投注单号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    game_key varchar(20) not null comment '彩种key',
    rule_count int unsigned not null comment '内容条数',
    period_count int unsigned not null comment '追号期数',
    settle_count int unsigned not null comment '已结算期数',
    bet_launch double not null comment '总投注金额',
    bet double not null comment '总有效投注',
    bonus double not null comment '总派奖',
    rebate double not null comment '总返点',
    revert double not null comment '总退款',
    launch_time int unsigned not null comment '投注时间',
    launch_deal_serial bigint unsigned not null comment '投注交易流水号',
    index (game_key),
    primary key(bet_serial)
) comment '下注';;

drop table if exists bet_rule;;
create table bet_rule(
    bet_serial bigint unsigned not null comment '投注单号',
    rule_id int unsigned not null comment '内容序号',
    play_key varchar(20) not null comment '玩法类型key',
    number json not null comment '投注号码',
    price double not null comment '每注金额',
    quantity double not null comment '注数',
    rebate_rate double not null comment '返点比例',
    amount double as (price*quantity) comment '金额',
    primary key(bet_serial,rule_id)
) comment '投注内容';;

drop table if exists bet_period;;
create table bet_period(
    bet_serial bigint unsigned not null comment '投注单号',
    period varchar(20) not null comment '期号',
    multiple int unsigned not null comment '倍数',
    bet_launch double not null comment '当期投注金额',
    bet double not null comment '当期有效投注',
    bonus double not null comment '当期派奖',
    rebate double not null comment '当期返点',
    revert double not null comment '当期退款',
    status tinyint not null comment '0:未开奖，1:已开奖，-1:注单取消/追号停止,-2:期号取消',
    settle_time int unsigned not null comment '结算时间',
    settle_deal_serial bigint unsigned not null comment '结算交易流水号',
    index(period),
    primary key(bet_serial,period)
) comment '投注期号';;

drop table if exists bet_unit;;
create table bet_unit(
    bet_serial bigint unsigned not null comment '投注单号',
    period varchar(20) not null comment '期号',
    rule_id int unsigned not null comment '内容序号',
    bet_launch double not null comment '投注',
    result tinyint not null comment '0:未开奖，1:未中奖，2:已中奖，3:和',
    bet double not null comment '有效投注',
    bonus double not null comment '派奖',
    rebate double not null comment '返点',
    revert double not null comment '退款',
    primary key(bet_serial,period,rule_id)
) comment '投注结果';;

drop table if exists withdraw_launch;;
create table withdraw_launch(
    withdraw_serial bigint unsigned not null comment '出款单号',
    launch_deal_serial bigint unsigned not null comment '交易流水号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    layer_id int unsigned not null comment '代理/会员层级id',
    launch_money double not null comment '提款金额',
    deposit_audit double not null comment '稽核金额',
    handling_fee double not null comment '手续费',
    withdraw_money double not null comment '出款金额',
    bank_name varchar(20) not null comment '银行名称',
    bank_branch varchar(20) not null comment '开户网点',
    account_number varchar(20) not null comment '银行账号',
    account_name varchar(20) not null comment '真实姓名',
    launch_time int unsigned not null comment '提交时间',
    launch_device tinyint unsigned not null comment '来源设备类型：0-PC，1-手机浏览器，2-苹果手机app，3-安卓手机app',
    must_inspect tinyint unsigned not null comment '0-不需要审核，1-需要审核',
    primary key(withdraw_serial)
) comment '出款申请';;

drop table if exists withdraw_lock;;
create table withdraw_lock(
    withdraw_serial bigint unsigned not null comment '入款单号',
    lock_type tinyint not null comment '0-出款锁，1-审核锁',
    lock_staff_id int unsigned not null comment '加锁员工id',
    lock_staff_name varchar(20) not null comment '加锁员工姓名',
    lock_time int unsigned not null comment '加锁时间',
    primary key(withdraw_serial)
) comment '入款记录锁';;

drop table if exists withdraw_accept;;
create table withdraw_accept(
    withdraw_serial bigint unsigned not null comment '出款单号',
    accept_staff_id int unsigned not null comment '审核人id',
    accept_staff_name varchar(20) not null comment '审核人姓名',
    accept_time int unsigned not null comment '审核时间',
    primary key(withdraw_serial)
) comment '允许出款';;

drop table if exists withdraw_reject;;
create table withdraw_reject(
    withdraw_serial bigint unsigned not null comment '出款单号',
    reject_deal_serial bigint unsigned not null comment '交易流水号',
    reject_staff_id int unsigned not null comment '审核人id',
    reject_staff_name varchar(20) not null comment '审核人姓名',
    reject_reason varchar(200) not null comment '拒绝理由',
    reject_time int unsigned not null comment '拒绝时间',
    primary key(withdraw_serial)
) comment '拒绝出款';;

drop table if exists withdraw_finish;;
create table withdraw_finish(
    withdraw_serial bigint unsigned not null comment '出款单号',
    finish_staff_id int unsigned not null comment '出款人id',
    finish_staff_name varchar(20) not null comment '出款人姓名',
    finish_time int unsigned not null comment '出款时间',
    primary key(withdraw_serial)
) comment '出款成功';;

drop table if exists withdraw_cancel;;
create table withdraw_cancel(
    withdraw_serial bigint unsigned not null comment '出款单号',
    cancel_deal_serial bigint unsigned not null comment '交易流水号',
    cancel_staff_id int unsigned not null comment '出款人id',
    cancel_staff_name varchar(20) not null comment '出款人姓名',
    cancel_reason varchar(200) not null comment '失败原因',
    cancel_time int unsigned not null comment '失败时间',
    primary key(withdraw_serial)
) comment '出款失败';;

drop table if exists deposit_launch;;
create table deposit_launch(
    deposit_serial bigint unsigned not null comment '入款单号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    passage_id int unsigned not null comment '通道id',
    passage_name varchar(20) not null comment '通道名称',
    route_id int unsigned not null comment '路线id',
    launch_money double not null comment '提交金额',
    launch_time int unsigned not null comment '提交时间',
    launch_device tinyint unsigned not null comment '来源设备类型：0-PC，1-手机浏览器，2-苹果手机app，3-安卓手机app',
    index(user_id,launch_time),
    primary key(deposit_serial)
) comment '入款申请';;

drop table if exists deposit_bank;;
create table deposit_bank(
    deposit_serial bigint unsigned not null comment '入款单号',
    from_type tinyint not null comment '存款方式：1-网银，2-手机银行、3-ATM自动柜员、4-ATM现金、5-银行柜台、11-支付宝、21-微信、22-QQ钱包、23-财付通',
    from_name varchar(20) not null comment '入款人姓名',
    to_bank_name varchar(20) not null comment '存入银行',
    to_bank_branch varchar(20) not null comment '存入开户网点',
    to_account_number varchar(20) not null comment '存入银行账号',
    to_account_name varchar(20) not null comment '存入开户名',
    primary key(deposit_serial)
) comment '公司入款';;

drop table if exists deposit_gateway;;
create table deposit_gateway(
    deposit_serial bigint unsigned not null comment '入款单号',
    gate_key varchar(10) not null comment '三方key',
    gate_name varchar(20) not null comment '三方名字',
    way_key varchar(10) not null comment '方式key',
    way_name varchar(20) not null comment '方式名字',
    to_account_number varchar(20) not null comment '商户号',
    to_account_name varchar(20) not null comment '商户名称',
    primary key(deposit_serial)
) comment '三方入款';;

drop table if exists deposit_simple;;
create table deposit_simple(
    deposit_serial bigint unsigned not null comment '入款单号',
    pay_url varchar(200) not null comment '支付网址',
    memo varchar(200) not null comment '备注',
    primary key(deposit_serial)
) comment '快捷入款';;

drop table if exists deposit_finish;;
create table deposit_finish(
    deposit_serial bigint unsigned not null comment '入款单号',
    finish_deal_serial bigint unsigned not null comment '交易流水号',
    finish_money double not null comment '实际存入金额',
    deposit_audit double not null comment '充值稽核',
    coupon_audit double not null comment '优惠稽核',
    finish_time int unsigned not null comment '到帐时间',
    finish_staff_id int unsigned not null comment '操作员工id',
    finish_staff_name varchar(20) not null comment '操作员工姓名',
    primary key(deposit_serial)
) comment '入款成功';;

drop table if exists deposit_cancel;;
create table deposit_cancel(
    deposit_serial bigint unsigned not null comment '入款单号',
    cancel_time int unsigned not null comment '失败时间',
    cancel_staff_id int unsigned not null comment '操作员工id',
    cancel_staff_name varchar(20) not null comment '操作员工姓名',
    cancel_reason varchar(200) not null comment '失败原因',
    primary key(deposit_serial)
) comment '入款失败';;

drop table if exists account;;
create table account(
    user_id int not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    money double not null comment '账户余额',
    deposit_audit double not null comment '入款稽核',
    coupon_audit double not null comment '活动稽核',
    primary key(user_id)
) comment '用户账户';;

drop table if exists staff_withdraw;;
create table staff_withdraw(
    deal_serial bigint unsigned not null comment '出款交易流水号',
    staff_id int unsigned not null comment '员工id',
    staff_name varchar(20) not null comment '员工姓名',
    staff_withdraw_id int unsigned not null comment '手工提出操作id',
    withdraw_type tinyint not null comment '提出项目：0-手工提出，1-取消存款，2-扣除非法下注派彩，3-放弃存款优惠，4-其他出款',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    money double not null comment '出款金额',
    deposit_audit double not null comment '入款稽核',
    coupon_audit double not null comment '活动稽核',
    memo varchar(100) not null comment '备注',
    withdraw_time int unsigned not null comment '出款时间',
    index(user_id,withdraw_time),
    primary key(deal_serial)
) comment '手工提出交易';;

drop table if exists coupon_offer;;
create table coupon_offer(
    coupon_id bigint unsigned auto_increment not null comment '活动单号',
    coupon_type varchar(20) not null comment '活动类型： daily-每日加奖，upgrade1-会员晋级奖励，upgrade2-代理晋级奖励，deposit-充值送彩金',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    coupon_money double not null comment '奖励金额',
    coupon_audit double not null comment '活动稽核',
    offer_time int unsigned not null comment '发放时间',
    index(coupon_type,offer_time,user_key),
    primary key(coupon_id)
) comment '活动发放';;

drop table if exists coupon_take;;
create table coupon_take(
    coupon_id bigint unsigned not null comment '活动单号',
    deal_serial bigint unsigned not null comment '交易流水号',
    take_time int unsigned not null comment '领取时间',
    unique(deal_serial),
    primary key(coupon_id)
) comment '活动领取';;

drop table if exists subsidy_deliver;;
create table subsidy_deliver(
    deliver_deal_serial bigint unsigned not null comment '交易流水号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    daily int unsigned not null comment '天 php:intval(date(Ymd))',
    subsidy double not null comment '反水金额',
    deliver_time int unsigned not null comment '派发时间',
    unique(user_id,daily),
    primary key(deliver_deal_serial)
) comment '反水结算';;

drop table if exists deal;;
create table deal(
    deal_serial bigint unsigned not null comment '交易流水号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    vary_money double not null comment '账户余额变动',
    vary_deposit_audit double not null comment '入款稽核变动',
    vary_coupon_audit double not null comment '活动稽核变动',
    old_money double not null comment '旧账户余额',
    old_deposit_audit double not null comment '旧入款稽核',
    old_coupon_audit double not null comment '旧活动稽核',
    new_money double not null comment '新账户余额',
    new_deposit_audit double not null comment '新入款稽核',
    new_coupon_audit double not null comment '新活动稽核',
    deal_type varchar(50) not null comment '交易类型-关联表名称',
    summary json not null comment '摘要-关联表的主要数据',
    deal_time int unsigned not null comment '交易时间',
    index(user_id,deal_time),
    primary key(deal_serial)
) comment '交易';;

drop table if exists serial_setting;;
create table serial_setting(
    serial_key varchar(20) not null comment '流水号key',
    increment int unsigned not null comment '步长',
    offset int unsigned not null comment '偏移',
    digit tinyint unsigned not null comment '位数',
    primary key(serial_key)
) comment '流水号设置';;

drop table if exists serial_current;;
create table serial_current(
    serial_key varchar(20) not null comment '流水号key',
    current bigint unsigned not null comment '当前流水号',
    primary key(serial_key)
) engine memory comment '流水号全局数值';;

drop table if exists brokerage_deliver;;
create table brokerage_deliver(
    deliver_deal_serial bigint unsigned not null comment '交易流水号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    daily int unsigned not null comment '天 php:intval(date(Ymd))',
    brokerage double not null comment '佣金',
    deliver_time int unsigned not null comment '派发时间',
    unique(user_id,daily),
    primary key(deliver_deal_serial)
) comment '佣金结算';;

drop trigger if exists external_import_launch_insert;;
create trigger external_import_launch_insert before insert on external_import_launch for each row
begin
    insert into serial_current set serial_key='external_import' on duplicate key update current=current+1;
    set new.import_serial=serial_last('external_import');
    set new.launch_time=unix_timestamp();
end;;

drop trigger if exists external_import_success_insert;;
create trigger external_import_success_insert before insert on external_import_success for each row
begin
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select user_id,user_key,account_name,layer_id,launch_money,0,0,'external_import_success',json_object(
            'external_type',external_type
        ) from external_import_launch where import_serial=new.import_serial;
    set new.success_deal_serial=serial_last('deal');
    set new.success_time=unix_timestamp();
end;;

drop trigger if exists external_import_failure_insert;;
create trigger external_import_failure_insert before insert on external_import_failure for each row
begin
    set new.failure_time=unix_timestamp();
end;;

drop trigger if exists staff_deposit_insert;;
create trigger staff_deposit_insert before insert on staff_deposit for each row
begin
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select new.user_id,new.user_key,new.account_name,new.layer_id,new.money,new.deposit_audit,new.coupon_audit,'staff_deposit',json_object(
            'staff_id',new.staff_id,'deposit_type',new.deposit_type,'memo',new.memo
        );
    set new.deal_serial=serial_last('deal');
    set new.deposit_time=unix_timestamp();
end;;

drop trigger if exists external_audit_insert;;
create trigger external_audit_insert before insert on external_audit for each row
begin
    insert into serial_current set serial_key='external_audit' on duplicate key update current=current+1;
    set new.audit_serial=serial_last('external_audit');
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select new.user_id,new.user_key,new.account_name,new.layer_id,0,0,-new.audit_amount,'external_audit',json_object(
            'external_type',new.external_type
        );
    set new.audit_deal_serial=serial_last('deal');
    set new.audit_time=unix_timestamp();
end;;

drop trigger if exists external_export_launch_insert;;
create trigger external_export_launch_insert before insert on external_export_launch for each row
begin
    insert into serial_current set serial_key='external_export' on duplicate key update current=current+1;
    set new.export_serial=serial_last('external_export');
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select new.user_id,new.user_key,new.account_name,new.layer_id,-new.launch_money,0,0,'external_export_launch',json_object(
            'external_type',new.external_type
        );
    set new.launch_deal_serial=serial_last('deal');
    set new.launch_time=unix_timestamp();
end;;

drop trigger if exists external_export_success_insert;;
create trigger external_export_success_insert before insert on external_export_success for each row
begin
    set new.success_time=unix_timestamp();
end;;

drop trigger if exists external_export_failure_insert;;
create trigger external_export_failure_insert before insert on external_export_failure for each row
begin
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select user_id,user_key,account_name,layer_id,launch_money,0,0,'external_export_failure',json_object(
            'external_type',external_type
        ) from external_export_launch where export_serial=new.export_serial;
    set new.failure_deal_serial=serial_last('deal');
    set new.failure_time=unix_timestamp();
end;;

drop trigger if exists bet_normal_insert;;
create trigger bet_normal_insert before insert on bet_normal for each row
begin
    declare _rule_id,_rule_count int default 0;
    declare _rule_data json;
    declare _sum_rule_amount,_period_bet_launch,_form_bet_launch double default 0;
    insert into serial_current set serial_key='bet' on duplicate key update current=current+1;
    set new.bet_serial=serial_last('bet');

    set _rule_count=json_length(new.rule_list);
    while _rule_id<_rule_count do
        set _rule_data=json_extract(new.rule_list,concat('$[',_rule_id,']'));
        set _sum_rule_amount=_sum_rule_amount+_rule_data->>'$.price'*_rule_data->>'$.quantity';
        insert into bet_rule set bet_serial=new.bet_serial,rule_id=_rule_id,
            play_key=_rule_data->>'$.play_key',number=_rule_data->>'$.number',price=_rule_data->>'$.price',
            quantity=_rule_data->>'$.quantity',rebate_rate=_rule_data->>'$.rebate_rate';
        set _rule_id=_rule_id+1;
    end while;

    set _rule_id=0;
    while _rule_id<_rule_count do
        set _rule_data=json_extract(new.rule_list,concat('$[',_rule_id,']'));
        insert into bet_unit set bet_serial=new.bet_serial,period=new.period,rule_id=_rule_id,
            bet_launch=_rule_data->>'$.price'*_rule_data->>'$.quantity'*new.multiple,result=0,
            bet=0,bonus=0,rebate=0,revert=0;
        set _rule_id=_rule_id+1;
    end while;
    set _period_bet_launch=_sum_rule_amount*new.multiple;
    insert into bet_period set bet_serial=new.bet_serial,period=new.period,multiple=new.multiple,
        bet_launch=_period_bet_launch,bet=0,bonus=0,rebate=0,revert=0,
        status=0,settle_time=0,settle_deal_serial=0;

    set _form_bet_launch=_period_bet_launch;
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select new.user_id,new.user_key,new.account_name,new.layer_id,-_form_bet_launch,0,0,'bet_normal',json_object(
            'game_key',new.game_key,'rule_list',new.rule_list,'period',new.period,'multiple',new.multiple
        );
    insert into bet_form set bet_serial=new.bet_serial,
        user_id=new.user_id,user_key=new.user_key,account_name=new.account_name,layer_id=new.layer_id,game_key=new.game_key,
        rule_count=_rule_count,period_count=1,settle_count=0,
        bet_launch=_form_bet_launch,bet=0,bonus=0,rebate=0,revert=0,
        launch_time=unix_timestamp(),launch_deal_serial=serial_last('deal');
end;;

drop trigger if exists bet_chase_insert;;
create trigger bet_chase_insert before insert on bet_chase for each row
begin
    declare _rule_id,_rule_count,_period_id,_period_count int default 0;
    declare _rule_data,_period_data json;
    declare _sum_rule_amount,_period_bet_launch,_form_bet_launch double default 0;
    insert into serial_current set serial_key='bet' on duplicate key update current=current+1;
    set new.bet_serial=serial_last('bet');

    set _rule_count=json_length(new.rule_list);
    while _rule_id<_rule_count do
        set _rule_data=json_extract(new.rule_list,concat('$[',_rule_id,']'));
        set _sum_rule_amount=_sum_rule_amount+_rule_data->>'$.price'*_rule_data->>'$.quantity';
        insert into bet_rule set bet_serial=new.bet_serial,rule_id=_rule_id,
            play_key=_rule_data->>'$.play_key',number=_rule_data->>'$.number',price=_rule_data->>'$.price',
            quantity=_rule_data->>'$.quantity',rebate_rate=_rule_data->>'$.rebate_rate';
        set _rule_id=_rule_id+1;
    end while;

    set _period_count=json_length(new.period_list);
    while _period_id<_period_count do
        set _period_data=json_extract(new.period_list,concat('$[',_period_id,']'));
        set _rule_id=0,_period_bet_launch=0;
        while _rule_id<_rule_count do
            set _rule_data=json_extract(new.rule_list,concat('$[',_rule_id,']'));
            insert into bet_unit set bet_serial=new.bet_serial,period=_period_data->>'$.period',rule_id=_rule_id,
                bet_launch=_rule_data->>'$.price'*_rule_data->>'$.quantity'*_period_data->>'$.multiple',result=0,
                bet=0,bonus=0,rebate=0,revert=0;
            set _rule_id=_rule_id+1;
        end while;
        set _period_bet_launch=_sum_rule_amount*_period_data->>'$.multiple';
        insert into bet_period set bet_serial=new.bet_serial,period=_period_data->>'$.period',multiple=_period_data->>'$.multiple',
            bet_launch=_period_bet_launch,bet=0,bonus=0,rebate=0,revert=0,
            status=0,settle_time=0,settle_deal_serial=0;
        set _form_bet_launch=_form_bet_launch+_period_bet_launch;
        set _period_id=_period_id+1;
    end while;

    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select new.user_id,new.user_key,new.account_name,new.layer_id,-_form_bet_launch,0,0,'bet_chase',json_object(
            'game_key',new.game_key,'rule_list',new.rule_list,
            'period_list',new.period_list,'chase_mode',new.chase_mode,'stop_mode',new.stop_mode
        );
    insert into bet_form set bet_serial=new.bet_serial,
        user_id=new.user_id,user_key=new.user_key,account_name=new.account_name,layer_id=new.layer_id,game_key=new.game_key,
        rule_count=_rule_count,period_count=_period_count,settle_count=0,
        bet_launch=_form_bet_launch,bet=0,bonus=0,rebate=0,revert=0,
        launch_time=unix_timestamp(),launch_deal_serial=serial_last('deal');
end;;

drop trigger if exists bet_settle_insert;;
create trigger bet_settle_insert before insert on bet_settle for each row
begin
    declare _user_id,_layer_id int unsigned;
    declare _user_key,_account_name varchar(20);
    declare _rule_idx,_rule_count int default 0;
    declare _unit_data json;
    declare _bet,_bonus,_rebate,_revert double default 0;
    select user_id,user_key,account_name,layer_id,rule_count into _user_id,_user_key,_account_name,_layer_id,_rule_count
        from bet_form where bet_serial=new.bet_serial;
    if _user_id is null then
        signal sqlstate 'BSI01' set message_text='bet_settle_insert: 找不到对应的 bet_serial';
    end if;
    while _rule_idx<_rule_count do
        set _unit_data=json_extract(new.unit_list,concat('$[',_rule_idx,']'));
        update bet_unit set result=_unit_data->>'$.result',
            bet=_unit_data->>'$.bet',bonus=_unit_data->>'$.bonus',
            rebate=_unit_data->>'$.rebate',revert=_unit_data->>'$.revert'
            where (bet_serial,period,rule_id)=(new.bet_serial,new.period,_unit_data->>'$.rule_id');
        set _rule_idx=_rule_idx+1;
        set _bet=_bet+_unit_data->>'$.bet',_bonus=_bonus+_unit_data->>'$.bonus',
            _rebate=_rebate+_unit_data->>'$.rebate',_revert=_revert+_unit_data->>'$.revert';
    end while;
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select _user_id,_user_key,_account_name,_layer_id,_bonus+_rebate+_revert,0,-_bet,'bet_settle',json_object(
            'bet_serial',new.bet_serial,'period',new.period,
            'bet',_bet,'bonus',_bonus,'rebate',_rebate,'revert',_revert
        );
    update bet_period set bet=_bet,bonus=_bonus,rebate=_rebate,revert=_revert,
        status=new.status,settle_time=unix_timestamp(),settle_deal_serial=serial_last('deal')
        where (bet_serial,period)=(new.bet_serial,period);
    update bet_form set settle_count=settle_count+1,
        bet=bet+_bet,bonus=bonus+_bonus,rebate=rebate+_rebate,revert=revert+_revert
        where bet_serial=new.bet_serial;
end;;

drop trigger if exists withdraw_launch_insert;;
create trigger withdraw_launch_insert before insert on withdraw_launch for each row
begin
    insert into serial_current set serial_key='withdraw' on duplicate key update current=current+1;
    set new.withdraw_serial=serial_last('withdraw');
    insert into deal set user_id=new.user_id,user_key=new.user_key,account_name=new.account_name,layer_id=new.layer_id,
        deal_type='withdraw_launch',vary_money=-new.launch_money,vary_deposit_audit=-new.deposit_audit,vary_coupon_audit=0,
        summary=json_object(
            'launch_money',new.launch_money,'deposit_audit',new.deposit_audit,
            'handling_fee',new.handling_fee,'withdraw_money',new.withdraw_money,
            'bank_name',new.bank_name,'bank_branch',new.bank_branch,
            'account_number',new.account_number,'account_name',new.account_name
        );
    set new.launch_deal_serial=serial_last('deal');
    set new.launch_time=unix_timestamp();
end;;

drop trigger if exists withdraw_lock_insert;;
create trigger withdraw_lock_insert before insert on withdraw_lock for each row
begin
    set new.lock_time=unix_timestamp();
end;;

drop trigger if exists withdraw_lock_delete;;
create trigger withdraw_lock_delete before delete on withdraw_lock for each row
begin
end;;

drop trigger if exists withdraw_accept_insert;;
create trigger withdraw_accept_insert before insert on withdraw_accept for each row
begin
    set new.accept_time=unix_timestamp();
end;;

drop trigger if exists withdraw_reject_insert;;
create trigger withdraw_reject_insert before insert on withdraw_reject for each row
begin
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select user_id,user_key,account_name,layer_id,launch_money,deposit_audit,0,'withdraw_reject',json_object(
            'reject_staff_id',new.reject_staff_id,'reject_reason',new.reject_reason
        ) from withdraw_launch where withdraw_serial=new.withdraw_serial;
    set new.reject_deal_serial=serial_last('deal');
    set new.reject_time=unix_timestamp();
end;;

drop trigger if exists withdraw_finish_insert;;
create trigger withdraw_finish_insert before insert on withdraw_finish for each row
begin
    set new.finish_time=unix_timestamp();
end;;

drop trigger if exists withdraw_cancel_insert;;
create trigger withdraw_cancel_insert before insert on withdraw_cancel for each row
begin
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select user_id,user_key,account_name,layer_id,launch_money,deposit_audit,0,'withdraw_reject',json_object(
            'cancel_staff_id',new.cancel_staff_id,'cancel_reason',new.cancel_reason
        ) from withdraw_launch where withdraw_serial=new.withdraw_serial;
    set new.cancel_deal_serial=serial_last('deal');
    set new.cancel_time=unix_timestamp();
end;;

drop trigger if exists deposit_launch_insert;;
create trigger deposit_launch_insert before insert on deposit_launch for each row
begin
    insert into serial_current set serial_key='deposit' on duplicate key update current=current+1;
    set new.deposit_serial=serial_last('deposit');
    set new.launch_time=unix_timestamp();
end;;

drop trigger if exists deposit_bank_insert;;
create trigger deposit_bank_insert before insert on deposit_bank for each row
begin
end;;

drop trigger if exists deposit_gateway_insert;;
create trigger deposit_gateway_insert before insert on deposit_gateway for each row
begin
end;;

drop trigger if exists deposit_simple_insert;;
create trigger deposit_simple_insert before insert on deposit_simple for each row
begin
end;;

drop trigger if exists deposit_finish_insert;;
create trigger deposit_finish_insert before insert on deposit_finish for each row
begin
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select l.user_id,l.user_key,l.account_name,l.layer_id,new.finish_money,new.deposit_audit,new.coupon_audit,'deposit_finish',json_object(
            'bank',(
                select json_object(
                    'from_type',b.from_type,'from_name',b.from_name,
                    'to_bank_name',b.to_bank_name,'to_bank_branch',b.to_bank_branch,
                    'to_account_number',b.to_account_number,'to_account_name',b.to_account_name
                ) from deposit_bank b where b.deposit_serial=l.deposit_serial
            ),
            'gateway',(
                select json_object(
                    'gate_key',g.gate_key,'gate_name',g.gate_name,'way_key',g.way_key,'way_name',g.way_name,
                    'to_account_number',g.to_account_number,'to_account_name',g.to_account_name
                ) from deposit_gateway g where g.deposit_serial=l.deposit_serial
            )
        ) from deposit_launch l where l.deposit_serial=new.deposit_serial;
    set new.finish_deal_serial=serial_last('deal');
    set new.finish_time=unix_timestamp();
end;;

drop trigger if exists deposit_cancel_insert;;
create trigger deposit_cancel_insert before insert on deposit_cancel for each row
begin
    set new.cancel_time=unix_timestamp();
end;;

drop trigger if exists staff_withdraw_insert;;
create trigger staff_withdraw_insert before insert on staff_withdraw for each row
begin
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select new.user_id,new.user_key,new.account_name,new.layer_id,-new.money,-new.deposit_audit,-new.coupon_audit,'staff_withdraw',json_object(
            'staff_id',new.staff_id,'withdraw_type',new.withdraw_type,'memo',new.memo
        );
    set new.deal_serial=serial_last('deal');
    set new.withdraw_time=unix_timestamp();
end;;

drop trigger if exists coupon_offer_insert;;
create trigger coupon_offer_insert before insert on coupon_offer for each row
begin
    set new.offer_time=unix_timestamp();
end;;

drop trigger if exists coupon_take_insert;;
create trigger coupon_take_insert before insert on coupon_take for each row
begin
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select o.user_id,o.user_key,o.account_name,o.layer_id,o.coupon_money,0,o.coupon_audit,'coupon_take',json_object(
            'coupon_type',o.coupon_type,'coupon_money',o.coupon_money,'coupon_audit',o.coupon_audit
        ) from coupon_offer o where o.coupon_id=new.coupon_id;
    set new.deal_serial=serial_last('deal');
    set new.take_time=unix_timestamp();
end;;

drop trigger if exists subsidy_deliver_insert;;
create trigger subsidy_deliver_insert before insert on subsidy_deliver for each row
begin
    insert into deal set user_id=new.user_id,user_key=new.user_key,account_name=new.account_name,layer_id=new.layer_id,
        deal_type='subsidy_deliver',vary_money=new.subsidy,vary_deposit_audit=0,vary_coupon_audit=0,
        summary=json_object('daily',new.daily);
    set new.deliver_deal_serial=serial_last('deal');
    set new.deliver_time=unix_timestamp();
end;;

drop trigger if exists deal_insert;;
create trigger deal_insert before insert on deal for each row
begin
    declare _old_money,_old_deposit_audit,_old_coupon_audit double;
    insert into serial_current set serial_key='deal' on duplicate key update current=current+1;
    set new.deal_serial=serial_last('deal');
    set new.deal_time=unix_timestamp();
    select money,deposit_audit,coupon_audit into _old_money,_old_deposit_audit,_old_coupon_audit
        from account where user_id=new.user_id for update;
    if _old_money is null then
        set new.old_money=0,new.old_deposit_audit=0,new.old_coupon_audit=0;
        set new.new_money=new.vary_money,new.new_deposit_audit=new.vary_deposit_audit,new.new_coupon_audit=new.vary_coupon_audit;
    else
        set new.old_money=_old_money,new.old_deposit_audit=_old_deposit_audit,new.old_coupon_audit=_old_coupon_audit;
        set new.new_money=new.old_money+new.vary_money;
        set new.new_deposit_audit=new.old_deposit_audit+new.vary_deposit_audit;
        set new.new_coupon_audit=new.old_coupon_audit+new.vary_coupon_audit;
        update account set user_key=new.user_key,account_name=new.account_name,layer_id=new.layer_id,
            money=new.new_money,deposit_audit=new.new_deposit_audit,coupon_audit=new.new_coupon_audit
            where user_id=new.user_id;
    end if;
    if new.new_money<0 then
        signal sqlstate 'DI001' set message_text='deal_insert: 余额不足';
    end if;
    if new.new_coupon_audit<0 then
        set new.new_deposit_audit=new.new_deposit_audit+new.new_coupon_audit;
        set new.new_coupon_audit=0;
        set new.vary_coupon_audit=new.new_coupon_audit-new.old_coupon_audit;
        set new.vary_deposit_audit=new.new_deposit_audit-new.old_deposit_audit;
    end if;
    if new.new_deposit_audit<0 then
        set new.new_deposit_audit=0;
        set new.vary_deposit_audit=new.new_deposit_audit-new.old_deposit_audit;
    end if;
    if _old_money is null then
        insert into account set user_id=new.user_id,user_key=new.user_key,account_name=new.account_name,layer_id=new.layer_id,
            money=new.new_money,deposit_audit=new.new_deposit_audit,coupon_audit=new.new_coupon_audit;
    else
        update account set user_key=new.user_key,account_name=new.account_name,layer_id=new.layer_id,
            money=new.new_money,deposit_audit=new.new_deposit_audit,coupon_audit=new.new_coupon_audit
            where user_id=new.user_id;
    end if;
end;;

drop trigger if exists serial_current_insert;;
create trigger serial_current_insert before insert on serial_current for each row
begin
    declare _increment,_offset,_digit int;
    declare _cycle,_prefix bigint unsigned;
    select increment,offset,digit into _increment,_offset,_digit
        from serial_setting where serial_key=new.serial_key;
    set _cycle=cast(rpad('1',_digit+1,'0') as unsigned);
    set _prefix=cast(date_format(current_timestamp,'%y%m%d%H%i%s') as unsigned);
    set new.current=_prefix*_cycle+_offset;
    if @serial_last is null then
        set @serial_last=json_object(new.serial_key,new.current);
    else
        set @serial_last=json_set(@serial_last,concat('$.',new.serial_key),new.current);
    end if;
end;;

drop trigger if exists serial_current_update;;
create trigger serial_current_update before update on serial_current for each row
begin
    declare _increment,_offset,_digit int;
    declare _cycle,_prefix,_tail bigint unsigned;
    select increment,offset,digit into _increment,_offset,_digit
        from serial_setting where serial_key=new.serial_key;
    set _prefix=cast(date_format(current_timestamp,'%y%m%d%H%i%s') as unsigned);
    set _cycle=cast(rpad('1',_digit+1,'0') as unsigned);
    set _tail=(old.current mod _cycle)+_increment;
    if _tail>_cycle then set _tail=_offset; end if;
    set new.current=_prefix*_cycle+_tail;
    if @serial_last is null then
        set @serial_last=json_object(new.serial_key,new.current);
    else
        set @serial_last=json_set(@serial_last,concat('$.',new.serial_key),new.current);
    end if;
end;;

drop trigger if exists brokerage_deliver_insert;;
create trigger brokerage_deliver_insert before insert on brokerage_deliver for each row
begin
    insert into deal set user_id=new.user_id,user_key=new.user_key,account_name=new.account_name,layer_id=new.layer_id,
        deal_type='brokerage_deliver',vary_money=new.brokerage,vary_deposit_audit=0,vary_coupon_audit=0,
        summary=json_object('daily',new.daily);
    set new.deliver_deal_serial=serial_last('deal');
    set new.deliver_time=unix_timestamp();
end;;

drop view if exists external_import_fungaming_intact;;
create view external_import_fungaming_intact as
	select l.import_serial,l.user_id,l.user_key,l.layer_id,l.external_type,l.launch_data,
        l.launch_money,l.launch_time,
		s.success_deal_serial,s.success_time,s.success_data,
		f.failure_time,f.failure_data
	from external_import_launch l
		left join external_import_success s on l.import_serial=s.import_serial
		left join external_import_failure f on l.import_serial=f.import_serial;;

drop view if exists staff_deposit_intact;;
create view staff_deposit_intact as
    select s.deal_serial,s.staff_id,s.staff_name,s.deposit_type,
        s.user_id,s.user_key,s.account_name,s.layer_id,s.money,s.deposit_audit,s.coupon_audit,s.memo,s.deposit_time,
        d.old_money,d.old_deposit_audit,d.old_coupon_audit,d.new_money,d.new_deposit_audit,d.new_coupon_audit,d.deal_time
    from staff_deposit s inner join deal d on s.deal_serial=d.deal_serial;;

drop view if exists external_export_fungaming_intact;;
create view external_export_fungaming_intact as
	select l.export_serial,l.user_id,l.user_key,l.layer_id,l.external_type,l.launch_data,
        l.launch_money,l.launch_deal_serial,l.launch_time,
		s.success_time,s.success_data,
		f.failure_deal_serial,f.failure_time,f.failure_data
	from external_export_launch l
		left join external_export_success s on l.export_serial=s.export_serial
		left join external_export_failure f on l.export_serial=f.export_serial;;

drop view if exists bet_unit_intact;;
create view bet_unit_intact as
    select f.bet_serial,f.user_id,f.user_key,f.layer_id,f.game_key,f.launch_time,
        p.period,p.multiple,p.status,p.settle_time,
        r.rule_id,r.play_key,r.number,r.price,r.quantity,r.rebate_rate,
        u.bet_launch,u.result,u.bet,u.bonus,u.rebate,u.revert,
        c.period_list,c.chase_mode,c.stop_mode
    from bet_form f
        inner join bet_period p on f.bet_serial=p.bet_serial
        inner join bet_rule r on f.bet_serial=r.bet_serial
        inner join bet_unit u on (f.bet_serial,p.period,r.rule_id)=(u.bet_serial,u.period,u.rule_id)
        left join bet_chase c on f.bet_serial=c.bet_serial;;

drop view if exists bet_normal_all;;
create view bet_normal_all as
    select f.bet_serial,f.user_id,f.user_key,f.layer_id,f.game_key,f.rule_count,f.period_count,f.settle_count,f.launch_time,
        p.period,p.multiple,p.bet_launch,p.bet,p.bonus,p.rebate,p.revert,p.status,p.settle_time,
        json_arrayagg(json_object(
            'rule_id',r.rule_id,'play_key',r.play_key,'number',r.number,
            'price',r.price,'quantity',r.quantity,'rebate_rate',r.rebate_rate,
            'bet_launch',u.bet_launch,'result',u.result,'bet',u.bet,
            'bonus',u.bonus,'rebate',u.rebate,'revert',u.revert
        )) unit_list
    from bet_normal n inner join bet_form f on n.bet_serial=f.bet_serial
        inner join bet_period p on f.bet_serial=p.bet_serial
        inner join bet_rule r on f.bet_serial=r.bet_serial
        inner join bet_unit u on (f.bet_serial,p.period,r.rule_id)=(u.bet_serial,u.period,u.rule_id)
    group by f.bet_serial,p.period;;

drop view if exists bet_normal_wait;;
create view bet_normal_wait as
    select f.bet_serial,f.user_id,f.user_key,f.layer_id,f.game_key,f.rule_count,f.period_count,f.settle_count,f.launch_time,
        p.period,p.multiple,p.bet_launch,p.bet,p.bonus,p.rebate,p.revert,p.status,p.settle_time,
        json_arrayagg(json_object(
            'rule_id',r.rule_id,'play_key',r.play_key,'number',r.number,
            'price',r.price,'quantity',r.quantity,'rebate_rate',r.rebate_rate,
            'bet_launch',u.bet_launch,'result',u.result,'bet',u.bet,
            'bonus',u.bonus,'rebate',u.rebate,'revert',u.revert
        )) unit_list
    from bet_normal n inner join bet_form f on n.bet_serial=f.bet_serial
        inner join bet_period p on f.bet_serial=p.bet_serial
        inner join bet_rule r on f.bet_serial=r.bet_serial
        inner join bet_unit u on (f.bet_serial,p.period,r.rule_id)=(u.bet_serial,u.period,u.rule_id)
    where p.status=0
    group by f.bet_serial,p.period;;

drop view if exists bet_normal_win;;
create view bet_normal_win as
    select f.bet_serial,f.user_id,f.user_key,f.layer_id,f.game_key,f.rule_count,f.period_count,f.settle_count,f.launch_time,
        p.period,p.multiple,p.bet_launch,p.bet,p.bonus,p.rebate,p.revert,p.status,p.settle_time,
        json_arrayagg(json_object(
            'rule_id',r.rule_id,'play_key',r.play_key,'number',r.number,
            'price',r.price,'quantity',r.quantity,'rebate_rate',r.rebate_rate,
            'bet_launch',u.bet_launch,'result',u.result,'bet',u.bet,
            'bonus',u.bonus,'rebate',u.rebate,'revert',u.revert
        )) unit_list
    from bet_normal n inner join bet_form f on n.bet_serial=f.bet_serial
        inner join bet_period p on f.bet_serial=p.bet_serial
        inner join bet_rule r on f.bet_serial=r.bet_serial
        inner join bet_unit u on (f.bet_serial,p.period,r.rule_id)=(u.bet_serial,u.period,u.rule_id)
    where p.status=1 and p.bonus>0
    group by f.bet_serial,p.period;;

drop view if exists bet_normal_lose;;
create view bet_normal_lose as
    select f.bet_serial,f.user_id,f.user_key,f.layer_id,f.game_key,f.rule_count,f.period_count,f.settle_count,f.launch_time,
        p.period,p.multiple,p.bet_launch,p.bet,p.bonus,p.rebate,p.revert,p.status,p.settle_time,
        json_arrayagg(json_object(
            'rule_id',r.rule_id,'play_key',r.play_key,'number',r.number,
            'price',r.price,'quantity',r.quantity,'rebate_rate',r.rebate_rate,
            'bet_launch',u.bet_launch,'result',u.result,'bet',u.bet,
            'bonus',u.bonus,'rebate',u.rebate,'revert',u.revert
        )) unit_list
    from bet_normal n inner join bet_form f on n.bet_serial=f.bet_serial
        inner join bet_period p on f.bet_serial=p.bet_serial
        inner join bet_rule r on f.bet_serial=r.bet_serial
        inner join bet_unit u on (f.bet_serial,p.period,r.rule_id)=(u.bet_serial,u.period,u.rule_id)
    where p.status=1 and p.bonus=0
    group by f.bet_serial,p.period;;

drop view if exists bet_chase_period;;
create view bet_chase_period as
    select f.bet_serial,f.user_id,f.user_key,f.layer_id,f.game_key,f.rule_count,f.period_count,f.settle_count,f.launch_time,
        f.bet_launch,f.bet,f.bonus,f.rebate,f.revert,
        json_object(
            'period',p.period,'multiple',p.multiple,'bet_launch',p.bet_launch,
            'bet',p.bet,'bonus',p.bonus,'rebate',p.rebate,'revert',p.revert,
            'status',p.status,'settle_time',p.settle_time,
            'unit_list',json_arrayagg(json_object(
                'rule_id',r.rule_id,'play_key',r.play_key,'number',r.number,
                'price',r.price,'quantity',r.quantity,'rebate_rate',r.rebate_rate,
                'bet_launch',u.bet_launch,'result',u.result,'bet',u.bet,
                'bonus',u.bonus,'rebate',u.rebate,'revert',u.revert
            ))
        ) period_data
    from bet_chase c inner join bet_form f on c.bet_serial=f.bet_serial
        inner join bet_period p on f.bet_serial=p.bet_serial
        inner join bet_rule r on f.bet_serial=r.bet_serial
        inner join bet_unit u on (f.bet_serial,p.period,r.rule_id)=(u.bet_serial,u.period,u.rule_id)
    group by f.bet_serial,p.period;;

drop view if exists bet_chase_all;;
create view bet_chase_all as
    select bet_serial,user_id,user_key,layer_id,game_key,rule_count,period_count,settle_count,launch_time,
        json_arrayagg(period_data) period_list
    from bet_chase_period
    group by bet_serial;;

drop view if exists bet_chase_run;;
create view bet_chase_run as
    select bet_serial,user_id,user_key,layer_id,game_key,rule_count,period_count,settle_count,launch_time,
        json_arrayagg(period_data) period_list
    from bet_chase_period
    where period_count>settle_count
    group by bet_serial;;

drop view if exists bet_chase_end;;
create view bet_chase_end as
    select bet_serial,user_id,user_key,layer_id,game_key,rule_count,period_count,settle_count,launch_time,
        json_arrayagg(period_data) period_list
    from bet_chase_period
    where period_count=settle_count
    group by bet_serial;;

drop view if exists bet_no_settle;;
create view bet_no_settle as
    select f.bet_serial,f.game_key,p.period,p.multiple,c.period_list,c.stop_mode,
        json_arrayagg(json_object(
            'rule_id',r.rule_id,'play_key',r.play_key,'number',r.number,
            'price',r.price,'quantity',r.quantity,'rebate_rate',r.rebate_rate,
            'bet_launch',u.bet_launch
        )) as rule_list
    from bet_form f
        inner join bet_period p on f.bet_serial=p.bet_serial
        inner join bet_rule r on f.bet_serial=r.bet_serial
        inner join bet_unit u on (f.bet_serial,p.period,r.rule_id)=(u.bet_serial,u.period,u.rule_id)
        left join bet_settle s on (f.bet_serial,p.period)=(s.bet_serial,s.period)
        left join bet_chase c on f.bet_serial=c.bet_serial
    where s.bet_serial is null
    group by f.bet_serial,p.period;;

drop view if exists withdraw_intact;;
create view withdraw_intact as
    select l.withdraw_serial,l.user_id,l.user_key,l.layer_id,
        l.launch_money,l.deposit_audit,l.handling_fee,l.withdraw_money,
        l.bank_name,l.bank_branch,l.account_number,l.account_name,
        l.launch_time,l.launch_device,l.must_inspect,l.launch_deal_serial,
        ld.old_money as launch_old_money,ld.old_deposit_audit as launch_old_deposit_audit,ld.old_coupon_audit as launch_old_coupon_audit,
        ld.new_money as launch_new_money,ld.new_deposit_audit as launch_new_deposit_audit,ld.new_coupon_audit as launch_new_coupon_audit,
        a.accept_staff_id,a.accept_staff_name,a.accept_time,
        r.reject_staff_id,r.reject_staff_name,r.reject_time,r.reject_reason,r.reject_deal_serial,
        f.finish_staff_id,f.finish_staff_name,f.finish_time,
        c.cancel_staff_id,c.cancel_staff_name,c.cancel_time,c.cancel_reason,c.cancel_deal_serial,
        k.lock_type,k.lock_staff_id,k.lock_staff_name,k.lock_time
    from withdraw_launch l inner join deal ld on l.launch_deal_serial=ld.deal_serial
        left join withdraw_accept a on l.withdraw_serial=a.withdraw_serial
        left join withdraw_reject r on l.withdraw_serial=r.withdraw_serial
        left join withdraw_finish f on l.withdraw_serial=f.withdraw_serial
        left join withdraw_cancel c on l.withdraw_serial=c.withdraw_serial
        left join withdraw_lock k on l.withdraw_serial=k.withdraw_serial;;

drop view if exists deposit_intact;;
create view deposit_intact as
    select l.deposit_serial,l.user_id,l.user_key,l.account_name,l.layer_id,
        l.passage_id,l.passage_name,l.route_id,l.launch_money,l.launch_time,l.launch_device,
        f.finish_money,f.coupon_audit,f.finish_time,f.finish_staff_id,f.finish_staff_name,f.finish_deal_serial,
        d.vary_money,d.vary_deposit_audit,d.vary_coupon_audit,
        d.old_money,d.old_deposit_audit,d.old_coupon_audit,
        d.new_money,d.new_deposit_audit,d.new_coupon_audit,
        d.user_id as deal_user_id,d.deal_type,d.summary,d.deal_time,
        c.cancel_time,c.cancel_staff_id,c.cancel_staff_name,c.cancel_reason
    from deposit_launch l
        left join deposit_finish f on l.deposit_serial=f.deposit_serial
        left join deal d on f.finish_deal_serial=d.deal_serial
        left join deposit_cancel c on l.deposit_serial=c.deposit_serial;;

drop view if exists deposit_bank_intact;;
create view deposit_bank_intact as
    select l.deposit_serial,l.user_id,l.user_key,l.account_name,l.layer_id,
        l.passage_id,l.passage_name,l.route_id,l.launch_money,l.launch_time,l.launch_device,
        b.from_type,b.from_name,
        b.to_bank_name,b.to_bank_branch,b.to_account_number,b.to_account_name,
        f.finish_money,f.coupon_audit,f.finish_time,f.finish_staff_id,f.finish_staff_name,f.finish_deal_serial,
        d.vary_money,d.vary_deposit_audit,d.vary_coupon_audit,
        d.old_money,d.old_deposit_audit,d.old_coupon_audit,
        d.new_money,d.new_deposit_audit,d.new_coupon_audit,
        d.user_id as deal_user_id,d.deal_type,d.summary,d.deal_time,
        c.cancel_time,c.cancel_staff_id,c.cancel_staff_name,c.cancel_reason
    from deposit_launch l inner join deposit_bank b on l.deposit_serial=b.deposit_serial
        left join deposit_finish f on l.deposit_serial=f.deposit_serial
        left join deal d on f.finish_deal_serial=d.deal_serial
        left join deposit_cancel c on l.deposit_serial=c.deposit_serial;;

drop view if exists deposit_gateway_intact;;
create view deposit_gateway_intact as
    select l.deposit_serial,l.user_id,l.user_key,l.account_name,l.layer_id,
        l.passage_id,l.passage_name,l.route_id,l.launch_money,l.launch_time,l.launch_device,
        g.gate_key,g.gate_name,g.way_key,g.way_name,g.to_account_number,g.to_account_name,
        f.finish_money,f.coupon_audit,f.finish_time,f.finish_staff_id,f.finish_staff_name,f.finish_deal_serial,
        d.vary_money,d.vary_deposit_audit,d.vary_coupon_audit,
        d.old_money,d.old_deposit_audit,d.old_coupon_audit,
        d.new_money,d.new_deposit_audit,d.new_coupon_audit,
        d.user_id as deal_user_id,d.deal_type,d.summary,d.deal_time,
        c.cancel_time,c.cancel_staff_id,c.cancel_staff_name,c.cancel_reason
    from deposit_launch l inner join deposit_gateway g on l.deposit_serial=g.deposit_serial
        left join deposit_finish f on l.deposit_serial=f.deposit_serial
        left join deal d on f.finish_deal_serial=d.deal_serial
        left join deposit_cancel c on l.deposit_serial=c.deposit_serial;;

drop view if exists deposit_simple_intact;;
create view deposit_simple_intact as
    select l.deposit_serial,l.user_id,l.user_key,l.account_name,l.layer_id,
        l.passage_id,l.passage_name,l.route_id,l.launch_money,l.launch_time,l.launch_device,
        s.pay_url,s.memo,
        f.finish_money,f.coupon_audit,f.finish_time,f.finish_staff_id,f.finish_staff_name,f.finish_deal_serial,
        d.vary_money,d.vary_deposit_audit,d.vary_coupon_audit,
        d.old_money,d.old_deposit_audit,d.old_coupon_audit,
        d.new_money,d.new_deposit_audit,d.new_coupon_audit,
        d.user_id as deal_user_id,d.deal_type,d.summary,d.deal_time,
        c.cancel_time,c.cancel_staff_id,c.cancel_staff_name,c.cancel_reason
    from deposit_launch l inner join deposit_simple s on l.deposit_serial=s.deposit_serial
        left join deposit_finish f on l.deposit_serial=f.deposit_serial
        left join deal d on f.finish_deal_serial=d.deal_serial
        left join deposit_cancel c on l.deposit_serial=c.deposit_serial;;

drop view if exists staff_withdraw_intact;;
create view staff_withdraw_intact as
    select s.deal_serial,s.staff_id,s.staff_name,s.withdraw_type,
        s.user_id,s.user_key,s.account_name,s.layer_id,s.money,s.deposit_audit,s.coupon_audit,s.memo,s.withdraw_time,
        d.old_money,d.old_deposit_audit,d.old_coupon_audit,d.new_money,d.new_deposit_audit,d.new_coupon_audit,d.deal_time
    from staff_withdraw s inner join deal d on s.deal_serial=d.deal_serial;;

drop view if exists coupon_intact;;
create view coupon_intact as
    select o.coupon_id,o.coupon_type,o.user_id,o.user_key,o.account_name,o.layer_id,o.coupon_money,o.coupon_audit,o.offer_time,
        t.deal_serial,t.take_time
    from coupon_offer o left join coupon_take t on o.coupon_id=t.coupon_id;;

insert into serial_setting(serial_key,increment,offset,digit)values
('deal',37,regexp_replace(schema(),'[^0-9]',''),7),
('deposit',37,regexp_replace(schema(),'[^0-9]',''),6),
('withdraw',37,regexp_replace(schema(),'[^0-9]',''),6),
('bet',37,regexp_replace(schema(),'[^0-9]',''),6),
('transfer',37,regexp_replace(schema(),'[^0-9]',''),6),
('external_import',37,regexp_replace(schema(),'[^0-9]',''),6),
('external_export',37,regexp_replace(schema(),'[^0-9]',''),6),
('external_audit',37,regexp_replace(schema(),'[^0-9]',''),6);;

