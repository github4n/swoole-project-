delimiter ;;

truncate table operate;;
insert into operate(operate_key,operate_name,owner_permit,major_permit,minor_permit,agent_permit,record_log)values
('self_login','登录',0,0,0,0,1),
('self_logout','退出',0,0,0,0,1),
('self_password','修改密码',0,0,0,0,1),
('home_today','首页-今日统计',0,0,0,0,0),
('home_report','首页-帐目汇总',1,1,1,1,0),
('slave_list_select','子账号管理-查看',1,1,1,1,0),
('slave_list_insert','子账号管理-新建',1,1,1,1,1),
('slave_list_update','子账号管理-修改',1,1,1,1,1),
('slave_list_delete','子账号管理-删除',1,1,1,1,1),
('slave_ip_select','子账号绑定IP-查看',1,-1,-1,-1,0),
('slave_ip_insert','子账号绑定IP-新建',1,-1,-1,-1,1),
('slave_ip_delete','子账号绑定IP-删除',1,-1,-1,-1,1),
('slave_log','子账号操作日志',1,1,1,1,0),
('staff_list_major_select','体系人员-大股东-查看',0,-1,-1,-1,0),
('staff_list_major_insert','体系人员-大股东-添加',1,-1,-1,-1,1),
('staff_list_major_update','体系人员-大股东-修改',1,-1,-1,-1,1),
('staff_list_major_delete','体系人员-大股东-删除',1,-1,-1,-1,1),
('staff_list_minor_select','体系人员-股东-查看',0,0,-1,-1,0),
('staff_list_minor_insert','体系人员-股东-添加',1,1,-1,-1,1),
('staff_list_minor_update','体系人员-股东-修改',1,1,-1,-1,1),
('staff_list_minor_delete','体系人员-股东-删除',1,1,-1,-1,1),
('staff_list_agent_select','体系人员-总代理-查看',0,0,0,-1,0),
('staff_list_agent_insert','体系人员-总代理-添加',1,1,1,-1,1),
('staff_list_agent_update','体系人员-总代理-修改',1,1,1,-1,1),
('staff_list_agent_delete','体系人员-总代理-删除',1,1,1,-1,1),
('staff_dividend_select','体系分红设置-查看',1,-1,-1,-1,0),
('staff_dividend_insert','体系分红设置-添加',1,-1,-1,-1,1),
('staff_dividend_update','体系分红设置-修改',1,-1,-1,-1,1),
('staff_dividend_delete','体系分红设置-删除',1,-1,-1,-1,1),
('staff_report_major','体系分红报表-大股东',1,-1,-1,-1,0),
('staff_report_minor','体系分红报表-股东',1,1,-1,-1,0),
('staff_report_agent','体系分红报表-总代理',1,1,1,-1,0),
('staff_report_self','体系分红报表-个人',-1,1,1,1,0),
('staff_log_major','体系操作日志-大股东',1,-1,-1,-1,0),
('staff_log_minor','体系操作日志-股东',1,1,-1,-1,0),
('staff_log_agent','体系操作日志-总代理',1,1,1,-1,0),
('staff_log_self','体系操作日志-个人',0,0,0,0,0),
('user_list_select','会员列表-查看',0,0,0,0,1),
('user_list_insert','会员列表-添加',-1,-1,-1,1,1),
('user_list_update','会员列表-修改',1,1,1,1,1),
('user_layer_select','会员层级-查看',0,0,0,0,0),
('user_layer_insert','会员层级-新增',1,-1,-1,-1,1),
('user_layer_update','会员层级-修改',1,-1,-1,-1,1),
('user_layer_delete','会员层级-删除',1,-1,-1,-1,1),
('user_money','会员-出入款查询',1,1,1,1,0),
('user_bet','会员-投注记录',1,1,1,1,0),
('user_analysis','会员-分析',1,1,1,1,0),
('user_audit','会员-交易统计',1,1,1,1,0),
('money_manual','现金-手工存提款',1,-1,-1,-1,1),
('money_simple','现金-快捷入款',1,-1,-1,-1,1),
('money_deposit_passage','现金-入款账户',1,-1,-1,-1,1),
('money_deposit_route','现金-支付管理',1,-1,-1,-1,1),
('money_deposit_deal','现金-入款记录',1,-1,-1,-1,1),
('money_setting','现金-出入款设定',1,-1,-1,-1,1),
('money_withdraw_accept','现金-审核出款',1,-1,-1,-1,1),
('money_withdraw_deal','现金-出款记录',1,-1,-1,-1,1),
('money_withdraw_select','现金-出款查询',1,-1,-1,-1,0),
('game_lottery_win','游戏管理-彩票赔率',1,-1,-1,-1,1),
('game_lottery_rebate','游戏管理-彩票返点',1,-1,-1,-1,1),
('game_lottery_bet','游戏管理-彩票投注额',1,-1,-1,-1,1),
('game_period_report','游戏管理-局数据',1,1,1,1,0),
('game_number','游戏管理-开奖结果',1,1,1,1,0),
('broker_layer_select','代理-层级-查看',1,0,0,0,1),
('broker_layer_insert','代理-层级-添加',1,-1,-1,-1,1),
('broker_layer_update','代理-层级-修改',1,-1,-1,-1,1),
('broker_layer_delete','代理-层级-删除',1,-1,-1,-1,1),
('broker_setting','代理-佣金设置',1,-1,-1,-1,1),
('broker_report','代理-佣金统计',1,1,1,1,0),
('broker_select','代理-佣金查询',1,1,1,1,0),
('broker_deliver','代理-佣金派发',1,-1,-1,-1,1),
('report_lottery','报表-统计报表',1,1,1,1,0),
('report_money','报表-经营统计',1,1,1,1,0),
('report_tax','报表-月结对账',1,-1,-1,-1,0),
('web_acceptable','网站管理-全站开关',1,-1,-1,-1,1),
('web_message','网站管理-消息',1,-1,-1,-1,1),
('web_homepage','网站管理-首页',1,-1,-1,-1,1),
('web_promotion','网站管理-活动',1,-1,-1,-1,1),
('promotion_setting','优惠活动-设置',1,-1,-1,-1,1),
('promotion_report','优惠活动-报表',1,-1,-1,-1,0),
('subsidy_setting','会员返水-返水设定',1,-1,-1,-1,1),
('subsidy_report','会员返水-查询',1,1,1,1,0),
('subsidy_deliver','会员返水-派发',1,-1,-1,-1,1);;

delete p from staff_permit p
    left join staff_info s on p.staff_id=s.staff_id
where s.staff_id is null;;
delete p from staff_permit p
    left join operate o on p.operate_key=o.operate_key
where o.operate_key is null;;
delete p from staff_permit p
    inner join staff_info s on p.staff_id=s.staff_id
    inner join operate o on p.operate_key=o.operate_key
where s.staff_grade=0 and o.owner_permit not in (0,1);;
delete p from staff_permit p
    inner join staff_info s on p.staff_id=s.staff_id
    inner join operate o on p.operate_key=o.operate_key
where s.staff_grade=1 and o.major_permit not in (0,1);;
delete p from staff_permit p
    inner join staff_info s on p.staff_id=s.staff_id
    inner join operate o on p.operate_key=o.operate_key
where s.staff_grade=2 and o.minor_permit not in (0,1);;
delete p from staff_permit p
    inner join staff_info s on p.staff_id=s.staff_id
    inner join operate o on p.operate_key=o.operate_key
where s.staff_grade=3 and o.agent_permit not in (0,1);;

insert ignore into staff_permit(staff_id,operate_key)
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
