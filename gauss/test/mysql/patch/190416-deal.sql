delimiter ;;

drop trigger if exists bet_settle_insert;;
create trigger bet_settle_insert before insert on bet_settle for each row
begin
    declare _user_id,_layer_id int unsigned;
    declare _user_key,_account_name varchar(20);
    declare _rule_idx,_rule_count int default 0;
    declare _unit_data json;
    declare _bet,_bonus,_rebate,_revert double default 0;
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
