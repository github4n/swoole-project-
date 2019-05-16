delimiter ;;

drop trigger if exists monthly_staff_insert;;
drop trigger if exists monthly_staff_update;;
drop trigger if exists monthly_staff_delete;;
drop trigger if exists monthly_staff_lottery_insert;;
drop trigger if exists monthly_staff_lottery_update;;
drop trigger if exists monthly_staff_lottery_delete;;
drop trigger if exists monthly_staff_external_insert;;
drop trigger if exists monthly_staff_external_update;;
drop trigger if exists monthly_staff_external_delete;;
drop trigger if exists lottery_period_insert;;
drop trigger if exists lottery_period_update;;
drop trigger if exists lottery_period_delete;;
drop trigger if exists daily_staff_insert;;
drop trigger if exists daily_staff_update;;
drop trigger if exists daily_staff_delete;;
drop trigger if exists daily_staff_lottery_insert;;
drop trigger if exists daily_staff_lottery_update;;
drop trigger if exists daily_staff_lottery_delete;;
drop trigger if exists daily_staff_external_insert;;
drop trigger if exists daily_staff_external_update;;
drop trigger if exists daily_staff_external_delete;;
drop trigger if exists monthly_user_insert;;
drop trigger if exists monthly_user_update;;
drop trigger if exists monthly_user_delete;;
drop trigger if exists monthly_user_lottery_insert;;
drop trigger if exists monthly_user_lottery_update;;
drop trigger if exists monthly_user_lottery_delete;;
drop trigger if exists monthly_user_external_insert;;
drop trigger if exists monthly_user_external_update;;
drop trigger if exists monthly_user_external_delete;;
drop trigger if exists daily_user_brokerage_insert;;
drop trigger if exists daily_user_brokerage_update;;
drop trigger if exists daily_user_brokerage_delete;;
drop trigger if exists weekly_user_brokerage_insert;;
drop trigger if exists weekly_user_brokerage_update;;
drop trigger if exists weekly_user_brokerage_delete;;
drop trigger if exists monthly_user_brokerage_insert;;
drop trigger if exists monthly_user_brokerage_update;;
drop trigger if exists monthly_user_brokerage_delete;;
drop trigger if exists user_cumulate_insert;;
drop trigger if exists user_cumulate_update;;
drop trigger if exists user_cumulate_delete;;
drop trigger if exists weekly_user_insert;;
drop trigger if exists weekly_user_delete;;
drop trigger if exists weekly_user_lottery_insert;;
drop trigger if exists weekly_user_lottery_delete;;
drop trigger if exists weekly_user_external_insert;;
drop trigger if exists weekly_user_external_update;;
drop trigger if exists weekly_user_external_delete;;
drop trigger if exists monthly_tax_insert;;
drop trigger if exists monthly_tax_delete;;
drop trigger if exists user_event_insert;;
drop trigger if exists user_event_delete;;
drop trigger if exists daily_layer_brokerage_insert;;
drop trigger if exists daily_layer_brokerage_update;;
drop trigger if exists daily_layer_brokerage_delete;;
drop trigger if exists daily_user_coupon_insert;;
drop trigger if exists daily_user_coupon_update;;
drop trigger if exists daily_user_coupon_delete;;
drop trigger if exists daily_staff_coupon_insert;;
drop trigger if exists daily_staff_coupon_update;;
drop trigger if exists daily_staff_coupon_delete;;
drop trigger if exists daily_layer_subsidy_insert;;
drop trigger if exists daily_layer_subsidy_update;;
drop trigger if exists daily_layer_subsidy_delete;;
drop trigger if exists daily_user_subsidy_insert;;
drop trigger if exists daily_user_subsidy_update;;
drop trigger if exists daily_user_subsidy_delete;;
drop trigger if exists daily_user_game_subsidy_insert;;
drop trigger if exists daily_user_game_subsidy_update;;
drop trigger if exists daily_user_game_subsidy_delete;;
drop trigger if exists weekly_user_subsidy_insert;;
drop trigger if exists weekly_user_subsidy_update;;
drop trigger if exists weekly_user_subsidy_delete;;
drop trigger if exists weekly_user_game_subsidy_insert;;
drop trigger if exists weekly_user_game_subsidy_update;;
drop trigger if exists weekly_user_game_subsidy_delete;;
drop trigger if exists monthly_user_subsidy_insert;;
drop trigger if exists monthly_user_subsidy_update;;
drop trigger if exists monthly_user_subsidy_delete;;
drop trigger if exists monthly_user_game_subsidy_insert;;
drop trigger if exists monthly_user_game_subsidy_update;;
drop trigger if exists monthly_user_game_subsidy_delete;;
drop trigger if exists weekly_staff_insert;;
drop trigger if exists weekly_staff_update;;
drop trigger if exists weekly_staff_delete;;
drop trigger if exists weekly_staff_lottery_insert;;
drop trigger if exists weekly_staff_lottery_update;;
drop trigger if exists weekly_staff_lottery_delete;;
drop trigger if exists weekly_staff_external_insert;;
drop trigger if exists weekly_staff_external_update;;
drop trigger if exists weekly_staff_external_delete;;
drop trigger if exists daily_status_insert;;
drop trigger if exists daily_status_update;;
drop trigger if exists daily_status_delete;;
drop trigger if exists daily_user_insert;;
drop trigger if exists daily_user_update;;
drop trigger if exists daily_user_delete;;
drop trigger if exists daily_user_deal_insert;;
drop trigger if exists daily_user_deal_update;;
drop trigger if exists daily_user_deal_delete;;
drop trigger if exists daily_user_lottery_insert;;
drop trigger if exists daily_user_lottery_update;;
drop trigger if exists daily_user_lottery_delete;;
drop trigger if exists daily_user_external_insert;;
drop trigger if exists daily_user_external_update;;
drop trigger if exists daily_user_external_delete;;


alter table monthly_staff
    modify amount_first_deposit decimal(14,2) not null comment '首充金额',
    modify deposit_amount decimal(14,2) not null comment '成功入款金额',
    modify deposit_bank_amount decimal(14,2) not null comment '网银成功入款金额',
    modify deposit_weixin_amount decimal(14,2) not null comment '微信成功入款金额',
    modify deposit_alipay_amount decimal(14,2) not null comment '支付宝成功入款金额',
    modify bank_deposit_amount decimal(14,2) not null comment '公司入款金额',
    modify staff_deposit_amount decimal(14,2) not null comment '人工入款金额',
    modify withdraw_amount decimal(14,2) not null comment '成功出款金额',
    modify staff_withdraw_amount decimal(14,2) not null comment '人工出款金额',
    modify coupon_amount decimal(14,2) not null comment '活动礼金总额',
    modify bet_amount decimal(14,2) not null comment '总投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '总派奖金额',
    modify rebate_amount decimal(14,2) not null comment '总返点金额',
    modify subsidy_amount decimal(14,2) not null comment '总返水金额',
    modify profit_amount decimal(14,2) not null comment '总损益金额';;

alter table monthly_staff_lottery
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify rebate_amount decimal(14,2) not null comment '返点金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table monthly_staff_external
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table lottery_period
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify rebate_amount decimal(14,2) not null comment '返点总额',
    modify bonus_amount decimal(14,2) not null comment '中奖总额',
    modify profit_amount decimal(14,2) not null comment '损益总额';;

alter table daily_staff
    modify amount_first_deposit decimal(14,2) not null comment '首充金额',
    modify deposit_amount decimal(14,2) not null comment '成功入款金额',
    modify deposit_bank_amount decimal(14,2) not null comment '网银成功入款金额',
    modify deposit_weixin_amount decimal(14,2) not null comment '微信成功入款金额',
    modify deposit_alipay_amount decimal(14,2) not null comment '支付宝成功入款金额',
    modify bank_deposit_amount decimal(14,2) not null comment '公司入款金额',
    modify staff_deposit_amount decimal(14,2) not null comment '人工入款金额',
    modify withdraw_amount decimal(14,2) not null comment '成功出款金额',
    modify staff_withdraw_amount decimal(14,2) not null comment '人工出款金额',
    modify coupon_amount decimal(14,2) not null comment '活动礼金总额',
    modify bet_amount decimal(14,2) not null comment '总投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '总派奖金额',
    modify rebate_amount decimal(14,2) not null comment '总返点金额',
    modify subsidy_amount decimal(14,2) not null comment '总返水金额',
    modify profit_amount decimal(14,2) not null comment '总损益金额';;

alter table daily_staff_lottery
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify rebate_amount decimal(14,2) not null comment '返点金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table daily_staff_external
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table monthly_user
    modify deposit_amount decimal(14,2) not null comment '成功入款金额',
    modify deposit_bank_amount decimal(14,2) not null comment '网银成功入款金额',
    modify deposit_weixin_amount decimal(14,2) not null comment '微信成功入款金额',
    modify deposit_alipay_amount decimal(14,2) not null comment '支付宝成功入款金额',
    modify bank_deposit_amount decimal(14,2) not null comment '公司入款金额',
    modify staff_deposit_amount decimal(14,2) not null comment '人工入款金额',
    modify withdraw_amount decimal(14,2) not null comment '成功出款金额',
    modify staff_withdraw_amount decimal(14,2) not null comment '人工出款金额',
    modify coupon_amount decimal(14,2) not null comment '活动礼金总额',
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify rebate_amount decimal(14,2) not null comment '返点金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify brokerage_amount decimal(14,2) not null comment '返佣总额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table monthly_user_lottery
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify rebate_amount decimal(14,2) not null comment '返点金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table monthly_user_external
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table daily_user_brokerage
    modify broker_1_rate decimal(14,2) not null comment '一级下线佣金比例',
    modify broker_2_rate decimal(14,2) not null comment '二级下线佣金比例',
    modify broker_3_rate decimal(14,2) not null comment '三级下线佣金比例',
    modify brokerage decimal(14,2) not null comment '当天佣金',
    modify brokerage_1 decimal(14,2) not null comment '一级下线佣金',
    modify brokerage_2 decimal(14,2) not null comment '二级下线佣金',
    modify brokerage_3 decimal(14,2) not null comment '三级下线佣金',
    modify cumulate_brokerage decimal(14,2) not null comment '累计佣金',
    modify broker_1_user int not null comment '一级下线会员人数',
    modify broker_2_user int not null comment '二级下线会员人数',
    modify broker_3_user int not null comment '三级下线会员人数',
    modify broker_1_bet decimal(14,2) not null comment '一级下线投注金额',
    modify broker_2_bet decimal(14,2) not null comment '二级下线投注金额',
    modify broker_3_bet decimal(14,2) not null comment '三级下线投注金额',
    modify broker_1_bet_user int not null comment '一级下线投注人数',
    modify broker_2_bet_user int not null comment '二级下线投注人数',
    modify broker_3_bet_user int not null comment '三级下线投注人数';;

alter table weekly_user_brokerage
    modify brokerage decimal(14,2) not null comment '当天佣金',
    modify brokerage_1 decimal(14,2) not null comment '一级下线佣金',
    modify brokerage_2 decimal(14,2) not null comment '二级下线佣金',
    modify brokerage_3 decimal(14,2) not null comment '三级下线佣金',
    modify broker_1_user int not null comment '一级下线会员人数',
    modify broker_2_user int not null comment '二级下线会员人数',
    modify broker_3_user int not null comment '三级下线会员人数',
    modify broker_1_bet decimal(14,2) not null comment '一级下线投注金额',
    modify broker_2_bet decimal(14,2) not null comment '二级下线投注金额',
    modify broker_3_bet decimal(14,2) not null comment '三级下线投注金额',
    modify broker_1_bet_user int not null comment '一级下线投注人数',
    modify broker_2_bet_user int not null comment '二级下线投注人数',
    modify broker_3_bet_user int not null comment '三级下线投注人数';;

alter table monthly_user_brokerage
    modify brokerage decimal(14,2) not null comment '当天佣金',
    modify brokerage_1 decimal(14,2) not null comment '一级下线佣金',
    modify brokerage_2 decimal(14,2) not null comment '二级下线佣金',
    modify brokerage_3 decimal(14,2) not null comment '三级下线佣金',
    modify broker_1_user int not null comment '一级下线会员人数',
    modify broker_2_user int not null comment '二级下线会员人数',
    modify broker_3_user int not null comment '三级下线会员人数',
    modify broker_1_bet decimal(14,2) not null comment '一级下线投注金额',
    modify broker_2_bet decimal(14,2) not null comment '二级下线投注金额',
    modify broker_3_bet decimal(14,2) not null comment '三级下线投注金额',
    modify broker_1_bet_user int not null comment '一级下线投注人数',
    modify broker_2_bet_user int not null comment '二级下线投注人数',
    modify broker_3_bet_user int not null comment '三级下线投注人数';;

alter table user_cumulate
    modify money decimal(14,2) not null comment '账户余额',
    modify deposit_amount decimal(14,2) not null comment '成功入款金额',
    modify withdraw_amount decimal(14,2) not null comment '成功出款金额',
    modify subsidy decimal(14,2) not null comment '反水',
    modify brokerage decimal(14,2) not null comment '代理佣金',
    modify bet_all decimal(14,2) not null comment '有效投注',
    modify bet_lottery decimal(14,2) not null comment '彩票投注',
    modify bet_video decimal(14,2) not null comment '真人视讯投注',
    modify bet_game decimal(14,2) not null comment '电子游戏投注',
    modify bet_sports decimal(14,2) not null comment '体育投注',
    modify bet_cards decimal(14,2) not null comment '棋牌投注',
    modify bonus_all decimal(14,2) not null comment '派奖',
    modify bonus_lottery decimal(14,2) not null comment '彩票派奖',
    modify bonus_video decimal(14,2) not null comment '真人视讯派奖',
    modify bonus_game decimal(14,2) not null comment '电子游戏派奖',
    modify bonus_sports decimal(14,2) not null comment '体育派奖',
    modify bonus_cards decimal(14,2) not null comment '棋牌派奖',
    modify profit_all decimal(14,2) not null comment '损益',
    modify profit_lottery decimal(14,2) not null comment '彩票损益',
    modify profit_video decimal(14,2) not null comment '真人视讯损益',
    modify profit_game decimal(14,2) not null comment '电子游戏损益',
    modify profit_sports decimal(14,2) not null comment '体育损益',
    modify profit_cards decimal(14,2) not null comment '棋牌损益';;

alter table weekly_user
    modify deposit_amount decimal(14,2) not null comment '成功入款金额',
    modify deposit_bank_amount decimal(14,2) not null comment '网银成功入款金额',
    modify deposit_weixin_amount decimal(14,2) not null comment '微信成功入款金额',
    modify deposit_alipay_amount decimal(14,2) not null comment '支付宝成功入款金额',
    modify bank_deposit_amount decimal(14,2) not null comment '公司入款金额',
    modify staff_deposit_amount decimal(14,2) not null comment '人工入款金额',
    modify withdraw_amount decimal(14,2) not null comment '成功出款金额',
    modify staff_withdraw_amount decimal(14,2) not null comment '人工出款金额',
    modify coupon_amount decimal(14,2) not null comment '活动礼金总额',
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify rebate_amount decimal(14,2) not null comment '返点金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify brokerage_amount decimal(14,2) not null comment '返佣总额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table weekly_user_lottery
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify rebate_amount decimal(14,2) not null comment '返点金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table weekly_user_external
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table monthly_tax
    modify tax_total decimal(14,2) unsigned not null comment '应收总金额',
    modify tax_rent decimal(14,2) unsigned not null comment '服务费',
    modify wager_lottery decimal(14,2) not null comment '彩票有效投注金额',
    modify bonus_lottery decimal(14,2) not null comment '彩票派奖金额',
    modify profit_lottery decimal(14,2) not null comment '彩票损益',
    modify tax_lottery decimal(14,2) unsigned not null comment '彩票提成',
    modify wager_video decimal(14,2) not null comment '真人有效投注金额',
    modify bonus_video decimal(14,2) not null comment '真人派奖金额',
    modify profit_video decimal(14,2) not null comment '真人损益',
    modify tax_video decimal(14,2) unsigned not null comment '真人提成',
    modify wager_game decimal(14,2) not null comment '电子有效投注金额',
    modify bonus_game decimal(14,2) not null comment '电子派奖金额',
    modify profit_game decimal(14,2) not null comment '电子损益',
    modify tax_game decimal(14,2) unsigned not null comment '电子提成',
    modify wager_sports decimal(14,2) not null comment '体育有效投注金额',
    modify bonus_sports decimal(14,2) not null comment '体育派奖金额',
    modify profit_sports decimal(14,2) not null comment '体育损益',
    modify tax_sports decimal(14,2) unsigned not null comment '体育提成',
    modify wager_cards decimal(14,2) not null comment '棋牌有效投注金额',
    modify bonus_cards decimal(14,2) not null comment '棋牌派奖金额',
    modify profit_cards decimal(14,2) not null comment '棋牌损益',
    modify tax_cards decimal(14,2) unsigned not null comment '棋牌提成';;

alter table daily_layer_brokerage
    modify brokerage_amount decimal(14,2) not null comment '返佣总额';;

alter table daily_user_coupon
    modify coupon_money decimal(14,2) not null comment '奖励金额',
    modify coupon_audit decimal(14,2) not null comment '活动稽核';;

alter table daily_staff_coupon
    modify offer_money decimal(14,2) not null comment '发放金额',
    modify take_money decimal(14,2) not null comment '领取金额';;

alter table daily_layer_subsidy
    modify bet_all decimal(14,2) not null comment '投注总额',
    modify subsidy_all decimal(14,2) not null comment '反水总额',
    modify bet_lottery decimal(14,2) not null comment '彩票投注总额',
    modify subsidy_lottery decimal(14,2) not null comment '彩票反水总额',
    modify bet_video decimal(14,2) not null comment '真人投注总额',
    modify subsidy_video decimal(14,2) not null comment '真人反水总额',
    modify bet_game decimal(14,2) not null comment '电子投注总额',
    modify subsidy_game decimal(14,2) not null comment '电子反水总额',
    modify bet_sports decimal(14,2) not null comment '体育投注总额',
    modify subsidy_sports decimal(14,2) not null comment '体育反水总额',
    modify bet_cards decimal(14,2) not null comment '棋牌投注总额',
    modify subsidy_cards decimal(14,2) not null comment '棋牌反水总额';;

alter table daily_user_subsidy
    modify subsidy decimal(14,2) not null comment '当期反水',
    modify cumulate_subsidy decimal(14,2) not null comment '累计反水总额';;

alter table daily_user_game_subsidy
    modify bet_amount decimal(14,2) not null comment '有效投注',
    modify subsidy decimal(14,2) not null comment '反水金额';;

alter table weekly_user_subsidy
    modify subsidy decimal(14,2) not null comment '当期反水',
    modify cumulate_subsidy decimal(14,2) not null comment '累计反水总额';;

alter table weekly_user_game_subsidy
    modify bet_amount decimal(14,2) not null comment '有效投注',
    modify subsidy decimal(14,2) not null comment '反水金额';;

alter table monthly_user_subsidy
    modify subsidy decimal(14,2) not null comment '当期反水',
    modify cumulate_subsidy decimal(14,2) not null comment '累计反水总额';;

alter table monthly_user_game_subsidy
    modify bet_amount decimal(14,2) not null comment '有效投注',
    modify subsidy decimal(14,2) not null comment '反水金额';;

alter table weekly_staff
    modify amount_first_deposit decimal(14,2) not null comment '首充金额',
    modify deposit_amount decimal(14,2) not null comment '成功入款金额',
    modify deposit_bank_amount decimal(14,2) not null comment '网银成功入款金额',
    modify deposit_weixin_amount decimal(14,2) not null comment '微信成功入款金额',
    modify deposit_alipay_amount decimal(14,2) not null comment '支付宝成功入款金额',
    modify bank_deposit_amount decimal(14,2) not null comment '公司入款金额',
    modify staff_deposit_amount decimal(14,2) not null comment '人工入款金额',
    modify withdraw_amount decimal(14,2) not null comment '成功出款金额',
    modify staff_withdraw_amount decimal(14,2) not null comment '人工出款金额',
    modify coupon_amount decimal(14,2) not null comment '活动礼金总额',
    modify bet_amount decimal(14,2) not null comment '总投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '总派奖金额',
    modify rebate_amount decimal(14,2) not null comment '总返点金额',
    modify subsidy_amount decimal(14,2) not null comment '总返水金额',
    modify profit_amount decimal(14,2) not null comment '总损益金额';;

alter table weekly_staff_lottery
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify rebate_amount decimal(14,2) not null comment '返点金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table weekly_staff_external
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table daily_user
    modify deposit_amount decimal(14,2) not null comment '成功入款金额',
    modify deposit_max decimal(14,2) not null comment '最大入款金额',
    modify deposit_bank_amount decimal(14,2) not null comment '网银成功入款金额',
    modify deposit_weixin_amount decimal(14,2) not null comment '微信成功入款金额',
    modify deposit_alipay_amount decimal(14,2) not null comment '支付宝成功入款金额',
    modify bank_deposit_amount decimal(14,2) not null comment '银行卡入款金额',
    modify bank_deposit_coupon decimal(14,2) not null comment '银行卡入款优惠金额',
    modify simple_deposit_amount decimal(14,2) not null comment '快捷入款金额',
    modify staff_deposit_amount decimal(14,2) not null comment '人工入款金额',
    modify withdraw_amount decimal(14,2) not null comment '成功出款金额',
    modify withdraw_max decimal(14,2) not null comment '最大出款金额',
    modify staff_withdraw_amount decimal(14,2) not null comment '人工出款金额',
    modify coupon_amount decimal(14,2) not null comment '活动礼金总额',
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify rebate_amount decimal(14,2) not null comment '返点金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify brokerage_amount decimal(14,2) not null comment '返佣总额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table daily_user_deal
    modify vary_money decimal(14,2) not null comment '账户余额变动',
    modify vary_deposit_audit decimal(14,2) not null comment '入款稽核变动',
    modify vary_coupon_audit decimal(14,2) not null comment '活动稽核变动';;

alter table daily_user_lottery
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify rebate_amount decimal(14,2) not null comment '返点金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

alter table daily_user_external
    modify bet_amount decimal(14,2) not null comment '投注金额',
    modify wager_amount decimal(14,2) not null comment '有效投注金额',
    modify bonus_amount decimal(14,2) not null comment '派奖金额',
    modify subsidy_amount decimal(14,2) not null comment '返水金额',
    modify profit_amount decimal(14,2) not null comment '损益金额';;

replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select '%',schema(),concat('master_',schema()),table_name,user(),'select,insert,update,delete'
    from information_schema.tables
    where table_schema=schema() and table_name not like '\_%';;
replace into mysql.tables_priv(host,db,user,table_name,grantor,table_priv)
    select r.to_host,p.db,r.to_user,p.table_name,p.grantor,p.table_priv
    from mysql.tables_priv p inner join mysql.role_edges r on p.host=r.from_host and p.user=r.from_user;;
flush privileges;;
