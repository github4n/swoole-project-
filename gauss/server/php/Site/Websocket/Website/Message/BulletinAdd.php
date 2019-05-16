<?php

/**
 * Class BulletinAdd
 * @description 会员公告增加类
 * @author Rose
 * @date 2018-12-03
 * @link Websocket: Website/Message/BulletinAdd {"title":"","publish":"1","layer_id":"0","start_time":"","stop_time":"","content":"","image":""}
 * @param string $title 标题
 * @param string $publish 状态
 * @param string $layer_id 层级Id
 * @param string $start_time 开始时间
 * @param string $stop_time 结束时间
 * @param string $content 内容
 * @param string $image 图片地址
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-15
 */

namespace Site\Websocket\Website\Message;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BulletinAdd extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限']);

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
        $mysql_staff = $config->data_staff;
        $title = $data['title'];
        $publish = $data['publish'];
        $layer_id = $data['layer_id'];
        $start_time = strtotime($data['start_time']);
        $stop_time = strtotime($data['stop_time']);
        $content = $data['content'];
        $cover = $data['image'];
        if (empty($publish)) {
            $context->reply(['status' => 500, 'msg' => '请选择状态']);

            return;
        }
        if (empty($title)) {
            $context->reply(['status' => 205, 'msg' => '公告标题不能为空']);

            return;
        } else {
            $title = $this->trimEmpty($title);
        }
        if (mb_strlen($title) < 4 || mb_strlen($title) > 40) {
            $context->reply(['status' => 206, 'msg' => '公告标题长度在4-40个字符之间']);

            return;
        }
        if (empty($content)) {
            $context->reply(['status' => 207, 'msg' => '内容描述不能为空']);

            return;
        }

        if (!$start_time) {
            $context->reply(['status' => 500, 'msg' => '开始时间格式错误']);

            return;
        }

        if (!$stop_time) {
            $context->reply(['status' => 500, 'msg' => '结束时间格式错误']);

            return;
        }

        if (!is_array($layer_id) || empty($layer_id)) {
            $context->reply(['status' => 210, 'msg' => '层级类型不正确']);

            return;
        }

        $layerData = [];
        foreach ($layer_id as $item) {
            if (!is_numeric($item)) {
                $context->reply(['status' => 211, 'msg' => '层级参数错误']);

                return;
            }
            if (count($layer_id) == 1 && $item == 0) {
                $layerData[] = [
                    'title' => $title,
                    'layer_id' => $item,
                    'start_time' => $start_time,
                    'stop_time' => $stop_time,
                    'cover' => $cover ? $cover : 0,
                    'content' => $content,
                    'publish' => $publish == 1 ? $publish : 0,
                    'insert_time' => time(),
                ];
            } else {
                $layerData[] = [
                    'title' => $title,
                    'layer_id' => $item,
                    'start_time' => $start_time,
                    'stop_time' => $stop_time,
                    'cover' => $cover ? $cover : 0,
                    'content' => $content,
                    'publish' => $publish == 1 ? $publish : 0,
                    'insert_time' => time(),
                ];
            }
        }
        $mysql->layer_message->load($layerData, [], 'ignore');

        $context->reply(['status' => 200, 'msg' => '新增成功']);
        //添加日志信息
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'web_message',
            ':detail' => '添加层级为'.json_encode($layer_id).'的会员公告信息',
        ];
        $mysql_staff->execute($sql, $params);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $layer_list = implode(',', $layer_id);
        if ($layer_list == 0) {
            $sql = 'SELECT client_id,layer_id FROM user_session';
            $list = iterator_to_array($mysql->query($sql));
        } else {
            $sql = 'SELECT client_id,layer_id FROM user_session WHERE Find_In_Set(`layer_id`, :layer_id)';

            $list = iterator_to_array($mysql->query($sql, [':layer_id' => $layer_list]));
        }
        foreach ($list as $key => $val) {
            $id = $val['client_id'];
            $taskAdapter->plan('NotifyApp', ['path' => 'Message/LayerMessage', 'data' => ['layer_id' => $val['layer_id'], 'id' => $id]]);
        }
    }
    
    /**
     * 删除字符串空格
     * @param string $string 要去除的字符串
     * @return string
     * @date 2019-05-15
     */
    public function trimEmpty($string) {
        $string = trim($string);
        $string = preg_replace('/\s(?=\s)/', '', $string);
        $string = preg_replace('/[\n\r\t]/', ' ', $string);
        return $string;
    }
}
