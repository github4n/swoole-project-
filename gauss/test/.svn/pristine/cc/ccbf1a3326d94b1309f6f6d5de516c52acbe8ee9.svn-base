delimiter ;;

drop trigger if exists lottery_preiod_insert;;
drop trigger if exists lottery_period_delete;;
drop trigger if exists lottery_number_insert;;
drop trigger if exists lottery_spider_insert;;

alter table lottery_period
    drop index game_key,
    add index (game_key,plan_time);;

alter table lottery_win
    modify win_rate decimal(16,8) unsigned not null comment '理论赔率',
    modify return_rate decimal(10,8) unsigned not null comment '返奖率',
    modify suggest_bonus_rate decimal(12,4) as (truncate(win_rate*return_rate,decimal_place)) comment '建议赔率';;

replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select '%',schema(),concat('master_',schema()),table_name,user(),'select,insert,update,delete'
    from information_schema.tables
    where table_schema=schema() and table_name not like '\_%';;
replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select r.to_host,p.db,r.to_user,p.table_name,p.grantor,p.table_priv
    from mysql.tables_priv p inner join mysql.role_edges r on p.host=r.from_host and p.user=r.from_user;;
flush privileges;;
