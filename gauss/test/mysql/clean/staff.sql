delimiter ;;

truncate table announcement;;
truncate table carousel;;
truncate table deposit_passage;;
truncate table deposit_passage_bank;;
truncate table deposit_passage_gate;;
truncate table deposit_passage_simple;;
truncate table deposit_route;;
truncate table deposit_route_layer;;
truncate table deposit_route_way;;
truncate table dividend_settle;;
truncate table operate_log;;
truncate table popup;;
truncate table promotion;;
truncate table staff_bind_ip;;
truncate table staff_deposit;;
truncate table staff_layer;;
truncate table staff_session;;
truncate table staff_struct_agent;;
truncate table staff_struct_major;;
truncate table staff_struct_minor;;
truncate table staff_struct_slave;;
truncate table staff_withdraw;;
truncate table suggest;;

truncate table dividend_setting;;
insert into dividend_setting(scope_staff_id,
    grade1_bet_rate,grade1_profit_rate,grade1_fee_rate,grade1_tax_rate,
    grade2_bet_rate,grade2_profit_rate,grade2_fee_rate,grade2_tax_rate,
    grade3_bet_rate,grade3_profit_rate,grade3_fee_rate,grade3_tax_rate
)values
(1,0.1,1,30,20,0.2,2,20,15,0.3,3,10,10);;

truncate table lottery_game;;
truncate table lottery_game_play;;
truncate table lottery_game_win;;
truncate table external_game;;

truncate table site_setting;;
insert into site_setting(setting_key,description,data_type,int_value,dbl_value,str_value)values
('deposit_count_day','每日入款次数（次）',0,50,0,''),
('deposit_interval','每日入款间隔时间（S/秒)',0,100,0,''),
('deposit_bank_coupon_rate','银行入款优惠比例',1,0,2,''),
('deposit_bank_coupon_max','银行入款优惠上限',1,0,50,''),
('deposit_bank_coupon_count','银行入款优惠次数上限',0,3,0,''),
('deposit_gateway_coupon_rate','线上入款优惠比例',1,0,2,''),
('deposit_gateway_coupon_max','线上入款优惠上限',1,0,50,''),
('deposit_gateway_coupon_count','线上入款优惠次数上限',0,3,0,''),
('withdraw_max','出款上限',1,0,200000,''),
('withdraw_min','出款下限',1,0,50,''),
('withdraw_fee_max','出款手续费上限',1,0,5000,''),
('withdraw_fee_rate','出款手续费比例（%）',1,0,2,''),
('withdraw_interval','重复出款间隔时间（分钟）',0,5,0,''),
('withdraw_free','免收手续费次数（次）',0,5,0,'');;

truncate table staff_auth;;
truncate table staff_credit;;
truncate table staff_info;;
truncate table staff_permit;;
truncate table staff_struct_owner;;
insert into staff_auth(staff_id,staff_key,password_hash)values
(1,'owner','123456');;
insert into staff_info(staff_id,staff_name,staff_grade,master_id,leader_id)values
(1,'站长',0,0,0);;
insert into staff_permit(staff_id,operate_key)
select s.staff_id,o.operate_key from staff_info s inner join operate o on s.staff_grade=0 and s.master_id=0
union all
select s.staff_id,o.operate_key from staff_info s inner join operate o on s.staff_grade=0 and s.master_id!=0 and o.owner_permit=0
union all
select s.staff_id,o.operate_key from staff_info s inner join operate o on s.staff_grade=1 and s.master_id=0 and o.major_permit in (0,1)
union all
select s.staff_id,o.operate_key from staff_info s inner join operate o on s.staff_grade=1 and s.master_id!=0 and o.major_permit=0
union all
select s.staff_id,o.operate_key from staff_info s inner join operate o on s.staff_grade=2 and s.master_id=0 and o.minor_permit in (0,1)
union all
select s.staff_id,o.operate_key from staff_info s inner join operate o on s.staff_grade=2 and s.master_id!=0 and o.minor_permit=0
union all
select s.staff_id,o.operate_key from staff_info s inner join operate o on s.staff_grade=3 and s.master_id=0 and o.agent_permit in (0,1)
union all
select s.staff_id,o.operate_key from staff_info s inner join operate o on s.staff_grade=3 and s.master_id!=0 and o.agent_permit=0;;
