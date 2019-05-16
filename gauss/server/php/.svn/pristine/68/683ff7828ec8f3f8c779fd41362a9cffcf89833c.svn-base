<?php

namespace Plat\Websocket\Logs;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * LogsList class.
 *
 * @description   操作日志
 * @Author  avery
 * @date  2019-04-27
 * @links  Logs/LogsList
 *
 * 参数：  admin_name:员工名,start_time:开始时间,end_time:结束时间, operate_name:操作类型 gonum:跳转的页数
 * 状态码：
 * 200：获取成功
 * 400：获取失败
 *
 * @modifyAuthor   avery
 * @modifyTime  2019-04-27
 */
class LogsList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('account_operate_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $admin_name = '';
        $operate_name = '';
        $time = '';

        $limit = ' LIMIT 1000';
        $param = [];
        //用户名模糊查询
        if (!empty(trim($data['admin_name']))) {
            $admin_name = trim($data['admin_name']);
            $param[':admin_name'] = $admin_name;
            $admin_name = ' AND admin_name =  :admin_name';
        }
        //操作类型
        if (!empty(trim($data['operate_name']))) {
            $operate_name = trim($data['operate_name']);
            $param[':operate_name'] = '%'.$operate_name.'%';
            $operate_name = ' AND operate_name LIKE  :operate_name';
        }
        $order = ' order by log_id desc';
        if (!empty($data['end_time']) && !empty($data['start_time'])) {
            $start = strtotime(date('Ymd', strtotime($data['start_time'])).' 00:00:00');
            $end = strtotime(date('Ymd', strtotime($data['end_time'])).' 23:59:59');
            $param[':start'] = $start;
            $param[':end'] = $end;
            $time = ' AND log_time  BETWEEN :start AND :end';
        }
        $mysql = $config->data_admin;
        $list = array();
        $sql = 'SELECT log_id,admin_name,operate_name,detail,log_time FROM operate_log_intact WHERE 1=1 '.$admin_name.$time.$operate_name.$order.$limit;
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $list[] = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取失败']);
            throw new \PDOException($e);
        }
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['time'] = date('Y-m-d H:i:s', $val['log_time']);
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'loglist' => $list]);
    }
}
