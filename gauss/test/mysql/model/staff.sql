delimiter ;;

drop table if exists staff_session;;
create table staff_session(
    client_id varchar(32) not null comment '客户端连接id',
    staff_id int unsigned not null comment '管理员id',
    login_time int unsigned not null comment '登陆时间',
    client_ip int unsigned not null comment '客户端ip',
    user_agent varchar(40) not null comment 'sha1(user-agent)',
    resume_key varchar(40) not null comment '恢复key',
    lose_time int unsigned not null default 0 comment '掉线时间',
    index(staff_id),
    index(resume_key),
    primary key(client_id)
) comment '管理员登陆session';;

drop table if exists staff_deposit;;
create table staff_deposit(
    staff_deposit_id int unsigned auto_increment not null comment '手工存入操作id',
    staff_id int unsigned not null comment '员工id',
    deposit_type tinyint not null comment '存入项目：0-手工存入，1-取消出款，2-活动优惠',
    deposit_audit_multiple int not null comment '0-不计充值稽核，1-计算充值稽核',
    coupon_audit_multiple int not null comment '0-不计活动稽核，>0-活动稽核倍数',
    memo varchar(100) not null comment '备注',
    user_money_map json not null comment '{"user_id":"money"}',
    submit_time int unsigned not null comment '提交时间',
    finish_count int unsigned not null comment '完成总人数',
    finish_money decimal(14,2) not null comment '完成总金额',
    finish_time int unsigned not null comment '执行完成时间',
    primary key(staff_deposit_id)
) comment '手工存入';;

drop table if exists dividend_setting;;
create table dividend_setting(
    scope_staff_id int unsigned not null comment '有效范围',
    grade1_bet_rate decimal(6,4) not null comment '大股东投注分红比例',
    grade1_profit_rate decimal(6,4) not null comment '大股东损益分红比例',
    grade1_fee_rate decimal(6,4) not null comment '大股东行政费比例',
    grade1_tax_rate decimal(6,4) not null comment '大股东平台费比例',
    grade2_bet_rate decimal(6,4) not null comment '股东投注分红比例',
    grade2_profit_rate decimal(6,4) not null comment '股东损益分红比例',
    grade2_fee_rate decimal(6,4) not null comment '股东行政费比例',
    grade2_tax_rate decimal(6,4) not null comment '股东平台费比例',
    grade3_bet_rate decimal(6,4) not null comment '总代理投注分红比例',
    grade3_profit_rate decimal(6,4) not null comment '总代理损益分红比例',
    grade3_fee_rate decimal(6,4) not null comment '总代理行政费比例',
    grade3_tax_rate decimal(6,4) not null comment '总代理平台费比例',
    primary key(scope_staff_id)
) comment '体系分红设置';;

drop table if exists dividend_settle;;
create table dividend_settle(
    staff_id int unsigned not null comment '员工id',
    bet_amount decimal(14,2) not null comment '投注总额',
    profit_amount decimal(14,2) not null comment '损益总额',
    bet_rate decimal(6,4) not null comment '投注分红比例',
    profit_rate decimal(6,4) not null comment '损益分红比例',
    fee_rate decimal(6,4) not null comment '行政费比例',
    tax_rate decimal(6,4) not null comment '平台费比例',
    dividend_bet decimal(14,2) not null comment '投注分红',
    dividend_profit decimal(14,2) not null comment '损益分红',
    dividend_result decimal(14,2) not null comment '最终分红金额',
    deliver_time int unsigned not null comment '派发时间，0表示未派发',
    settle_time int unsigned not null comment '结算时间',
    primary key(staff_id,settle_time)
) comment '分红报表';;

drop table if exists lottery_game;;
create table lottery_game(
    model_key varchar(10) not null comment '彩票类型key',
    game_key varchar(20) not null comment '彩种key',
    official tinyint not null comment '是否官方彩',
    acceptable tinyint not null comment '开关',
    rebate_max decimal(4,2) unsigned not null comment '最大返点比例%',
    primary key(game_key)
) comment '彩票设置';;

drop table if exists lottery_game_play;;
create table lottery_game_play(
    game_key varchar(20) not null comment '彩种key',
    play_key varchar(20) not null comment '玩法key',
    acceptable tinyint not null default 1 comment '开关',
    bet_min decimal(14,2) unsigned not null comment '最小投注额',
    bet_max decimal(14,2) unsigned not null comment '最大投注额',
    primary key(game_key,play_key)
) comment '彩票玩法设置';;

drop table if exists lottery_game_win;;
create table lottery_game_win(
    game_key varchar(20) not null comment '彩种key',
    play_key varchar(20) not null comment '玩法key',
    win_key varchar(30) not null comment '赔率key',
    bonus_rate decimal(12,4) unsigned not null comment '赔率',
    primary key(game_key,win_key)
) comment '彩票赔率设置';;

drop table if exists staff_info;;
create table staff_info(
    staff_id int unsigned auto_increment not null comment '员工id',
    staff_name varchar(20) not null comment '员工姓名',
    staff_grade tinyint not null comment '账号等级： 0-站长，1-大股东，2-股东，3-总代理',
    master_id int unsigned not null default 0 comment '所属主账号id，0表示本身是主账号',
    leader_id int unsigned not null default 0 comment '上级账号id，主账号记录上级体系，子账号记录上级子账号',
    add_time int unsigned not null default 0 comment '添加时间',
    add_ip int unsigned not null default 0 comment '添加ip地址',
    login_time int unsigned not null default 0 comment '最后登陆时间',
    login_ip int unsigned not null default 0 comment '最后登陆ip地址',
    remove_time int unsigned not null default 0 comment '删除时间',
    remove_ip int unsigned not null default 0 comment '删除ip地址',
    primary key(staff_id)
) comment '员工基本信息';;

drop table if exists staff_permit;;
create table staff_permit(
    staff_id int unsigned not null comment '员工id',
    operate_key varchar(30) not null comment '操作key',
    primary key(staff_id,operate_key)
) comment '操作授权';;

drop table if exists staff_layer;;
create table staff_layer(
    staff_id int unsigned not null comment '员工id',
    layer_id int unsigned not null comment '层级id',
    primary key(staff_id,layer_id)
) comment '员工管理会员层级设置';;

drop table if exists staff_credit;;
create table staff_credit(
    staff_id int unsigned not null comment '员工id',
    deposit_limit decimal(14,2) not null comment '入款限额',
    withdraw_limit decimal(14,2) not null comment '出款限额',
    notify_status tinyint not null default 0 comment '通知状态：0-只接收额度范围内的通知，1-接收所有通知',
    primary key(staff_id)
) comment '员工授信额度';;

drop table if exists suggest;;
create table suggest(
    category_key varchar(10) not null comment '类型：lottery-彩票，video-真人视讯，game-电子游戏，sports-体育，cards-棋牌',
    game_key varchar(20) not null comment '彩种或外接口key',
    display_order int unsigned not null comment '显示顺序',
    is_popular tinyint not null default 1 comment '是否热门',
    to_home tinyint not null default 0 comment '推荐至首页',
    primary key(game_key)
) comment '彩票及外接口推荐';;

drop table if exists carousel;;
create table carousel(
    carousel_id int unsigned auto_increment not null comment '轮播图id',
    start_time int unsigned not null comment '开始时间',
    stop_time int unsigned not null comment '结束时间',
    add_time int unsigned not null comment '添加时间',
    img_src mediumtext not null comment '图片地址或base64',
    link_type varchar(20) not null comment '链接类型: webview-内嵌网页， browser-浏览器打开网页， bet-彩种投注页面， promotion-活动， layer_message-公告',
    link_data varchar(200) not null comment '链接地址/链接key/链接id',
    publish tinyint not null default 1 comment '启用',
    primary key(carousel_id)
) comment '轮播图';;

drop table if exists announcement;;
create table announcement(
    announcement_id int unsigned auto_increment not null comment '通知id',
    start_time int unsigned not null comment '开始时间',
    stop_time int unsigned not null comment '结束时间',
    add_time int unsigned not null comment '添加时间',
    content mediumtext not null comment '内容',
    publish tinyint not null default 1 comment '启用',
    primary key(announcement_id)
) comment '首页通知';;

drop table if exists promotion;;
create table promotion(
    promotion_id int unsigned auto_increment not null comment '活动id',
    title varchar(20) not null comment '标题',
    publish tinyint not null default 1 comment '启用',
    start_time int unsigned not null comment '开始时间',
    stop_time int unsigned not null comment '结束时间',
    add_time int unsigned not null comment '添加时间',
    cover mediumtext not null comment '封面',
    content mediumtext not null comment '内容',
    primary key(promotion_id)
) comment '活动';;

drop table if exists popup;;
create table popup(
    popup_id int unsigned auto_increment not null comment '弹窗id',
    content mediumtext not null comment '内容',
    publish tinyint not null default 1 comment '启用',
    start_time int unsigned not null comment '开始时间',
    stop_time int unsigned not null comment '结束时间',
    add_time int unsigned not null comment '添加时间',
    primary key(popup_id)
) comment '弹窗消息';;

drop table if exists deposit_route;;
create table deposit_route(
    route_id int unsigned auto_increment not null comment '路线id',
    passage_id int unsigned not null comment '通道id',
    min_money decimal(14,2) not null comment '最低入款',
    max_money decimal(14,2) not null comment '最高入款',
    coupon_rate decimal(4,2) not null comment '优惠比例',
    coupon_max decimal(14,2) not null comment '优惠金额上限',
    coupon_times int not null comment '优惠次数上限',
    coupon_audit_rate decimal(4,2) not null comment '优惠稽核倍数',
    acceptable tinyint not null comment '是否启用',
    primary key(route_id)
) comment '支付路线';;

drop table if exists deposit_route_layer;;
create table deposit_route_layer(
    route_id int unsigned not null comment '路线id',
    layer_id int unsigned not null comment '层级id',
    primary key(route_id,layer_id)
) comment '支付路线关联层级';;

drop table if exists deposit_route_way;;
create table deposit_route_way(
    route_id int unsigned not null comment '路线id',
    way_key varchar(10) not null comment '方式key',
    way_name varchar(20) not null comment '方式名字',
    primary key(route_id)
) comment '三方入款路线入款方式';;

drop table if exists deposit_passage;;
create table deposit_passage(
    passage_id int unsigned auto_increment not null comment '通道id',
    passage_name varchar(20) not null comment '通道名称',
    risk_control decimal(14,2) not null comment '风控金额',
    cumulate decimal(14,2) not null comment '目前存款',
    acceptable tinyint not null default 1 comment '启用',
    primary key(passage_id)
) comment '入款账户';;

drop table if exists deposit_passage_bank;;
create table deposit_passage_bank(
    passage_id int unsigned not null comment '通道id',
    bank_name varchar(20) not null comment '存入银行',
    bank_branch varchar(20) not null comment '存入开户网点',
    account_number varchar(20) not null comment '存入银行账号',
    account_name varchar(20) not null comment '存入开户名',
    primary key(passage_id)
) comment '公司入款银行账户';;

drop table if exists deposit_passage_gate;;
create table deposit_passage_gate(
    passage_id int unsigned not null comment '通道id',
    gate_key varchar(10) not null comment '三方key',
    gate_name varchar(20) not null comment '三方名字',
    api_url varchar(200) not null default '' comment '网关地址',
    account_number varchar(100) not null comment '商户号',
    account_name varchar(100) not null comment '商户名称',
    jump_url varchar(200) not null comment '商城网址',
    signature_key text not null comment '签名key',
    encrypt_key text not null comment '加密key',
    primary key(passage_id)
) comment '三方入款账户';;

drop table if exists deposit_passage_simple;;
create table deposit_passage_simple(
    passage_id int unsigned not null comment '通道id',
    pay_url varchar(200) not null comment '支付网址',
    primary key(passage_id)
) comment '快捷入款地址';;

drop table if exists staff_auth;;
create table staff_auth(
    staff_id int unsigned not null comment '员工id',
    staff_key varchar(20) not null comment '登陆名',
    password_salt varchar(40) not null comment '密码hash盐',
    password_hash varchar(40) not null comment '密码的hash',
    unique(staff_key),
    primary key(staff_id)
) comment '员工登陆信息';;

drop table if exists site_setting;;
create table site_setting(
    setting_key varchar(30) not null comment '设置key',
    description varchar(30) not null comment '说明',
    data_type tinyint not null comment '0-整数，1-双精度，2-字符',
    int_value int not null default 0 comment '整数值',
    dbl_value decimal(20,8) not null default 0 comment '双精度值',
    str_value varchar(1000) not null default '' comment '字符值',
    primary key(setting_key)
) comment '站点零散设置';;

drop table if exists operate;;
create table operate(
    operate_key varchar(30) not null comment '操作key',
    operate_name varchar(20) not null comment '操作名称',
    owner_permit tinyint not null comment '0：站长及其子账号可用；1：站长可用，站长子账号需要授权',
    major_permit tinyint not null comment '-1：大股东不可用；0：大股东及其子账号可用；1：大股东可用，大股东子账号需要授权；2：大股东及子账号需要授权',
    minor_permit tinyint not null comment '-1：股东不可用；0：股东及其子账号可用；1：股东可用，股东子账号需要授权；2：股东及子账号需要授权',
    agent_permit tinyint not null comment '-1：总代不可用；0：总代及其子账号可用；1：总代可用，总代子账号需要授权；2：总代及子账号需要授权',
    record_log tinyint not null default 1 comment '是否需要记录日志',
    display_order int unsigned auto_increment not null unique,
    primary key(operate_key)
) comment '操作';;

drop table if exists operate_log;;
create table operate_log(
    log_id bigint unsigned auto_increment not null comment '日志id',
    staff_id int unsigned not null comment '管理员id',
    operate_key varchar(30) not null comment '操作key',
    detail text not null comment '操作详情',
    client_ip int unsigned not null comment '客户端ip',
    log_time int unsigned not null default 0 comment '日志记录时间',
    index(staff_id,log_time),
    primary key(log_id)
) comment '操作日志';;

drop table if exists staff_struct_owner;;
create table staff_struct_owner(
    owner_id int unsigned not null comment '站长id',
    owner_name varchar(20) not null comment '站长姓名',
    slave_count int unsigned not null comment '子账号人数',
    major_count int unsigned not null comment '下级大股东人数',
    minor_count int unsigned not null comment '下级股东人数',
    agent_count int unsigned not null comment '下级总代理人数',
    primary key(owner_id)
) comment '站长主账号结构信息';;

drop table if exists staff_struct_major;;
create table staff_struct_major(
    owner_id int unsigned not null comment '所属站长id',
    owner_name varchar(20) not null comment '所属站长姓名',
    major_id int unsigned not null comment '大股东id',
    major_name varchar(20) not null comment '大股东姓名',
    slave_count int unsigned not null comment '子账号人数',
    minor_count int unsigned not null comment '下级股东人数',
    agent_count int unsigned not null comment '下级总代理人数',
    unique(owner_id,major_id),
    primary key(major_id)
) comment '大股东主账号结构信息';;

drop table if exists staff_struct_minor;;
create table staff_struct_minor(
    owner_id int unsigned not null comment '所属站长id',
    owner_name varchar(20) not null comment '所属站长姓名',
    major_id int unsigned not null comment '所属大股东id',
    major_name varchar(20) not null comment '所属大股东姓名',
    minor_id int unsigned not null comment '股东id',
    minor_name varchar(20) not null comment '股东姓名',
    slave_count int unsigned not null comment '子账号人数',
    agent_count int unsigned not null comment '下级总代理人数',
    unique(owner_id,major_id,minor_id),
    unique(major_id,minor_id),
    primary key(minor_id)
) comment '股东主账号结构信息';;

drop table if exists staff_struct_agent;;
create table staff_struct_agent(
    owner_id int unsigned not null comment '所属站长id',
    owner_name varchar(20) not null comment '所属站长姓名',
    major_id int unsigned not null comment '所属大股东id',
    major_name varchar(20) not null comment '所属大股东姓名',
    minor_id int unsigned not null comment '所属股东id',
    minor_name varchar(20) not null comment '所属股东姓名',
    agent_id int unsigned not null comment '总代理id',
    agent_name varchar(20) not null comment '总代理姓名',
    slave_count int unsigned not null comment '子账号人数',
    unique(owner_id,major_id,minor_id,agent_id),
    unique(major_id,minor_id,agent_id),
    unique(minor_id,agent_id),
    primary key(agent_id)
) comment '总代理主账号结构信息';;

drop table if exists staff_struct_slave;;
create table staff_struct_slave(
    master_id int unsigned not null comment '主账号id',
    master_name varchar(20) not null comment '主账号姓名',
    superior_degree tinyint unsigned not null comment '上级子账号级别',
    superior_id int unsigned not null comment '上级子账号id',
    superior_name varchar(20) not null comment '上级子账号姓名',
    underling_degree tinyint unsigned not null comment '下级子账号级别',
    underling_id int unsigned not null comment '下级子账号id',
    underling_name varchar(20) not null comment '下级子账号姓名',
    index(master_id,underling_id),
    index(underling_id,superior_degree,superior_id),
    index(superior_id,underling_degree,underling_id),
    primary key(superior_id,underling_id)
) comment '子账号结构信息';;

drop table if exists staff_withdraw;;
create table staff_withdraw(
    staff_withdraw_id int unsigned auto_increment not null comment '手工提出操作id',
    staff_id int unsigned not null comment '员工id',
    withdraw_type tinyint not null comment '提出项目：0-手工提出，1-入款存误，2-扣除非法下注派彩，3-放弃存款优惠，4-其他出款',
    deposit_audit_multiple int not null comment '0-不计充值稽核，1-计算充值稽核',
    coupon_audit_multiple int not null comment '0-不计活动稽核，>0-活动稽核倍数',
    memo varchar(100) not null comment '备注',
    user_money_map json not null comment '{"user_id":"money"}',
    submit_time int unsigned not null comment '提交时间',
    finish_count int unsigned not null comment '完成总人数',
    finish_money decimal(14,2) not null comment '完成总金额',
    finish_time int unsigned not null comment '执行完成时间',
    primary key(staff_withdraw_id)
) comment '手工提出';;

drop table if exists external_game;;
create table external_game(
    category_key varchar(10) not null comment '类型：video-真人视讯，game-电子游戏，sports-体育，cards-棋牌',
    interface_key varchar(10) not null comment '外接口： fg-FunGaming，ky-开元棋牌，lb-Lebo体育，ag-AsiaGaming',
    game_key varchar(20) not null comment '外接口key',
    acceptable tinyint not null default 1 comment '开关',
    primary key(game_key)
) comment '外接口设置';;

drop table if exists staff_bind_ip;;
create table staff_bind_ip(
    staff_id int unsigned not null comment '员工id',
    bind_ip int unsigned not null comment '绑定ip',
    add_time int unsigned not null comment '创建时间',
    index(bind_ip),
    primary key(staff_id,bind_ip)
) comment '员工绑定ip';;

drop procedure if exists staff_session_lose;;
create procedure staff_session_lose(
    _client_id varchar(32)
)
begin
    update staff_session set lose_time=unix_timestamp() where client_id=_client_id;
end;;

drop procedure if exists staff_auth_verify;;
create procedure staff_auth_verify(
    _staff_key varchar(20),
    _password varchar(40)
) comment '验证员工登陆信息'
begin
    select staff_id
        from staff_auth
        where staff_key=_staff_key and password_hash = sha1(concat(password_salt,sha1(_password)));
end;;

drop procedure if exists _staff_struct_insert;;
create procedure _staff_struct_insert(
    _staff_id int unsigned,
    _staff_name varchar(20),
    _staff_grade tinyint,
    _master_id int unsigned,
    _leader_id int unsigned
)
begin
    if 0=_master_id then
        case _staff_grade
            when 0 then begin
                insert into staff_struct_owner set owner_id=_staff_id,owner_name=_staff_name,slave_count=0,
                    major_count=0,minor_count=0,agent_count=0;
            end;
            when 1 then begin
                declare _owner_id int unsigned;
                declare _owner_name varchar(20);
                select staff_id,staff_name,leader_id into _owner_id,_owner_name,_leader_id
                    from staff_info
                    where staff_id=_leader_id;
                insert into staff_struct_major set major_id=_staff_id,major_name=_staff_name,slave_count=0,
                    owner_id=_owner_id,owner_name=_owner_name,
                    minor_count=0,agent_count=0;
                update staff_struct_owner set major_count=major_count+1
                    where owner_id=_owner_id;
            end;
            when 2 then begin
                declare _owner_id,_major_id int unsigned;
                declare _owner_name,_major_name varchar(20);
                select staff_id,staff_name,leader_id into _major_id,_major_name,_leader_id
                    from staff_info
                    where staff_id=_leader_id;
                select staff_id,staff_name,leader_id into _owner_id,_owner_name,_leader_id
                    from staff_info
                    where staff_id=_leader_id;
                insert into staff_struct_minor set minor_id=_staff_id,minor_name=_staff_name,slave_count=0,
                    owner_id=_owner_id,owner_name=_owner_name,
                    major_id=_major_id,major_name=_major_name,
                    agent_count=0;
                update staff_struct_owner set minor_count=minor_count+1
                    where owner_id=_owner_id;
                update staff_struct_major set minor_count=minor_count+1
                    where major_id=_major_id;
            end;
            when 3 then begin
                declare _owner_id,_major_id,_minor_id int unsigned;
                declare _owner_name,_major_name,_minor_name varchar(20);
                select staff_id,staff_name,leader_id into _minor_id,_minor_name,_leader_id
                    from staff_info
                    where staff_id=_leader_id;
                select staff_id,staff_name,leader_id into _major_id,_major_name,_leader_id
                    from staff_info
                    where staff_id=_leader_id;
                select staff_id,staff_name,leader_id into _owner_id,_owner_name,_leader_id
                    from staff_info
                    where staff_id=_leader_id;
                insert into staff_struct_agent set agent_id=_staff_id,agent_name=_staff_name,slave_count=0,
                    owner_id=_owner_id,owner_name=_owner_name,
                    major_id=_major_id,major_name=_major_name,
                    minor_id=_minor_id,minor_name=_minor_name;
                update staff_struct_owner set agent_count=agent_count+1
                    where owner_id=_owner_id;
                update staff_struct_major set agent_count=agent_count+1
                    where major_id=_major_id;
                update staff_struct_minor set agent_count=agent_count+1
                    where minor_id=_minor_id;
            end;
        end case;
        insert into staff_struct_slave set master_id=_staff_id,master_name=_staff_name,
            superior_degree=0,superior_id=_staff_id,superior_name=_staff_name,
            underling_degree=0,underling_id=_staff_id,underling_name=_staff_name;
    else
        insert into staff_struct_slave(
            master_id,master_name,superior_degree,superior_id,superior_name,underling_degree,underling_id,underling_name
        )
        select master_id,master_name,superior_degree,superior_id,superior_name,underling_degree+1,_staff_id,_staff_name
            from staff_struct_slave where underling_id=_leader_id
        union all
        select master_id,master_name,underling_degree+1,_staff_id,_staff_name,underling_degree+1,_staff_id,_staff_name
            from staff_struct_slave where (superior_id,underling_id)=(_master_id,_leader_id);
        case _staff_grade
            when 0 then
                update staff_struct_owner set slave_count=slave_count+1 where owner_id=_master_id;
            when 1 then
                update staff_struct_major set slave_count=slave_count+1 where major_id=_master_id;
            when 2 then
                update staff_struct_minor set slave_count=slave_count+1 where minor_id=_master_id;
            when 3 then
                update staff_struct_agent set slave_count=slave_count+1 where agent_id=_master_id;
        end case;
    end if;
end;;

drop procedure if exists _staff_struct_update_name;;
create procedure _staff_struct_update_name(
    _staff_id int unsigned,
    _staff_name varchar(20),
    _staff_grade tinyint,
    _master_id int unsigned
)
begin
    if 0=_master_id then
        case _staff_grade
            when 0 then
                update staff_struct_owner set owner_name=_staff_name where owner_id=_staff_id;
                update staff_struct_major set owner_name=_staff_name where owner_id=_staff_id;
                update staff_struct_minor set owner_name=_staff_name where owner_id=_staff_id;
                update staff_struct_agent set owner_name=_staff_name where owner_id=_staff_id;
            when 1 then
                update staff_struct_major set major_name=_staff_name where major_id=_staff_id;
                update staff_struct_minor set major_name=_staff_name where major_id=_staff_id;
                update staff_struct_agent set major_name=_staff_name where major_id=_staff_id;
            when 2 then
                update staff_struct_minor set minor_name=_staff_name where minor_id=_staff_id;
                update staff_struct_agent set minor_name=_staff_name where minor_id=_staff_id;
            when 3 then
                update staff_struct_agent set agent_name=_staff_name where agent_id=_staff_id;
        end case;
    end if;
    update staff_struct_slave set master_name=_staff_name where master_id=_master_id;
    update staff_struct_slave set superior_name=_staff_name where superior_id=_staff_id;
    update staff_struct_slave set underling_name=_staff_name where underling_id=_staff_id;
end;;

drop procedure if exists _staff_struct_update_leader;;
create procedure _staff_struct_update_leader(
    _staff_id int unsigned,
    _staff_grade tinyint,
    _master_id int unsigned,
    _leader_id int unsigned
)
begin
    if 0=_master_id then
        case _staff_grade
            when 0 then begin
            end;
            when 1 then begin
                declare _owner_id,_minor_count,_agent_count int unsigned;
                declare _owner_name varchar(20);
                select owner_id,minor_count,agent_count into _owner_id,_minor_count,_agent_count
                    from staff_struct_major where major_id=_staff_id;
                update staff_struct_owner
                    set major_count=major_count-1,minor_count=minor_count-_minor_count,agent_count=agent_count-_agent_count
                    where owner_id=_owner_id;
                select owner_id,owner_name into _owner_id,_owner_name
                    from staff_struct_owner where owner_id=_leader_id;
                update staff_struct_owner
                    set major_count=major_count+1,minor_count=minor_count+_minor_count,agent_count=agent_count+_agent_count
                    where owner_id=_owner_id;
                update staff_struct_major
                    set owner_id=_owner_id,owner_name=_owner_name
                    where major_id=_staff_id;
                update staff_struct_minor
                    set owner_id=_owner_id,owner_name=_owner_name
                    where major_id=_staff_id;
                update staff_struct_agent
                    set owner_id=_owner_id,owner_name=_owner_name
                    where major_id=_staff_id;
            end;
            when 2 then begin
                declare _owner_id,_major_id,_agent_count int unsigned;
                declare _owner_name,_major_name varchar(20);
                select owner_id,major_id,agent_count into _owner_id,_major_id,_agent_count
                    from staff_struct_minor where minor_id=_staff_id;
                update staff_struct_owner
                    set minor_count=minor_count-1,agent_count=agent_count-_agent_count
                    where owner_id=_owner_id;
                update staff_struct_major
                    set minor_count=minor_count-1,agent_count=agent_count-_agent_count
                    where major_id=_major_id;
                select owner_id,owner_name,major_id,major_name into _owner_id,_owner_name,_major_id,_major_name
                    from staff_struct_major where major_id=_leader_id;
                update staff_struct_owner
                    set minor_count=minor_count+1,agent_count=agent_count+_agent_count
                    where owner_id=_owner_id;
                update staff_struct_major
                    set minor_count=minor_count+1,agent_count=agent_count+_agent_count
                    where major_id=_major_id;
                update staff_struct_minor
                    set owner_id=_owner_id,owner_name=_owner_name,major_id=_major_id,major_name=_major_name
                    where minor_id=_staff_id;
                update staff_struct_agent
                    set owner_id=_owner_id,owner_name=_owner_name,major_id=_major_id,major_name=_major_name
                    where minor_id=_staff_id;
            end;
            when 3 then begin
                declare _owner_id,_major_id,_minor_id int unsigned;
                declare _owner_name,_major_name,_minor_name varchar(20);
                select owner_id,major_id,minor_id into _owner_id,_major_id,_minor_id
                    from staff_struct_agent where agent_id=_staff_id;
                update staff_struct_owner set agent_count=agent_count-1 where owner_id=_owner_id;
                update staff_struct_major set agent_count=agent_count-1 where major_id=_major_id;
                update staff_struct_minor set agent_count=agent_count-1 where minor_id=_minor_id;
                select owner_id,owner_name,major_id,major_name,minor_id,minor_name
                    into _owner_id,_owner_name,_major_id,_major_name,_minor_id,_minor_name
                    from staff_struct_minor where minor_id=_leader_id;
                update staff_struct_owner set agent_count=agent_count+1 where owner_id=_owner_id;
                update staff_struct_major set agent_count=agent_count+1 where major_id=_major_id;
                update staff_struct_minor set agent_count=agent_count+1 where minor_id=_minor_id;
                update staff_struct_agent
                    set owner_id=_owner_id,major_id=_major_id,minor_id=_minor_id,
                        owner_name=_owner_name,major_name=_major_name,minor_name=_minor_name
                    where agent_id=_staff_id;
            end;
        end case;
    else begin
        declare _old_degree,_new_degree tinyint;
        declare _staff_name varchar(20);
        select underling_degree,underling_name into _old_degree,_staff_name
            from staff_struct_slave where (superior_id,underling_id)=(_master_id,_staff_id);
        select underling_degree+1 into _new_degree
            from staff_struct_slave where (superior_id,underling_id)=(_master_id,_leader_id);
        delete r from staff_struct_slave r
            inner join staff_struct_slave s on r.superior_id=s.superior_id
            inner join staff_struct_slave u on r.underling_id=u.underling_id
            where s.underling_id=_staff_id and s.superior_id!=_staff_id and u.superior_id=_staff_id;
        update staff_struct_slave
            set superior_degree=_new_degree,underling_degree=underling_degree-_old_degree+_new_degree
            where superior_id=_staff_id;
        insert into staff_struct_slave(
            master_id,master_name,superior_degree,superior_id,superior_name,underling_degree,underling_id,underling_name
        )
        select master_id,master_name,superior_degree,superior_id,superior_name,underling_degree+1,_staff_id,_staff_name
            from staff_struct_slave where underling_id=_leader_id;
        insert into staff_struct_slave(
            master_id,master_name,superior_degree,superior_id,superior_name,underling_degree,underling_id,underling_name
        )
        select s.master_id,s.master_name,s.superior_degree,s.superior_id,s.superior_name,u.underling_degree,u.underling_id,u.underling_name
            from staff_struct_slave s inner join staff_struct_slave u
            where s.underling_id=_staff_id and s.superior_id!=_staff_id and u.superior_id=_staff_id and u.underling_id!=_staff_id;
    end;
    end if;
end;;

drop procedure if exists _staff_struct_delete;;
create procedure _staff_struct_delete(
    _staff_id int unsigned,
    _staff_grade tinyint,
    _master_id int unsigned
)
begin
    if exists (select * from staff_struct_slave where superior_id=_staff_id and underling_id!=_staff_id) then
        signal sqlstate 'SSDS0' set message_text='staff_struct_delete: 还有下级子账号，不能删除';
    end if;
    if 0=_master_id then
        case _staff_grade
            when 0 then begin
                declare _major_count,_minor_count,_agent_count int unsigned;
                select major_count,minor_count,agent_count into _major_count,_minor_count,_agent_count
                    from staff_struct_owner where owner_id=_staff_id;
                if 0<_major_count or 0<_minor_count or 0<_agent_count then
                    signal sqlstate 'SSDM0' set message_text='staff_struct_delete: 还有下级大股东，不能删除';
                end if;
                delete from staff_struct_owner where owner_id=_staff_id;
            end;
            when 1 then begin
                declare _owner_id,_minor_count,_agent_count int unsigned;
                select owner_id,minor_count,agent_count into _owner_id,_minor_count,_agent_count
                    from staff_struct_major where major_id=_staff_id;
                if 0<_minor_count or 0<_agent_count then
                    signal sqlstate 'SSDM1' set message_text='staff_struct_delete: 还有下级股东，不能删除';
                end if;
                delete from staff_struct_major where major_id=_staff_id;
                update staff_struct_owner set major_count=major_count-1 where owner_id=_owner_id;
            end;
            when 2 then begin
                declare _owner_id,_major_id,_agent_count int unsigned;
                select owner_id,major_id,agent_count into _owner_id,_major_id,_agent_count
                    from staff_struct_minor where minor_id=_staff_id;
                if 0<_agent_count then
                    signal sqlstate 'SSDM2' set message_text='staff_struct_delete: 还有下级总代理，不能删除';
                end if;
                delete from staff_struct_minor where minor_id=_staff_id;
                update staff_struct_owner set minor_count=minor_count-1 where owner_id=_owner_id;
                update staff_struct_major set minor_count=minor_count-1 where major_id=_major_id;
            end;
            when 3 then begin
                declare _owner_id,_major_id,_minor_id int unsigned;
                select owner_id,major_id,minor_id into _owner_id,_major_id,_minor_id
                    from staff_struct_agent where agent_id=_staff_id;
                delete from staff_struct_agent where agent_id=_staff_id;
                update staff_struct_owner set agent_count=agent_count-1 where owner_id=_owner_id;
                update staff_struct_major set agent_count=agent_count-1 where major_id=_major_id;
                update staff_struct_minor set agent_count=agent_count-1 where minor_id=_minor_id;
            end;
        end case;
    else
        delete from staff_struct_slave where underling_id=_staff_id;
        case _staff_grade
            when 0 then
                update staff_struct_owner set slave_count=slave_count-1 where owner_id=_master_id;
            when 1 then
                update staff_struct_major set slave_count=slave_count-1 where major_id=_master_id;
            when 2 then
                update staff_struct_minor set slave_count=slave_count-1 where minor_id=_master_id;
            when 3 then
                update staff_struct_agent set slave_count=slave_count-1 where agent_id=_master_id;
        end case;
    end if;
end;;

drop trigger if exists staff_session_insert;;
create trigger staff_session_insert before insert on staff_session for each row
begin
    set new.resume_key=sha1(random_bytes(40));
    set new.login_time=unix_timestamp();
end;;

drop trigger if exists staff_deposit_insert;;
create trigger staff_deposit_insert before insert on staff_deposit for each row
begin
    set new.submit_time=unix_timestamp();
end;;

drop trigger if exists staff_deposit_update;;
create trigger staff_deposit_update before update on staff_deposit for each row
begin
    set new.finish_time=unix_timestamp();
end;;

drop trigger if exists dividend_setting_update;;
create trigger dividend_setting_update before update on dividend_setting for each row
begin
    if old.scope_staff_id != new.scope_staff_id then
        signal sqlstate 'DSU01' set message_text='dividend_setting_update: 禁止修改 scope_staff_id';
    end if;
end;;

drop trigger if exists dividend_settle_update;;
create trigger dividend_settle_update before update on dividend_settle for each row
begin
    if old.staff_id != new.staff_id then
        signal sqlstate 'DSI01' set message_text='dividend_settle_update: 禁止修改 staff_id';
    end if;
end;;

drop trigger if exists staff_info_insert;;
create trigger staff_info_insert after insert on staff_info for each row
begin
    if new.staff_grade not between 0 and 3 then
        signal sqlstate 'SII01' set message_text='staff_info_insert: staff_grade 超出范围(0-3)';
    end if;
    call _staff_struct_insert(new.staff_id,new.staff_name,new.staff_grade,new.master_id,new.leader_id);
    case new.staff_grade
        when 0 then
            insert into staff_credit set staff_id=new.staff_id,deposit_limit=99999999,withdraw_limit=99999999;
        else
            insert into staff_credit set staff_id=new.staff_id,deposit_limit=0,withdraw_limit=0;
    end case;
end;;

drop trigger if exists staff_info_update;;
create trigger staff_info_update after update on staff_info for each row
begin
    if new.staff_id != old.staff_id then
        signal sqlstate 'SIU01' set message_text='staff_info_update: 禁止修改 staff_id';
    end if;
    if new.staff_name != old.staff_name then
        call _staff_struct_update_name(new.staff_id,new.staff_name,new.staff_grade,new.master_id);
    end if;
    if new.staff_grade != old.staff_grade then
        signal sqlstate 'SIU01' set message_text='staff_info_update: 禁止修改 staff_grade';
    end if;
    if new.master_id != old.master_id then
        signal sqlstate 'SIU01' set message_text='staff_info_update: 禁止修改 master_id';
    end if;
    if new.leader_id != old.leader_id then
        call _staff_struct_update_leader(new.staff_id,new.staff_grade,new.master_id,new.leader_id);
    end if;
end;;

drop trigger if exists staff_info_delete;;
create trigger staff_info_delete after delete on staff_info for each row
begin
    call _staff_struct_delete(old.staff_id,old.staff_grade,old.master_id);
    delete from staff_credit where staff_id=old.staff_id;
end;;

drop trigger if exists staff_credit_update;;
create trigger staff_credit_update before update on staff_credit for each row
begin
    if new.staff_id != old.staff_id then
        signal sqlstate 'SCU01' set message_text='staff_credit_update: 禁止修改 staff_id';
    end if;
end;;

drop trigger if exists staff_auth_insert;;
create trigger staff_auth_insert before insert on staff_auth for each row
begin
    set new.password_salt=sha1(random_bytes(40));
    set new.password_hash=sha1(concat(new.password_salt,sha1(new.password_hash)));
end;;

drop trigger if exists staff_auth_update;;
create trigger staff_auth_update before update on staff_auth for each row
begin
    if new.staff_id != old.staff_id then
        signal sqlstate 'SAU01' set message_text='staff_auth_update: 禁止修改 staff_id';
    end if;
    if new.password_hash != old.password_hash then
        set new.password_salt=sha1(random_bytes(40));
        set new.password_hash=sha1(concat(new.password_salt,sha1(new.password_hash)));
    end if;
end;;

drop trigger if exists operate_log_insert;;
create trigger operate_log_insert before insert on operate_log for each row
begin
    set new.log_time=unix_timestamp();
end;;

drop trigger if exists staff_withdraw_insert;;
create trigger staff_withdraw_insert before insert on staff_withdraw for each row
begin
    set new.submit_time=unix_timestamp();
end;;

drop trigger if exists staff_withdraw_update;;
create trigger staff_withdraw_update before update on staff_withdraw for each row
begin
    set new.finish_time=unix_timestamp();
end;;

drop view if exists dividend_settle_major;;
create view dividend_settle_major as
    select a.staff_key,s.major_id,s.major_name,
        d.bet_amount,d.bet_rate,d.dividend_bet,d.profit_amount,d.profit_rate,d.dividend_profit,
        d.fee_rate,d.tax_rate,d.dividend_result,d.deliver_time,d.settle_time
    from dividend_settle d inner join staff_struct_major s on d.staff_id=s.major_id
        inner join staff_auth a on d.staff_id=a.staff_id;;

drop view if exists dividend_settle_minor;;
create view dividend_settle_minor as
    select a.staff_key,s.major_id,s.major_name,s.minor_id,s.minor_name,
        d.bet_amount,d.bet_rate,d.dividend_bet,d.profit_amount,d.profit_rate,d.dividend_profit,
        d.fee_rate,d.tax_rate,d.dividend_result,d.deliver_time,d.settle_time
    from dividend_settle d inner join staff_struct_minor s on d.staff_id=s.minor_id
        inner join staff_auth a on d.staff_id=a.staff_id;;

drop view if exists dividend_settle_agent;;
create view dividend_settle_agent as
    select a.staff_key,s.major_id,s.major_name,s.minor_id,s.minor_name,s.agent_id,s.agent_name,
        d.bet_amount,d.bet_rate,d.dividend_bet,d.profit_amount,d.profit_rate,d.dividend_profit,
        d.fee_rate,d.tax_rate,d.dividend_result,d.deliver_time,d.settle_time
    from dividend_settle d inner join staff_struct_agent s on d.staff_id=s.agent_id
        inner join staff_auth a on d.staff_id=a.staff_id;;

drop view if exists staff_info_intact;;
create view staff_info_intact as
    select i.staff_id,i.staff_name,i.staff_grade,i.master_id,i.leader_id,
        i.add_time,i.add_ip,i.login_time,i.login_ip,i.remove_time,i.remove_ip,
        a.staff_key,c.deposit_limit,c.withdraw_limit,c.notify_status,
        json_arrayagg(l.layer_id) layer_id_list
    from staff_info i inner join staff_auth a on i.staff_id=a.staff_id
        left join staff_credit c on i.staff_id=c.staff_id
        left join staff_layer l on i.staff_id=l.staff_id
    group by staff_id;;

drop view if exists deposit_route_bank_intact;;
create view deposit_route_bank_intact as
    select r.route_id,r.passage_id,r.min_money,r.max_money,r.coupon_rate,r.coupon_max,r.coupon_times,r.coupon_audit_rate,r.acceptable,
        p.passage_name,p.risk_control,p.cumulate,p.acceptable as passage_acceptable,
        b.bank_name,b.bank_branch,b.account_number,b.account_name,
        group_concat(layer_id) as layer_id_list
    from deposit_route r inner join deposit_passage p on r.passage_id=p.passage_id
        inner join deposit_passage_bank b on p.passage_id=b.passage_id
        left join deposit_route_layer l on r.route_id=l.route_id
    group by r.route_id;;

drop view if exists deposit_route_gateway_intact;;
create view deposit_route_gateway_intact as
    select r.route_id,r.passage_id,r.min_money,r.max_money,r.acceptable,
        p.passage_name,p.risk_control,p.cumulate,p.acceptable as passage_acceptable,
        g.gate_key,g.gate_name,g.account_number,g.account_name,
        w.way_key,w.way_name,
        group_concat(distinct layer_id) as layer_id_list
    from deposit_route r inner join deposit_passage p on r.passage_id=p.passage_id
        inner join deposit_passage_gate g on p.passage_id=g.passage_id
        inner join deposit_route_way w on r.route_id=w.route_id
        left join deposit_route_layer l on r.route_id=l.route_id
    group by r.route_id;;

drop view if exists deposit_route_simple_intact;;
create view deposit_route_simple_intact as
    select r.route_id,r.passage_id,r.min_money,r.max_money,r.acceptable,
        p.passage_name,p.risk_control,p.cumulate,p.acceptable as passage_acceptable,
        s.pay_url,
        group_concat(distinct layer_id) as layer_id_list
    from deposit_route r inner join deposit_passage p on r.passage_id=p.passage_id
        inner join deposit_passage_simple s on p.passage_id=s.passage_id
        left join deposit_route_layer l on r.route_id=l.route_id
    group by r.route_id;;

drop view if exists deposit_passage_bank_intact;;
create view deposit_passage_bank_intact as
    select a.passage_id,a.passage_name,a.risk_control,a.cumulate,a.acceptable,
        b.bank_name,b.bank_branch,b.account_number,b.account_name
    from deposit_passage a inner join deposit_passage_bank b on a.passage_id=b.passage_id;;

drop view if exists deposit_passage_gate_intact;;
create view deposit_passage_gate_intact as
    select a.passage_id,a.passage_name,a.risk_control,a.cumulate,a.acceptable,
        g.gate_key,g.gate_name,g.api_url,g.account_number,g.account_name,g.jump_url,g.signature_key,g.encrypt_key
    from deposit_passage a inner join deposit_passage_gate g on a.passage_id=g.passage_id;;

drop view if exists deposit_passage_simple_intact;;
create view deposit_passage_simple_intact as
    select a.passage_id,a.passage_name,a.risk_control,a.cumulate,a.acceptable,
        s.pay_url
    from deposit_passage a inner join deposit_passage_simple s on a.passage_id=s.passage_id;;

drop view if exists operate_log_intact;;
create view operate_log_intact as
    select o.log_id,i.staff_id,i.staff_name,i.staff_grade,o.operate_key,o.detail,o.client_ip,o.log_time,
        i.master_id,m.staff_name as master_name,i.leader_id,l.staff_name as leader_name
    from operate_log o inner join staff_info i on o.staff_id=i.staff_id
        left outer join staff_info m on i.master_id=m.staff_id
        left outer join staff_info l on i.leader_id=l.staff_id;;

insert into dividend_setting(scope_staff_id,
    grade1_bet_rate,grade1_profit_rate,grade1_fee_rate,grade1_tax_rate,
    grade2_bet_rate,grade2_profit_rate,grade2_fee_rate,grade2_tax_rate,
    grade3_bet_rate,grade3_profit_rate,grade3_fee_rate,grade3_tax_rate
)values
(1,0.1,1,30,20,0.2,2,20,15,0.3,3,10,10);;

insert into staff_auth(staff_id,staff_key,password_hash)values
(1,'owner','123456');;

insert into staff_info(staff_id,staff_name,staff_grade,master_id,leader_id)values
(1,'站长',0,0,0);;

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

