delimiter ;;

alter table monthly_tax
    modify profit_lottery double not null comment '彩票损益',
    modify profit_video double not null comment '真人损益',
    modify profit_game double not null comment '电子损益',
    modify profit_sports double not null comment '体育损益',
    modify profit_cards double not null comment '棋牌损益';;
