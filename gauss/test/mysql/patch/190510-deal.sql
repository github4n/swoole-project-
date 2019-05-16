delimiter ;;

drop trigger if exists deposit_bank_insert;;
drop trigger if exists deposit_gateway_insert;;
drop trigger if exists deposit_simple_insert;;
drop trigger if exists withdraw_lock_delete;;


alter table external_import_launch
    modify launch_money decimal(14,2) not null comment '转入金额';;

alter table staff_deposit
    modify money decimal(14,2) not null comment '入款金额',
    modify deposit_audit decimal(14,2) not null comment '入款稽核',
    modify coupon_audit decimal(14,2) not null comment '活动稽核';;

alter table external_audit
    modify audit_amount decimal(14,2) not null comment '打码金额';;

alter table external_export_launch
    modify launch_money decimal(14,2) not null comment '转出金额';;

alter table bet_form
    modify bet_launch decimal(14,2) not null comment '总投注金额',
    modify bet decimal(14,2) not null comment '总有效投注',
    modify bonus decimal(14,2) not null comment '总派奖',
    modify rebate decimal(14,2) not null comment '总返点',
    modify revert decimal(14,2) not null comment '总退款';;

alter table bet_rule
    modify price decimal(14,2) not null comment '每注金额',
    modify quantity int not null comment '注数',
    modify rebate_rate decimal(4,2) not null comment '返点比例',
    modify amount decimal(14,2) as (price*quantity) comment '金额';;

alter table bet_period
    modify bet_launch decimal(14,2) not null comment '当期投注金额',
    modify bet decimal(14,2) not null comment '当期有效投注',
    modify bonus decimal(14,2) not null comment '当期派奖',
    modify rebate decimal(14,2) not null comment '当期返点',
    modify revert decimal(14,2) not null comment '当期退款';;

alter table bet_unit
    modify bet_launch decimal(14,2) not null comment '投注',
    modify bet decimal(14,2) not null comment '有效投注',
    modify bonus decimal(14,2) not null comment '派奖',
    modify rebate decimal(14,2) not null comment '返点',
    modify revert decimal(14,2) not null comment '退款';;

alter table withdraw_launch
    modify launch_money decimal(14,2) not null comment '提款金额',
    modify deposit_audit decimal(14,2) not null comment '稽核金额',
    modify handling_fee decimal(14,2) not null comment '手续费',
    modify withdraw_money decimal(14,2) not null comment '出款金额';;

alter table deposit_launch
    modify launch_money decimal(14,2) not null comment '提交金额';;

alter table deposit_finish
    modify finish_money decimal(14,2) not null comment '实际存入金额',
    modify deposit_audit decimal(14,2) not null comment '充值稽核',
    modify coupon_audit decimal(14,2) not null comment '优惠稽核';;

alter table account
    modify money decimal(14,2) not null comment '账户余额',
    modify deposit_audit decimal(14,2) not null comment '入款稽核',
    modify coupon_audit decimal(14,2) not null comment '活动稽核';;

alter table staff_withdraw
    modify money decimal(14,2) not null comment '出款金额',
    modify deposit_audit decimal(14,2) not null comment '入款稽核',
    modify coupon_audit decimal(14,2) not null comment '活动稽核';;

alter table coupon_offer
    modify coupon_money decimal(14,2) not null comment '奖励金额',
    modify coupon_audit decimal(14,2) not null comment '活动稽核';;

alter table subsidy_deliver
    modify subsidy decimal(14,2) not null comment '反水金额';;

alter table deal
    modify vary_money decimal(14,2) not null comment '账户余额变动',
    modify vary_deposit_audit decimal(14,2) not null comment '入款稽核变动',
    modify vary_coupon_audit decimal(14,2) not null comment '活动稽核变动',
    modify old_money decimal(14,2) not null comment '旧账户余额',
    modify old_deposit_audit decimal(14,2) not null comment '旧入款稽核',
    modify old_coupon_audit decimal(14,2) not null comment '旧活动稽核',
    modify new_money decimal(14,2) not null comment '新账户余额',
    modify new_deposit_audit decimal(14,2) not null comment '新入款稽核',
    modify new_coupon_audit decimal(14,2) not null comment '新活动稽核';;

alter table brokerage_deliver
    modify brokerage decimal(14,2) not null comment '佣金';;


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

replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select '%',schema(),concat('master_',schema()),table_name,user(),'select,insert,update,delete'
    from information_schema.tables
    where table_schema=schema() and table_name not like '\_%';;
replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select r.to_host,p.db,r.to_user,p.table_name,p.grantor,p.table_priv
    from mysql.tables_priv p inner join mysql.role_edges r on p.host=r.from_host and p.user=r.from_user;;
flush privileges;;
