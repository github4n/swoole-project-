delimiter ;;


drop table if exists external_audit_fungaming;;
drop table if exists external_export_fungaming;;
drop table if exists external_import_fungaming;;

drop view if exists external_audit_fungaming_intact;;
drop view if exists external_export_fungaming_intact;;
drop view if exists external_import_fungaming_intact;;


alter table external_audit
    add column external_key varchar(100) not null comment '三方平台：唯一性检测' after external_type,
    add unique(external_type,external_key);;

alter table external_export_launch
    add column external_key varchar(100) not null comment '三方平台：唯一性检测' after external_type,
    add unique(external_type,external_key);;

alter table external_import_launch
    add column external_key varchar(100) not null comment '三方平台：唯一性检测' after external_type,
    add unique(external_type,external_key);;


drop trigger if exists external_audit_insert;;
create trigger external_audit_insert before insert on external_audit for each row
begin
    insert into serial_current set serial_key='external_audit' on duplicate key update current=current+1;
    set new.audit_serial=serial_last('external_audit');
    insert into deal(user_id,user_key,account_name,layer_id,vary_money,vary_deposit_audit,vary_coupon_audit,deal_type,summary)
        select new.user_id,new.user_key,new.account_name,new.layer_id,0,0,-new.audit_amount,'external_audit',json_object(
            'external_type',new.external_type,'external_key',new.external_key
        );
    set new.audit_deal_serial=serial_last('deal');
    set new.audit_time=unix_timestamp();
end;;


drop view if exists external_export_intact;;
create view external_export_intact as
	select l.export_serial,l.user_id,l.user_key,l.layer_id,l.external_type,l.external_key,l.launch_data,
        l.launch_money,l.launch_deal_serial,l.launch_time,
		s.success_time,s.success_data,
		f.failure_deal_serial,f.failure_time,f.failure_data
	from external_export_launch l
		left join external_export_success s on l.export_serial=s.export_serial
		left join external_export_failure f on l.export_serial=f.export_serial
;;

drop view if exists external_import_intact;;
create view external_import_intact as
	select l.import_serial,l.user_id,l.user_key,l.layer_id,l.external_type,l.external_key,l.launch_data,
        l.launch_money,l.launch_time,
		s.success_deal_serial,s.success_time,s.success_data,
		f.failure_time,f.failure_data
	from external_import_launch l
		left join external_import_success s on l.import_serial=s.import_serial
		left join external_import_failure f on l.import_serial=f.import_serial
;;


replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select '%',schema(),concat('master_',schema()),table_name,user(),'select,insert,update,delete'
    from information_schema.tables
    where table_schema=schema() and table_name not like '\_%';;
replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select r.to_host,p.db,r.to_user,p.table_name,p.grantor,p.table_priv
    from mysql.tables_priv p inner join mysql.role_edges r on p.host=r.from_host and p.user=r.from_user;;
flush privileges;;
