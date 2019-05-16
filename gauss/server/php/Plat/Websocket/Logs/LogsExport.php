<?php

namespace Plat\Websocket\Logs;

use Lib\Websocket\Context;
use Lib\Config;
use Plat\Websocket\CheckLogin;

/**
 * LogsExport class.
 *
 * @description   导出日志
 * @Author  avery
 * @date  2019-05-06
 * @links  url
 * @modifyAuthor   avery
 * @modifyTime  2019-05-06
 * 参数： admin_name:搜索的员工名，operate_name:操作类型,start_time:开始时间,end_time:结束时间
 * 状态码：
 * 200：导出成功
 * 201：没有操作权限
 * 202：请输入导出条件
 * 203：员工名类型不正确
 * 204：开始时间不能大于结束时间
 * 205：提交的时间类型不正确
 * 400：导出失败
 */
class LogsExport extends CheckLogin
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
        $param = [];
        //用户名模糊查询
        if (!empty(trim($data['admin_name']))) {
            $admin_name = ' AND admin_name =:admin_name ';
            $param[':admin_name'] = trim($data['admin_name']);
        }
        //操作类型
        if (!empty(trim($data['operate_name']))) {
            $operate_name = ' AND operate_name LIKE :operate_name';
            $param[':operate_name'] = " '%".trim($data['operate_name'])."%' ";
        }
        //只有开始时间的话那就查询那目前时间的数据，如果只有结束时间的话就只查询在从这个时间之前的数据，两个参数都有的话就查询之间的数据
        if (!empty(trim($data['start_time']))) {
            $param[':start'] = strtotime(trim($data['start_time']).' 00:00:00');
            if (!empty(trim($data['end_time']))) {
                $param[':end'] = strtotime(trim($data['end_time']).'23:59:59');
            } else {
                $param[':end'] = time();
            }
            $time = ' AND log_time  BETWEEN :start AND :end';
        }

        if (!empty(trim($data['end_time'])) && empty(trim($data['start_time']))) {
            $time = ' AND log_time <=  :end';
            $param[':end'] = strtotime(trim($data['end_time']).'23:59:59');
        }
        $mysql = $config->data_admin;
        try {
            $list = array();
            $sql = 'SELECT log_id,admin_name,operate_name,detail,log_time FROM operate_log_intact WHERE 1=1 '.$admin_name.$time.$operate_name;
            foreach ($mysql->query($sql, $param) as $row) {
                $list[] = $row;
            }
            if (!empty($list)) {
                foreach ($list as $key => $val) {
                    $list[$key]['time'] = date('Y-m-d H:i:s', $val['log_time']);
                }
            }
            $context->reply(['status' => 200, 'msg' => '获取成功', 'loglist' => $list]);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取失败']);
            throw new \PDOException('sql run error'.$e);
        }
    }
}
