<?php

/**
 * Class BulletinList
 * @description 网站管理消息管理会员公告列表类
 * @author Rose
 * @date 2018-12-03
 * @link Websocket: Website/Message/BulletinList {"layer_id":"","start_time":"","end_time":""}
 * @param string $layer_id 层级Id
 * @param string $start_time 开始时间
 * @param string $end_time 结束时间
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-09
 */

namespace Site\Websocket\Website\Message;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BulletinList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);
            return;
        }

        // 操作权限检测
        $auth = json_decode($context->getInfo('StaffAuth'), true);
        if (!in_array('web_message', $auth)) {
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $data = $context->getData();
        $mysql = $config->data_user;
        $cache = $config->cache_site;
        //会员层级
        $all_layer = json_decode($cache->hget('LayerList', 'allLayer'));
        $layerId = array_column($all_layer, 'layer_id');
        $layer_id = isset($data['layer_id']) ? $data['layer_id'] : '';
        // 参数
        $searchParam = [];
        if($layer_id){
            $searchParam[':layer_id'] = $layer_id;
            $layer_id = " AND layer_id = :layer_id";
        }
        $start_time = isset($data['start_time']) ? $data['start_time'] : '';
        $end_time = isset($data['end_time']) ? $data['end_time'] : '';
        if ($start_time) {
            $start = strtotime($data["start_time"]);
            $searchParam[':start'] = $start;
            $start_time = " AND start_time >= :start";
        }
        if ($end_time) {
            $end = strtotime($data["end_time"]);
            $searchParam[':end'] = $end;
            $end_time = " AND stop_time <= :end";
        }

        $sql = "SELECT * FROM layer_message WHERE 1=1 " . $layer_id . $start_time . $end_time . ' order by insert_time desc';
        $total_sql = "SELECT layer_message_id FROM layer_message WHERE 1=1 " . $layer_id . $start_time . $end_time;
        $list = array();
        try{
            foreach ($mysql->query($sql, $searchParam) as $rows){
                $list[] = $rows;
            }
            $total = $mysql->execute($total_sql, $searchParam);
        }catch (\PDOException $e){
            $context->reply(['status' => 400, 'msg' => '获取列表失败']);
            throw new \PDOException($e);
        }
        $lists = array();
        if(!empty($list)){
            foreach ($list as $key=>$val){
                $lists[$key]['layer_message_id'] = $val['layer_message_id'];
                $lists[$key]['title'] = $val['title'];
                $lists[$key]['layer_id'] = in_array($val['layer_id'], $layerId) ? $val['layer_id'] : ($val['layer_id'] == 0 ? 0 : null); // 层级被删除则返回Null
                $lists[$key]['start_time'] = date('Y-m-d', $val['start_time']);
                $lists[$key]['stop_time'] = date('Y-m-d', $val['stop_time']);
                $lists[$key]['create_time'] = date('Y-m-d H:i:s', $val['insert_time']);
                $lists[$key]['content'] = $val['content'];
                $lists[$key]['publish'] = $val['publish'];
                $lists[$key]['cover'] = empty($val['cover']) ? 0 : 1; // 取消列表页封面图返回
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取数据成功', 'list' => $lists, 'total' => $total, 'layer_list' => $all_layer]);
    }
}