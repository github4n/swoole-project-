delimiter ;;

truncate table bank_history;;
truncate table bank_info;;
truncate table coupon_daily_setting;;
truncate table coupon_deposit_setting;;
truncate table coupon_upgrade_setting;;
truncate table invite_info;;
truncate table layer_message;;
truncate table operate_log;;
truncate table subsidy_game_setting;;
truncate table subsidy_setting;;
truncate table user_auth;;
truncate table user_fungaming;;
truncate table user_info;;
truncate table user_ip_history;;
truncate table user_message;;
truncate table user_session;;
truncate table layer_permit;;

truncate table layer_info;;
truncate table brokerage_setting;;
truncate table brokerage_rate;;
insert into layer_info(layer_name,layer_type,min_deposit_amount,min_bet_amount,min_deposit_user,max_day)values
('新会员',2,0,0,0,0),
('普通会员',2,10,100,0,0),
('青铜会员',2,100,1000,0,0),
('白银会员',2,1000,10000,0,0),
('黄金会员',2,10000,100000,0,0),
('铂金会员',2,100000,1000000,0,0),
('钻石会员',2,1000000,10000000,0,0),
('超级VIP',1,0,0,0,0),
('大客户',1,0,0,0,0),
('黑名单',1,0,0,0,0),
('新代理',103,0,0,0,60),
('一级代理',102,0,0,0,0),
('二级代理',102,100,1000,5,0),
('三级代理',102,1000,10000,20,0),
('四级代理',102,10000,100000,100,0),
('五级代理',102,100000,1000000,1000,0),
('特约代理',101,0,0,0,0),
('黑名单代理',101,0,0,0,0);;
