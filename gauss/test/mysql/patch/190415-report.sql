delimiter ;;

alter table daily_user add column
    brokerage_amount double not null comment '返佣总额'
after subsidy_amount;;

alter table weekly_user add column
    brokerage_amount double not null comment '返佣总额'
after subsidy_amount;;

alter table monthly_user add column
    brokerage_amount double not null comment '返佣总额'
after subsidy_amount;;
