delimiter ;;

drop function if exists serial_last;;
create function serial_last(
    _serial_key varchar(20)
) returns bigint no sql comment '上一个流水号'
begin
    return json_extract(@serial_last,concat('$.',_serial_key));
end;;

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
    bet_launch decimal(14,2) not null comment '总投注金额',
    bet decimal(14,2) not null comment '总有效投注',
    bonus decimal(14,2) not null comment '总派奖',
    rebate decimal(14,2) not null comment '总返点',
    revert decimal(14,2) not null comment '总退款',
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
    price decimal(14,2) not null comment '每注金额',
    quantity int not null comment '注数',
    rebate_rate decimal(4,2) not null comment '返点比例',
    amount decimal(14,2) as (price*quantity) comment '金额',
    primary key(bet_serial,rule_id)
) comment '投注内容';;

drop table if exists bet_period;;
create table bet_period(
    bet_serial bigint unsigned not null comment '投注单号',
    period varchar(20) not null comment '期号',
    multiple int unsigned not null comment '倍数',
    bet_launch decimal(14,2) not null comment '当期投注金额',
    bet decimal(14,2) not null comment '当期有效投注',
    bonus decimal(14,2) not null comment '当期派奖',
    rebate decimal(14,2) not null comment '当期返点',
    revert decimal(14,2) not null comment '当期退款',
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
    bet_launch decimal(14,2) not null comment '投注',
    result tinyint not null comment '0:未开奖，1:未中奖，2:已中奖，3:和',
    bet decimal(14,2) not null comment '有效投注',
    bonus decimal(14,2) not null comment '派奖',
    rebate decimal(14,2) not null comment '返点',
    revert decimal(14,2) not null comment '退款',
    primary key(bet_serial,period,rule_id)
) comment '投注结果';;

drop table if exists account;;
create table account(
    user_id int not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    money decimal(14,2) not null comment '账户余额',
    deposit_audit decimal(14,2) not null comment '入款稽核',
    coupon_audit decimal(14,2) not null comment '活动稽核',
    primary key(user_id)
) comment '用户账户';;

drop table if exists guest_user;;
create table guest_user(
    user_id int unsigned not null auto_increment comment '用户id',
    user_key varchar(20) not null default '' comment '用户名',
    account_name varchar(20) not null default '' comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    register_time int unsigned not null comment '注册时间',
    register_ip int unsigned not null comment '注册ip',
    index(register_time),
    primary key(user_id)
) comment '试玩帐号';;

drop table if exists guest_session;;
create table guest_session(
    client_id varchar(32) not null comment '客户端连接id',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '用户名',
    login_time int unsigned not null comment '登陆时间',
    client_ip int unsigned not null comment '客户端ip',
    user_agent varchar(40) not null comment 'sha1(user-agent)',
    resume_key varchar(40) not null comment '恢复key',
    lose_time int unsigned not null default 0 comment '掉线时间',
    index(user_id),
    index(resume_key),
    primary key(client_id)
) comment '试玩登陆session';;

drop table if exists deal;;
create table deal(
    deal_serial bigint unsigned not null comment '交易流水号',
    user_id int unsigned not null comment '用户id',
    user_key varchar(20) not null comment '登陆名',
    account_name varchar(20) not null comment '真实姓名',
    layer_id int unsigned not null comment '代理/会员层级id',
    vary_money decimal(14,2) not null comment '账户余额变动',
    vary_deposit_audit decimal(14,2) not null comment '入款稽核变动',
    vary_coupon_audit decimal(14,2) not null comment '活动稽核变动',
    old_money decimal(14,2) not null comment '旧账户余额',
    old_deposit_audit decimal(14,2) not null comment '旧入款稽核',
    old_coupon_audit decimal(14,2) not null comment '旧活动稽核',
    new_money decimal(14,2) not null comment '新账户余额',
    new_deposit_audit decimal(14,2) not null comment '新入款稽核',
    new_coupon_audit decimal(14,2) not null comment '新活动稽核',
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

drop procedure if exists guest_session_lose;;
create procedure guest_session_lose(
    _client_id varchar(32)
)
begin
    update guest_session set lose_time=unix_timestamp() where client_id=_client_id;
end;;

drop trigger if exists bet_normal_insert;;
create trigger bet_normal_insert before insert on bet_normal for each row
begin
    declare _rule_id,_rule_count int default 0;
    declare _rule_data json;
    declare _sum_rule_amount,_period_bet_launch,_form_bet_launch decimal(14,2) default 0;
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
    declare _sum_rule_amount,_period_bet_launch,_form_bet_launch decimal(14,2) default 0;
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
    declare _bet,_bonus,_rebate,_revert decimal(14,2) default 0;
    declare _settle_deal_serial bigint;
    select user_id,user_key,account_name,layer_id,rule_count into _user_id,_user_key,_account_name,_layer_id,_rule_count
        from bet_form where bet_serial=new.bet_serial;
    if _user_id is null then
        signal sqlstate 'BSI01' set message_text='bet_settle_insert: 找不到对应的 bet_serial';
    end if;
    select settle_deal_serial into _settle_deal_serial
        from bet_period where (bet_serial,period,settle_deal_serial)=(new.bet_serial,new.period,0);
    if 0 != _settle_deal_serial then
        signal sqlstate 'BSI01' set message_text='bet_settle_insert: 重复结算';
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
        where (bet_serial,period)=(new.bet_serial,new.period);
    update bet_form set settle_count=settle_count+1,
        bet=bet+_bet,bonus=bonus+_bonus,rebate=rebate+_rebate,revert=revert+_revert
        where bet_serial=new.bet_serial;
end;;

drop trigger if exists guest_user_before_delete;;
create trigger guest_user_before_delete before delete on guest_user for each row
begin
    if exists (select client_id from guest_session where user_id=old.user_id) then
        signal sqlstate 'GUBD1' set message_text='guest_user_before_delete: 不能删除在线账户';
    end if;
end;;

drop trigger if exists guest_user_delete;;
create trigger guest_user_delete after delete on guest_user for each row
begin
    delete from account where user_id=old.user_id;
    delete from deal where user_id=old.user_id;
    delete from bet_normal where user_id=old.user_id;
    delete from bet_chase where user_id=old.user_id;
    delete f,r,p,u,s from bet_form f
        inner join bet_rule r on f.bet_serial=r.bet_serial
        inner join bet_period p on f.bet_serial=p.bet_serial
        inner join bet_unit u on f.bet_serial=u.bet_serial
        left join bet_settle s on f.bet_serial=s.bet_serial
        where f.user_id=old.user_id;
end;;

drop trigger if exists guest_session_insert;;
create trigger guest_session_insert before insert on guest_session for each row
begin
    if new.user_id is null or new.user_id<0 then
        insert into guest_user set layer_id=0,register_time=unix_timestamp(),register_ip=new.client_ip;
        set new.user_id=last_insert_id();
        set new.user_key=concat('guest',new.user_id*629137%1000000);
        update guest_user set user_key=new.user_key,account_name=replace(new.user_key,'guest','试玩')
            where user_id=new.user_id;
    end if;
    set new.resume_key=sha1(random_bytes(40));
    set new.login_time=unix_timestamp();
end;;

drop trigger if exists deal_insert;;
create trigger deal_insert before insert on deal for each row
begin
    declare _old_money,_old_deposit_audit,_old_coupon_audit decimal(14,2);
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

insert into serial_setting(serial_key,increment,offset,digit)values
('deal',37,regexp_replace(schema(),'[^0-9]',''),7),
('deposit',37,regexp_replace(schema(),'[^0-9]',''),6),
('withdraw',37,regexp_replace(schema(),'[^0-9]',''),6),
('bet',37,regexp_replace(schema(),'[^0-9]',''),6),
('transfer',37,regexp_replace(schema(),'[^0-9]',''),6),
('external_import',37,regexp_replace(schema(),'[^0-9]',''),6),
('external_export',37,regexp_replace(schema(),'[^0-9]',''),6),
('external_audit',37,regexp_replace(schema(),'[^0-9]',''),6);;

