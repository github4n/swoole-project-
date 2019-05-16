delimiter ;;

alter table deposit_finish add column
	deposit_audit double not null comment '充值稽核'
after finish_money;;

drop trigger if exists deposit_finish_insert;;
create trigger deposit_finish_insert before insert on deposit_finish for each row
begin
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select l.user_id,l.user_key,l.account_name,l.layer_id,new.finish_money,new.deposit_audit,new.coupon_audit,'deposit_finish',json_object(
            'bank',(
                select json_object(
                    'from_type',b.from_type,'from_name',b.from_name,
                    'to_bank_name',b.to_bank_name,'to_bank_branch',b.to_bank_branch,
                    'to_account_number',b.to_account_number,'to_account_name',b.to_account_name
                ) from deposit_bank b where b.deposit_serial=l.deposit_serial
            ),
            'gateway',(
                select json_object(
                    'gate_key',g.gate_key,'gate_name',g.gate_name,'way_key',g.way_key,'way_name',g.way_name,
                    'to_account_number',g.to_account_number,'to_account_name',g.to_account_name
                ) from deposit_gateway g where g.deposit_serial=l.deposit_serial
            )
        ) from deposit_launch l where l.deposit_serial=new.deposit_serial;
    set new.finish_deal_serial=serial_last('deal');
    set new.finish_time=unix_timestamp();
end;;
