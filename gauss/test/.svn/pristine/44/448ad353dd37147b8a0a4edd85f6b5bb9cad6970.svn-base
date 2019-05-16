delimiter ;;

drop table if exists weekly_site;;
create table weekly_site(
    weekly int unsigned not null comment '周 php:intval(date(oW))',
    site_key varchar(10) not null comment '站点key',
    site_name varchar(20) not null comment '站点名字',
    user_all int unsigned not null comment '用户数',
    user_register int unsigned not null comment '新注册用户数',
    user_first_deposit int unsigned not null comment '首充用户数',
    user_active int unsigned not null comment '活跃用户数',
    bet_all decimal(14,2) not null comment '有效投注',
    bet_lottery decimal(14,2) not null comment '彩票投注',
    bet_video decimal(14,2) not null comment '真人视讯投注',
    bet_game decimal(14,2) not null comment '电子游戏投注',
    bet_sports decimal(14,2) not null comment '体育投注',
    bet_cards decimal(14,2) not null comment '棋牌投注',
    bonus_all decimal(14,2) not null comment '派奖',
    bonus_lottery decimal(14,2) not null comment '彩票派奖',
    bonus_video decimal(14,2) not null comment '真人视讯派奖',
    bonus_game decimal(14,2) not null comment '电子游戏派奖',
    bonus_sports decimal(14,2) not null comment '体育派奖',
    bonus_cards decimal(14,2) not null comment '棋牌派奖',
    profit_all decimal(14,2) not null comment '损益',
    profit_lottery decimal(14,2) not null comment '彩票损益',
    profit_video decimal(14,2) not null comment '真人视讯损益',
    profit_game decimal(14,2) not null comment '电子游戏损益',
    profit_sports decimal(14,2) not null comment '体育损益',
    profit_cards decimal(14,2) not null comment '棋牌损益',
    rebate decimal(14,2) not null comment '返点',
    index(site_key,weekly),
    primary key(weekly,site_key)
) comment '每周站点分析';;

drop table if exists weekly_site_lottery;;
create table weekly_site_lottery(
    weekly int unsigned not null comment '周 php:intval(date(oW))',
    site_key varchar(10) not null comment '站点key',
    site_name varchar(20) not null comment '站点名字',
    game_key varchar(20) not null comment '彩种key',
    game_name varchar(20) not null comment '彩种名字',
    bet_count int not null comment '注单数量',
    bet_amount decimal(14,2) not null comment '投注金额',
    bonus_amount decimal(14,2) not null comment '派奖金额',
    profit_amount decimal(14,2) not null comment '损益金额',
    index(site_key,weekly,game_key),
    primary key(weekly,site_key,game_key)
) comment '每周站点彩票报表';;

drop table if exists monthly_tax;;
create table monthly_tax(
    monthly int unsigned not null comment '月份:intval(date(Ym))',
    site_key varchar(10) not null comment '站点key',
    site_name varchar(20) not null comment '站点名字',
    tax_total decimal(14,2) unsigned not null comment '应收总金额',
    tax_rent decimal(14,2) unsigned not null comment '服务费',
    wager_lottery decimal(14,2) not null comment '彩票有效投注金额',
    bonus_lottery decimal(14,2) not null comment '彩票派奖金额',
    profit_lottery decimal(14,2) not null comment '彩票损益',
    setting_lottery json not null comment '彩票提成比例',
    tax_lottery decimal(14,2) unsigned not null comment '彩票提成',
    wager_video decimal(14,2) not null comment '真人有效投注金额',
    bonus_video decimal(14,2) not null comment '真人派奖金额',
    profit_video decimal(14,2) not null comment '真人损益',
    setting_video json not null comment '真人提成比例',
    tax_video decimal(14,2) unsigned not null comment '真人提成',
    wager_game decimal(14,2) not null comment '电子有效投注金额',
    bonus_game decimal(14,2) not null comment '电子派奖金额',
    profit_game decimal(14,2) not null comment '电子损益',
    setting_game json not null comment '电子提成比例',
    tax_game decimal(14,2) unsigned not null comment '电子提成',
    wager_sports decimal(14,2) not null comment '体育有效投注金额',
    bonus_sports decimal(14,2) not null comment '体育派奖金额',
    profit_sports decimal(14,2) not null comment '体育损益',
    setting_sports json not null comment '体育提成比例',
    tax_sports decimal(14,2) unsigned not null comment '体育提成',
    wager_cards decimal(14,2) not null comment '棋牌有效投注金额',
    bonus_cards decimal(14,2) not null comment '棋牌派奖金额',
    profit_cards decimal(14,2) not null comment '棋牌损益',
    setting_cards json not null comment '棋牌提成比例',
    tax_cards decimal(14,2) unsigned not null comment '棋牌提成',
    index(site_key,monthly),
    primary key(monthly,site_key)
) comment '月结对账';;

drop table if exists monthly_site;;
create table monthly_site(
    monthly int unsigned not null comment '月 php:intval(date(Ym))',
    site_key varchar(10) not null comment '站点key',
    site_name varchar(20) not null comment '站点名字',
    user_all int unsigned not null comment '用户数',
    user_register int unsigned not null comment '新注册用户数',
    user_first_deposit int unsigned not null comment '首充用户数',
    user_active int unsigned not null comment '活跃用户数',
    bet_all decimal(14,2) not null comment '有效投注',
    bet_lottery decimal(14,2) not null comment '彩票投注',
    bet_video decimal(14,2) not null comment '真人视讯投注',
    bet_game decimal(14,2) not null comment '电子游戏投注',
    bet_sports decimal(14,2) not null comment '体育投注',
    bet_cards decimal(14,2) not null comment '棋牌投注',
    bonus_all decimal(14,2) not null comment '派奖',
    bonus_lottery decimal(14,2) not null comment '彩票派奖',
    bonus_video decimal(14,2) not null comment '真人视讯派奖',
    bonus_game decimal(14,2) not null comment '电子游戏派奖',
    bonus_sports decimal(14,2) not null comment '体育派奖',
    bonus_cards decimal(14,2) not null comment '棋牌派奖',
    profit_all decimal(14,2) not null comment '损益',
    profit_lottery decimal(14,2) not null comment '彩票损益',
    profit_video decimal(14,2) not null comment '真人视讯损益',
    profit_game decimal(14,2) not null comment '电子游戏损益',
    profit_sports decimal(14,2) not null comment '体育损益',
    profit_cards decimal(14,2) not null comment '棋牌损益',
    rebate decimal(14,2) not null comment '返点',
    index(site_key,monthly),
    primary key(monthly,site_key)
) comment '每月站点分析';;

drop table if exists monthly_site_lottery;;
create table monthly_site_lottery(
    monthly int unsigned not null comment '月 php:intval(date(Ym))',
    site_key varchar(10) not null comment '站点key',
    site_name varchar(20) not null comment '站点名字',
    game_key varchar(20) not null comment '彩种key',
    game_name varchar(20) not null comment '彩种名字',
    bet_count int not null comment '注单数量',
    bet_amount decimal(14,2) not null comment '投注金额',
    bonus_amount decimal(14,2) not null comment '派奖金额',
    profit_amount decimal(14,2) not null comment '损益金额',
    index(site_key,monthly,game_key),
    primary key(monthly,site_key,game_key)
) comment '每月站点彩票报表';;

drop table if exists daily_site;;
create table daily_site(
    daily int unsigned not null comment '天 php:intval(date(Ymd))',
    site_key varchar(10) not null comment '站点key',
    site_name varchar(20) not null comment '站点名字',
    user_all int unsigned not null comment '用户数',
    user_register int unsigned not null comment '新注册用户数',
    user_first_deposit int unsigned not null comment '首充用户数',
    user_active int unsigned not null comment '活跃用户数',
    bet_all decimal(14,2) not null comment '有效投注',
    bet_lottery decimal(14,2) not null comment '彩票投注',
    bet_video decimal(14,2) not null comment '真人视讯投注',
    bet_game decimal(14,2) not null comment '电子游戏投注',
    bet_sports decimal(14,2) not null comment '体育投注',
    bet_cards decimal(14,2) not null comment '棋牌投注',
    bonus_all decimal(14,2) not null comment '派奖',
    bonus_lottery decimal(14,2) not null comment '彩票派奖',
    bonus_video decimal(14,2) not null comment '真人视讯派奖',
    bonus_game decimal(14,2) not null comment '电子游戏派奖',
    bonus_sports decimal(14,2) not null comment '体育派奖',
    bonus_cards decimal(14,2) not null comment '棋牌派奖',
    profit_all decimal(14,2) not null comment '损益',
    profit_lottery decimal(14,2) not null comment '彩票损益',
    profit_video decimal(14,2) not null comment '真人视讯损益',
    profit_game decimal(14,2) not null comment '电子游戏损益',
    profit_sports decimal(14,2) not null comment '体育损益',
    profit_cards decimal(14,2) not null comment '棋牌损益',
    rebate decimal(14,2) not null comment '返点',
    index(site_key,daily),
    primary key(daily,site_key)
) comment '每日站点分析';;

drop table if exists daily_site_lottery;;
create table daily_site_lottery(
    daily int unsigned not null comment '天 php:intval(date(Ymd))',
    site_key varchar(10) not null comment '站点key',
    site_name varchar(20) not null comment '站点名字',
    game_key varchar(20) not null comment '彩种key',
    game_name varchar(20) not null comment '彩种名字',
    bet_count int not null comment '注单数量',
    bet_amount decimal(14,2) not null comment '投注金额',
    bonus_amount decimal(14,2) not null comment '派奖金额',
    profit_amount decimal(14,2) not null comment '损益金额',
    index(site_key,daily,game_key),
    primary key(daily,site_key,game_key)
) comment '每日站点彩票报表';;

drop table if exists daily_site_external;;
create table daily_site_external(
    daily int unsigned not null comment '天 php:intval(date(Ymd))',
    site_key varchar(10) not null comment '站点key',
    site_name varchar(20) not null comment '站点名字',
    category_key varchar(10) not null comment '类型：video-真人视讯，game-电子游戏，sports-体育，cards-棋牌',
    interface_key varchar(10) not null comment '外接口： fg-FunGaming，ky-开元棋牌，lb-Lebo体育，ag-AsiaGaming',
    game_key varchar(20) not null comment '外接口key',
    game_name varchar(20) not null comment '外接口名字',
    bet_count int not null comment '注单数量',
    bet_amount decimal(14,2) not null comment '投注金额',
    bonus_amount decimal(14,2) not null comment '派奖金额',
    profit_amount decimal(14,2) not null comment '损益金额',
    index(site_key,daily,game_key),
    primary key(daily,site_key,game_key)
) comment '每日站点外接口游戏报表';;

