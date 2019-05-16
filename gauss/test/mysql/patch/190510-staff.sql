delimiter ;;

drop trigger if exists staff_session_delete;;
drop trigger if exists dividend_setting_insert;;
drop trigger if exists dividend_setting_delete;;
drop trigger if exists dividend_settle_insert;;
drop trigger if exists dividend_settle_delete;;
drop trigger if exists lottery_game_insert;;
drop trigger if exists lottery_game_update;;
drop trigger if exists lottery_game_delete;;
drop trigger if exists lottery_game_play_insert;;
drop trigger if exists lottery_game_play_update;;
drop trigger if exists lottery_game_play_delete;;
drop trigger if exists lottery_game_win_insert;;
drop trigger if exists lottery_game_win_update;;
drop trigger if exists lottery_game_win_delete;;
drop trigger if exists staff_permit_insert;;
drop trigger if exists staff_permit_delete;;
drop trigger if exists staff_layer_insert;;
drop trigger if exists staff_layer_delete;;
drop trigger if exists suggest_insert;;
drop trigger if exists suggest_update;;
drop trigger if exists suggest_delete;;
drop trigger if exists carousel_insert;;
drop trigger if exists carousel_update;;
drop trigger if exists carousel_delete;;
drop trigger if exists announcement_insert;;
drop trigger if exists announcement_update;;
drop trigger if exists announcement_delete;;
drop trigger if exists promotion_insert;;
drop trigger if exists promotion_update;;
drop trigger if exists promotion_delete;;
drop trigger if exists popup_insert;;
drop trigger if exists popup_update;;
drop trigger if exists popup_delete;;
drop trigger if exists deposit_route_insert;;
drop trigger if exists deposit_route_update;;
drop trigger if exists deposit_route_delete;;
drop trigger if exists deposit_route_layer_insert;;
drop trigger if exists deposit_route_layer_delete;;
drop trigger if exists deposit_route_way_insert;;
drop trigger if exists deposit_route_way_delete;;
drop trigger if exists deposit_passage_insert;;
drop trigger if exists deposit_passage_update;;
drop trigger if exists deposit_passage_delete;;
drop trigger if exists deposit_passage_bank_insert;;
drop trigger if exists deposit_passage_bank_update;;
drop trigger if exists deposit_passage_bank_delete;;
drop trigger if exists deposit_passage_gate_insert;;
drop trigger if exists deposit_passage_gate_update;;
drop trigger if exists deposit_passage_gate_delete;;
drop trigger if exists deposit_passage_simple_insert;;
drop trigger if exists deposit_passage_simple_update;;
drop trigger if exists deposit_passage_simple_delete;;
drop trigger if exists staff_auth_delete;;
drop trigger if exists site_setting_insert;;
drop trigger if exists site_setting_update;;
drop trigger if exists site_setting_delete;;
drop trigger if exists external_game_insert;;
drop trigger if exists external_game_update;;
drop trigger if exists external_game_delete;;
drop trigger if exists staff_bind_ip_insert;;
drop trigger if exists staff_bind_ip_delete;;


alter table staff_deposit
    modify finish_money decimal(14,2) not null comment '完成总金额';;

alter table dividend_setting
    modify grade1_bet_rate decimal(6,4) not null comment '大股东投注分红比例',
    modify grade1_profit_rate decimal(6,4) not null comment '大股东损益分红比例',
    modify grade1_fee_rate decimal(6,4) not null comment '大股东行政费比例',
    modify grade1_tax_rate decimal(6,4) not null comment '大股东平台费比例',
    modify grade2_bet_rate decimal(6,4) not null comment '股东投注分红比例',
    modify grade2_profit_rate decimal(6,4) not null comment '股东损益分红比例',
    modify grade2_fee_rate decimal(6,4) not null comment '股东行政费比例',
    modify grade2_tax_rate decimal(6,4) not null comment '股东平台费比例',
    modify grade3_bet_rate decimal(6,4) not null comment '总代理投注分红比例',
    modify grade3_profit_rate decimal(6,4) not null comment '总代理损益分红比例',
    modify grade3_fee_rate decimal(6,4) not null comment '总代理行政费比例',
    modify grade3_tax_rate decimal(6,4) not null comment '总代理平台费比例';;

alter table dividend_settle
    modify bet_amount decimal(14,2) not null comment '投注总额',
    modify profit_amount decimal(14,2) not null comment '损益总额',
    modify bet_rate decimal(6,4) not null comment '投注分红比例',
    modify profit_rate decimal(6,4) not null comment '损益分红比例',
    modify fee_rate decimal(6,4) not null comment '行政费比例',
    modify tax_rate decimal(6,4) not null comment '平台费比例',
    modify dividend_bet decimal(14,2) not null comment '投注分红',
    modify dividend_profit decimal(14,2) not null comment '损益分红',
    modify dividend_result decimal(14,2) not null comment '最终分红金额';;

alter table lottery_game
    modify rebate_max decimal(4,2) unsigned not null comment '最大返点比例%';;

alter table lottery_game_play
    modify bet_min decimal(14,2) unsigned not null comment '最小投注额',
    modify bet_max decimal(14,2) unsigned not null comment '最大投注额';;

alter table lottery_game_win
    modify bonus_rate decimal(12,4) unsigned not null comment '赔率';;

alter table staff_credit
    modify deposit_limit decimal(14,2) not null comment '入款限额',
    modify withdraw_limit decimal(14,2) not null comment '出款限额';;

alter table deposit_route
    modify min_money decimal(14,2) not null comment '最低入款',
    modify max_money decimal(14,2) not null comment '最高入款',
    modify coupon_rate decimal(4,2) not null comment '优惠比例',
    modify coupon_max decimal(14,2) not null comment '优惠金额上限',
    modify coupon_times int not null comment '优惠次数上限',
    modify coupon_audit_rate decimal(4,2) not null comment '优惠稽核倍数';;

alter table deposit_passage
    modify risk_control decimal(14,2) not null comment '风控金额',
    modify cumulate decimal(14,2) not null comment '目前存款';;

alter table site_setting
    modify dbl_value decimal(20,8) not null default 0 comment '双精度值';;

alter table staff_withdraw
    modify finish_money decimal(14,2) not null comment '完成总金额';;

replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select '%',schema(),concat('master_',schema()),table_name,user(),'select,insert,update,delete'
    from information_schema.tables
    where table_schema=schema() and table_name not like '\_%';;
replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select r.to_host,p.db,r.to_user,p.table_name,p.grantor,p.table_priv
    from mysql.tables_priv p inner join mysql.role_edges r on p.host=r.from_host and p.user=r.from_user;;
flush privileges;;
