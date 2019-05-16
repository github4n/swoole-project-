delimiter ;;

alter view dividend_settle_major as
    select a.staff_key,s.major_id,s.major_name,
        d.bet_amount,d.bet_rate,d.dividend_bet,d.profit_amount,d.profit_rate,d.dividend_profit,
        d.fee_rate,d.tax_rate,d.dividend_result,d.deliver_time,d.settle_time
    from dividend_settle d inner join staff_struct_major s on d.staff_id=s.major_id
        inner join staff_auth a on d.staff_id=a.staff_id;;

alter view dividend_settle_minor as
    select a.staff_key,s.major_id,s.major_name,s.minor_id,s.minor_name,
        d.bet_amount,d.bet_rate,d.dividend_bet,d.profit_amount,d.profit_rate,d.dividend_profit,
        d.fee_rate,d.tax_rate,d.dividend_result,d.deliver_time,d.settle_time
    from dividend_settle d inner join staff_struct_minor s on d.staff_id=s.minor_id
        inner join staff_auth a on d.staff_id=a.staff_id;;

alter view dividend_settle_agent as
    select a.staff_key,s.major_id,s.major_name,s.minor_id,s.minor_name,s.agent_id,s.agent_name,
        d.bet_amount,d.bet_rate,d.dividend_bet,d.profit_amount,d.profit_rate,d.dividend_profit,
        d.fee_rate,d.tax_rate,d.dividend_result,d.deliver_time,d.settle_time
    from dividend_settle d inner join staff_struct_agent s on d.staff_id=s.agent_id
        inner join staff_auth a on d.staff_id=a.staff_id;;
