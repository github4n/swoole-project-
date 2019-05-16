delimiter ;;

alter table deposit_launch
	drop column coupon_money,
	drop column coupon_audit_rate,
    add column route_id int unsigned not null comment '路线id' after passage_name
;;

alter view deposit_intact as
    select l.deposit_serial,l.user_id,l.user_key,l.account_name,l.layer_id,
        l.passage_id,l.passage_name,l.route_id,l.launch_money,l.launch_time,l.launch_device,
        f.finish_money,f.coupon_audit,f.finish_time,f.finish_staff_id,f.finish_staff_name,f.finish_deal_serial,
        d.vary_money,d.vary_deposit_audit,d.vary_coupon_audit,
        d.old_money,d.old_deposit_audit,d.old_coupon_audit,
        d.new_money,d.new_deposit_audit,d.new_coupon_audit,
        d.user_id as deal_user_id,d.deal_type,d.summary,d.deal_time,
        c.cancel_time,c.cancel_staff_id,c.cancel_staff_name,c.cancel_reason
    from deposit_launch l
        left join deposit_finish f on l.deposit_serial=f.deposit_serial
        left join deal d on f.finish_deal_serial=d.deal_serial
        left join deposit_cancel c on l.deposit_serial=c.deposit_serial
;;

alter view deposit_bank_intact as
    select l.deposit_serial,l.user_id,l.user_key,l.account_name,l.layer_id,
        l.passage_id,l.passage_name,l.route_id,l.launch_money,l.launch_time,l.launch_device,
        b.from_type,b.from_name,
        b.to_bank_name,b.to_bank_branch,b.to_account_number,b.to_account_name,
        f.finish_money,f.coupon_audit,f.finish_time,f.finish_staff_id,f.finish_staff_name,f.finish_deal_serial,
        d.vary_money,d.vary_deposit_audit,d.vary_coupon_audit,
        d.old_money,d.old_deposit_audit,d.old_coupon_audit,
        d.new_money,d.new_deposit_audit,d.new_coupon_audit,
        d.user_id as deal_user_id,d.deal_type,d.summary,d.deal_time,
        c.cancel_time,c.cancel_staff_id,c.cancel_staff_name,c.cancel_reason
    from deposit_launch l inner join deposit_bank b on l.deposit_serial=b.deposit_serial
        left join deposit_finish f on l.deposit_serial=f.deposit_serial
        left join deal d on f.finish_deal_serial=d.deal_serial
        left join deposit_cancel c on l.deposit_serial=c.deposit_serial
;;

alter view deposit_gateway_intact as
    select l.deposit_serial,l.user_id,l.user_key,l.account_name,l.layer_id,
        l.passage_id,l.passage_name,l.route_id,l.launch_money,l.launch_time,l.launch_device,
        g.gate_key,g.gate_name,g.way_key,g.way_name,g.to_account_number,g.to_account_name,
        f.finish_money,f.coupon_audit,f.finish_time,f.finish_staff_id,f.finish_staff_name,f.finish_deal_serial,
        d.vary_money,d.vary_deposit_audit,d.vary_coupon_audit,
        d.old_money,d.old_deposit_audit,d.old_coupon_audit,
        d.new_money,d.new_deposit_audit,d.new_coupon_audit,
        d.user_id as deal_user_id,d.deal_type,d.summary,d.deal_time,
        c.cancel_time,c.cancel_staff_id,c.cancel_staff_name,c.cancel_reason
    from deposit_launch l inner join deposit_gateway g on l.deposit_serial=g.deposit_serial
        left join deposit_finish f on l.deposit_serial=f.deposit_serial
        left join deal d on f.finish_deal_serial=d.deal_serial
        left join deposit_cancel c on l.deposit_serial=c.deposit_serial
;;

alter view deposit_simple_intact as
    select l.deposit_serial,l.user_id,l.user_key,l.account_name,l.layer_id,
        l.passage_id,l.passage_name,l.route_id,l.launch_money,l.launch_time,l.launch_device,
        s.pay_url,s.memo,
        f.finish_money,f.coupon_audit,f.finish_time,f.finish_staff_id,f.finish_staff_name,f.finish_deal_serial,
        d.vary_money,d.vary_deposit_audit,d.vary_coupon_audit,
        d.old_money,d.old_deposit_audit,d.old_coupon_audit,
        d.new_money,d.new_deposit_audit,d.new_coupon_audit,
        d.user_id as deal_user_id,d.deal_type,d.summary,d.deal_time,
        c.cancel_time,c.cancel_staff_id,c.cancel_staff_name,c.cancel_reason
    from deposit_launch l inner join deposit_simple s on l.deposit_serial=s.deposit_serial
        left join deposit_finish f on l.deposit_serial=f.deposit_serial
        left join deal d on f.finish_deal_serial=d.deal_serial
        left join deposit_cancel c on l.deposit_serial=c.deposit_serial
;;
