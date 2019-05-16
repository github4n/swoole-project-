delimiter ;;

alter table deposit_gateway
    modify to_account_number varchar(100) not null comment '商户号',
    modify to_account_name varchar(100) not null comment '商户名称';;
