<?php

/**
 * Class PopList
 * @description App弹窗消息列表类
 * @author Rose
 * @date 2019-02-15
 * @link Website/Index/App/PopList {"start_time":"2019-04-27","stop_time":"2019-04-27"}
 * @param string $start_time 开始时间
 * @param string $stop_time  结束时间
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Index\App;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PopList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 201, 'msg' => '当前账号没有操作权限权限']);

            return;
        }

        $auth = json_decode($context->getInfo('StaffAuth'), true);
        if (!in_array('web_homepage', $auth)) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限权限']);

            return;
        }

        //接受参数
        $data = $context->getData();
        $start_time = empty($data['start_time']) ? '' : strtotime($data['start_time']);
        $stop_time = empty($data['stop_time']) ? '' : strtotime($data['stop_time']);

        //搜索条件
        $time = '';
        $paramSearch = [];
        if (!empty($start_time) && !empty($stop_time)) {
            $time = 'AND start_time >= :start_time AND stop_time <= :stop_time';
            $paramSearch = [
                ':start_time' => $start_time,
                ':stop_time' => $stop_time,
            ];
        } elseif (!empty($start_time) && empty($stop_time)) {
            $time = 'AND start_time >= :start_time';
            $paramSearch = [':start_time' => $start_time];
        } elseif (empty($start_time) && !empty($stop_time)) {
            $time = 'AND stop_time <= :stop_time';
            $paramSearch = [':stop_time' => $stop_time];
        }

        //连接数据库
        $staff_mysql = $config->data_staff;

        //查询数据
        $tmp_list = [];
        $list = [];
        $sql = 'SELECT * FROM popup WHERE 1=1 ' . $time . ' order by add_time desc';
        foreach ($staff_mysql->query($sql, $paramSearch) as $row) {
            $tmp_list[] = $row;
        }

        foreach ($tmp_list as $k => $v) {
            $list[$k]['popup_id'] = $v['popup_id'];
            $list[$k]['content'] = $v['content'];
            $list[$k]['start_time'] = date('Y-m-d H:i:s', $v['start_time']);
            $list[$k]['stop_time'] = date('Y-m-d H:i:s', $v['stop_time']);
            $list[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            $list[$k]['publish'] = $v['publish'];
        }

        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $list,
        ]);

        //记录日志
        $operate_sql = 'INSERT INTO operate_log (staff_id,operate_key,detail,client_ip) VALUES (:staff_id,:operate_key,:detail,:client_ip)';
        $operate_param = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':operate_key' => 'web_homepage',
            ':detail' => '查看app弹窗消息',
            ':client_ip' => ip2long($context->getClientAddr()),
        ];
        $staff_mysql->execute($operate_sql, $operate_param);
    }
}
