delimiter ;;

alter table deposit_passage_gate
    modify account_number varchar(100) not null comment '商户号',
    modify account_name varchar(100) not null comment '商户名称';;
