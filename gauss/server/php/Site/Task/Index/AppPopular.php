<?php
namespace Site\Task\Index;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 * AppPopular
 * @description   热门彩票
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Index/AppPopular 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class AppPopular implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $adapter = $context->getAdapter();
        $staff_mysql = $config->data_staff;
        //热门彩票
        $sqls = "SELECT * FROM suggest WHERE is_popular=1 AND to_home=0 ORDER BY display_order";
        try{
            $popular = iterator_to_array($staff_mysql->query($sqls));
        }catch (\PDOException $e){
            throw new \PDOException($e);
        }
        $adapter->plan('NotifyApp', ['path' => 'Index/Popular', 'data' => ['popular'=>$popular]]);
    }
}
