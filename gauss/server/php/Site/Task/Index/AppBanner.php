<?php
namespace Site\Task\Index;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 * AppBanner.php
 * @description   app/轮播图推送任务
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Index/AppBanner 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class AppBanner implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $adapter = $context->getAdapter();
        $staff_mysql = $config->data_staff;

        //首页轮播图
        $sql = "SELECT carousel_id,img_src,link_type,link_data FROM carousel WHERE publish = 1 AND stop_time>:stop_time AND start_time<:start_time LIMIT 5";
        $param = [
            ":stop_time"=>time(),
            ":start_time"=>time(),
        ];
        try{
            $banner = iterator_to_array($staff_mysql->query($sql,$param));
        }catch(\PDOException $e){
            throw new \PDOException($e);
        }
        $adapter->plan('NotifyApp', ['path' => 'Index/Banner', 'data' => ['banner'=>$banner]]);

    }
}
