delimiter ;;

alter table daily_user_brokerage add column (
    broker_1_bet_user double not null comment '一级下线投注人数',
    broker_2_bet_user double not null comment '二级下线投注人数',
    broker_3_bet_user double not null comment '三级下线投注人数'
);;

alter table weekly_user_brokerage add column (
    broker_1_bet_user double not null comment '一级下线投注人数',
    broker_2_bet_user double not null comment '二级下线投注人数',
    broker_3_bet_user double not null comment '三级下线投注人数'
);;

alter table monthly_user_brokerage add column (
    broker_1_bet_user double not null comment '一级下线投注人数',
    broker_2_bet_user double not null comment '二级下线投注人数',
    broker_3_bet_user double not null comment '三级下线投注人数'
);;
