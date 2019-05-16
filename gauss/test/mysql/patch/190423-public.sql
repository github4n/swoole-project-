delimiter ;;

drop trigger if exists lottery_period_delete;;
create trigger lottery_period_delete before delete on lottery_period for each row
begin
end;;

update mysql.tables_priv
    set table_priv=concat_ws(',',table_priv,'delete')
    where db like '%public' and table_name='lottery_period'
      and 0<find_in_set('insert',table_priv);;

flush privileges;;