delimiter ;;

drop trigger if exists weekly_site_insert;;
drop trigger if exists weekly_site_delete;;
drop trigger if exists weekly_site_lottery_insert;;
drop trigger if exists weekly_site_lottery_delete;;
drop trigger if exists monthly_tax_insert;;
drop trigger if exists monthly_tax_delete;;
drop trigger if exists monthly_site_insert;;
drop trigger if exists monthly_site_delete;;
drop trigger if exists monthly_site_lottery_insert;;
drop trigger if exists monthly_site_lottery_delete;;
drop trigger if exists daily_site_insert;;
drop trigger if exists daily_site_delete;;
drop trigger if exists daily_site_lottery_insert;;
drop trigger if exists daily_site_lottery_delete;;
drop trigger if exists daily_site_external_insert;;
drop trigger if exists daily_site_external_delete;;


alter table weekly_site
    modify bet_all decimal(14,2) not null comment '有效投注',
    modify bet_lottery decimal(14,2) not null comment '彩票投注',
    modify bet_video decimal(14,2) not null comment '真人视讯投注',
    modify bet_game decimal(14,2) not null comment '电子游戏投注',
    modify bet_sports decimal(14,2) not null comment '体育投注',
    modify bet_cards decimal(14,2) not null comment '棋牌投注',
    modify bonus_all decimal(14,2) not null comment '派奖',
    modify bonus_lottery decimal(14,2) not null comment '彩票派奖',
    modify bonus_video decimal(14,2) not null comment '真人视讯派奖',
    modify bonus_game decimal(14,2) not null comment '电子游戏派奖',
    modify bonus_sports decimal(14,2) not null comment '体育派奖',
    modify bonus_cards decimal(14,2) not null comment '棋牌派奖',
    modify profit_all decimal(14,2) not null comment '损益',
    modify profit_lottery decimal(14,2) not null comment '彩票损益',
    modify profit_video decimal(14,2) not null comment '真人视讯损益',
    modify profit_game decimal(14,2) not null comment '电子游戏损益',
    modify profit_sports decimal(14,2) not null comment '体育损益',
    modify profit_cards decimal(14,2) not null comment '棋牌损益',
    modify rebate decimal(14,2) not null comment '返点';;

alter table weekly_site_lottery
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table monthly_tax
    modify tax_total decimal(14,2) unsigned not null comment '应收总金额',
    modify tax_rent decimal(14,2) unsigned not null comment '服务费',
    modify wager_lottery decimal(14,2) not null comment '彩票有效投注金额',
    modify bonus_lottery decimal(14,2) not null comment '彩票派奖金额',
    modify profit_lottery decimal(14,2) not null comment '彩票损益',
    modify tax_lottery decimal(14,2) unsigned not null comment '彩票提成',
    modify wager_video decimal(14,2) not null comment '真人有效投注金额',
    modify bonus_video decimal(14,2) not null comment '真人派奖金额',
    modify profit_video decimal(14,2) not null comment '真人损益',
    modify tax_video decimal(14,2) unsigned not null comment '真人提成',
    modify wager_game decimal(14,2) not null comment '电子有效投注金额',
    modify bonus_game decimal(14,2) not null comment '电子派奖金额',
    modify profit_game decimal(14,2) not null comment '电子损益',
    modify tax_game decimal(14,2) unsigned not null comment '电子提成',
    modify wager_sports decimal(14,2) not null comment '体育有效投注金额',
    modify bonus_sports decimal(14,2) not null comment '体育派奖金额',
    modify profit_sports decimal(14,2) not null comment '体育损益',
    modify tax_sports decimal(14,2) unsigned not null comment '体育提成',
    modify wager_cards decimal(14,2) not null comment '棋牌有效投注金额',
    modify bonus_cards decimal(14,2) not null comment '棋牌派奖金额',
    modify profit_cards decimal(14,2) not null comment '棋牌损益',
    modify tax_cards decimal(14,2) unsigned not null comment '棋牌提成';;

alter table monthly_site
    modify bet_all decimal(14,2) not null comment '有效投注',
    modify bet_lottery decimal(14,2) not null comment '彩票投注',
    modify bet_video decimal(14,2) not null comment '真人视讯投注',
    modify bet_game decimal(14,2) not null comment '电子游戏投注',
    modify bet_sports decimal(14,2) not null comment '体育投注',
    modify bet_cards decimal(14,2) not null comment '棋牌投注',
    modify bonus_all decimal(14,2) not null comment '派奖',
    modify bonus_lottery decimal(14,2) not null comment '彩票派奖',
    modify bonus_video decimal(14,2) not null comment '真人视讯派奖',
    modify bonus_game decimal(14,2) not null comment '电子游戏派奖',
    modify bonus_sports decimal(14,2) not null comment '体育派奖',
    modify bonus_cards decimal(14,2) not null comment '棋牌派奖',
    modify profit_all decimal(14,2) not null comment '损益',
    modify profit_lottery decimal(14,2) not null comment '彩票损益',
    modify profit_video decimal(14,2) not null comment '真人视讯损益',
    modify profit_game decimal(14,2) not null comment '电子游戏损益',
    modify profit_sports decimal(14,2) not null comment '体育损益',
    modify profit_cards decimal(14,2) not null comment '棋牌损益',
    modify rebate decimal(14,2) not null comment '返点';;

alter table monthly_site_lottery
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table daily_site
    modify bet_all decimal(14,2) not null comment '有效投注',
    modify bet_lottery decimal(14,2) not null comment '彩票投注',
    modify bet_video decimal(14,2) not null comment '真人视讯投注',
    modify bet_game decimal(14,2) not null comment '电子游戏投注',
    modify bet_sports decimal(14,2) not null comment '体育投注',
    modify bet_cards decimal(14,2) not null comment '棋牌投注',
    modify bonus_all decimal(14,2) not null comment '派奖',
    modify bonus_lottery decimal(14,2) not null comment '彩票派奖',
    modify bonus_video decimal(14,2) not null comment '真人视讯派奖',
    modify bonus_game decimal(14,2) not null comment '电子游戏派奖',
    modify bonus_sports decimal(14,2) not null comment '体育派奖',
    modify bonus_cards decimal(14,2) not null comment '棋牌派奖',
    modify profit_all decimal(14,2) not null comment '损益',
    modify profit_lottery decimal(14,2) not null comment '彩票损益',
    modify profit_video decimal(14,2) not null comment '真人视讯损益',
    modify profit_game decimal(14,2) not null comment '电子游戏损益',
    modify profit_sports decimal(14,2) not null comment '体育损益',
    modify profit_cards decimal(14,2) not null comment '棋牌损益',
    modify rebate decimal(14,2) not null comment '返点';;

alter table daily_site_lottery
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table daily_site_external
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select '%',schema(),concat('master_',schema()),table_name,user(),'select,insert,update,delete'
    from information_schema.tables
    where table_schema=schema() and table_name not like '\_%';;
replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select r.to_host,p.db,r.to_user,p.table_name,p.grantor,p.table_priv
    from mysql.tables_priv p inner join mysql.role_edges r on p.host=r.from_host and p.user=r.from_user;;
flush privileges;;
