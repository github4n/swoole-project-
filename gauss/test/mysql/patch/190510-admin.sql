delimiter ;;

drop trigger if exists admin_auth_delete;;
drop trigger if exists admin_role_insert;;
drop trigger if exists admin_role_update;;
drop trigger if exists admin_permit_insert;;
drop trigger if exists admin_permit_delete;;
drop trigger if exists admin_appoint_insert;;
drop trigger if exists admin_appoint_delete;;
drop trigger if exists admin_session_delete;;
drop trigger if exists site_external_game_insert;;
drop trigger if exists site_external_game_update;;
drop trigger if exists site_external_game_delete;;
drop trigger if exists site_game_insert;;
drop trigger if exists site_play_insert;;
drop trigger if exists site_play_delete;;
drop trigger if exists site_win_insert;;
drop trigger if exists site_win_delete;;
drop trigger if exists site_tax_config_insert;;
drop trigger if exists site_tax_config_delete;;
drop trigger if exists admin_info_insert;;

alter table site_game
    modify rebate_max decimal(4,2) unsigned not null default 10 comment '最大返点比例%',
    modify subsidy_rate decimal(4,2) unsigned not null default 0 comment '默认反水比例%';;

alter table site_external_game
    modify subsidy_rate decimal(3,1) unsigned not null default 0 comment '默认反水比例%';;

alter table site_play
    modify bet_min decimal(14,2) unsigned not null default 2 comment '最小投注额',
    modify bet_max decimal(14,2) unsigned not null default 5000 comment '最大投注额';;

alter table site_win
    modify bonus_rate decimal(12,4) unsigned not null comment '赔率';;

alter table site_rent_config
    modify month_rent decimal(14,2) unsigned not null comment '月服务费';;

alter table site_tax_config
    modify range_max decimal(14,2) unsigned not null comment '损益额度范围上限',
    modify tax_rate decimal(8,4) unsigned not null comment '提成比例';;

replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select '%',schema(),concat('master_',schema()),table_name,user(),'select,insert,update,delete'
    from information_schema.tables
    where table_schema=schema() and table_name not like '\_%';;
replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select r.to_host,p.db,r.to_user,p.table_name,p.grantor,p.table_priv
    from mysql.tables_priv p inner join mysql.role_edges r on p.host=r.from_host and p.user=r.from_user;;
flush privileges;;
