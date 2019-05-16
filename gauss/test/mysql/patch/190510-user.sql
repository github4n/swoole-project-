delimiter ;;

drop trigger if exists layer_message_update;;
drop trigger if exists layer_message_delete;;
drop trigger if exists user_message_update;;
drop trigger if exists user_message_delete;;
drop trigger if exists user_session_delete;;
drop trigger if exists user_info_insert;;
drop trigger if exists user_auth_delete;;
drop trigger if exists layer_info_delete;;
drop trigger if exists layer_permit_insert;;
drop trigger if exists layer_permit_delete;;
drop trigger if exists invite_info_delete;;
drop trigger if exists user_fungaming_insert;;
drop trigger if exists user_fungaming_update;;
drop trigger if exists user_fungaming_delete;;
drop trigger if exists coupon_daily_setting_insert;;
drop trigger if exists coupon_daily_setting_update;;
drop trigger if exists coupon_daily_setting_delete;;
drop trigger if exists coupon_upgrade_setting_insert;;
drop trigger if exists coupon_upgrade_setting_update;;
drop trigger if exists coupon_upgrade_setting_delete;;
drop trigger if exists coupon_deposit_setting_insert;;
drop trigger if exists coupon_deposit_setting_update;;
drop trigger if exists coupon_deposit_setting_delete;;
drop trigger if exists subsidy_setting_insert;;
drop trigger if exists subsidy_setting_update;;
drop trigger if exists subsidy_setting_delete;;
drop trigger if exists subsidy_game_setting_insert;;
drop trigger if exists subsidy_game_setting_update;;
drop trigger if exists subsidy_game_setting_delete;;


alter table layer_info
    modify min_deposit_amount decimal(14,2) not null default 0 comment '条件-充值金额',
    modify min_bet_amount decimal(14,2) not null default 0 comment '条件-有效投注金额',
    modify min_deposit_user int not null default 0 comment '条件-首充人数',
    modify withdraw_audit_amount decimal(14,2) not null default 10000 comment '出款审核下限';;

alter table coupon_daily_setting
    modify bet_money_1 decimal(14,2) not null comment '投注额1挡',
    modify coupon_rate_1 decimal(4,2) not null comment '加奖比例1挡',
    modify bet_money_2 decimal(14,2) not null comment '投注额2挡',
    modify coupon_rate_2 decimal(4,2) not null comment '加奖比例2挡',
    modify bet_money_3 decimal(14,2) not null comment '投注额3挡',
    modify coupon_rate_3 decimal(4,2) not null comment '加奖比例3挡',
    modify audit_rate decimal(4,2) not null comment '活动稽核倍数';;

alter table coupon_upgrade_setting
    modify coupon_money decimal(14,2) not null comment '晋级彩金',
    modify audit_rate decimal(4,2) not null comment '活动稽核倍数';;

alter table coupon_deposit_setting
    modify deposit_money decimal(14,2) not null comment '充值金额',
    modify coupon_money decimal(14,2) not null comment '送彩金',
    modify audit_rate decimal(4,2) not null comment '活动稽核倍数';;

alter table subsidy_game_setting
    modify min_bet decimal(14,2) not null comment '打码量',
    modify subsidy_rate decimal(4,2) not null comment '反水比例%',
    modify max_subsidy decimal(14,2) not null comment '反水上限';;

alter table bank_info
    modify withdraw_amount decimal(14,2) not null comment '累计出款金额';;

alter table bank_history
    modify withdraw_amount decimal(14,2) not null comment '累计出款金额';;

alter table brokerage_setting
    modify min_bet_amount decimal(14,2) not null comment '活跃会员最低投注额',
    modify min_deposit decimal(14,2) not null comment '活跃会员最低充值金额';;

alter table brokerage_rate
    modify broker_1_rate decimal(4,2) not null comment '一级下线佣金比例',
    modify broker_2_rate decimal(4,2) not null comment '二级下线佣金比例',
    modify broker_3_rate decimal(4,2) not null comment '三级下线佣金比例';;

replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select '%',schema(),concat('master_',schema()),table_name,user(),'select,insert,update,delete'
    from information_schema.tables
    where table_schema=schema() and table_name not like '\_%';;
replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select r.to_host,p.db,r.to_user,p.table_name,p.grantor,p.table_priv
    from mysql.tables_priv p inner join mysql.role_edges r on p.host=r.from_host and p.user=r.from_user;;
flush privileges;;
