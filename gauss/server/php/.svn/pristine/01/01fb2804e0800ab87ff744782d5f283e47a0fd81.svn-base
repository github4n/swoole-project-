<?php

namespace Site\Task\Site;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * Status.php
 * @description 站点设置任务
 * @Author Rose
 * @date 2019-05-09
 * @links Site/Status
 * @modifyAuthor Nathan
 * @modifyTime2019-05-09
 */
class Status implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $mysqlStaff = $config->data_staff;
        $sql = "select int_value from site_setting  where setting_key='site_status'";
        $status = [];
        foreach ($mysqlStaff->query($sql) as $row) {
            $status = $row;
        }
        if (!empty($status)) {
            // 返回维护公告
            $sql = 'Select `str_value` From `site_setting` Where `setting_key` = "maintenance_announcement"';
            $content = '';
            foreach($mysqlStaff->query($sql) as $v) {
                $content = $v['str_value'];
            }

            if ($status['int_value'] == 0) {
                $context->getAdapter()->plan('NotifyApp', ['path' => 'Site/Status', 'data' => ['data' => ['status' => 501, 'msg' => '正常运行']]]);
                $context->getAdapter()->plan('NotifyClient', ['path' => 'Website/Status', 'data' => ['status' => 501, 'msg' => '正常运行']]);
            }
            if ($status['int_value'] == 1) {
                $context->getAdapter()->plan('NotifyApp', ['path' => 'Site/Status', 'data' => ['data' => ['status' => 502, 'msg' => '网站停止交易', 'content' => $content]]]);
            }
            if ($status['int_value'] == 2) {
                $context->getAdapter()->plan('NotifyApp', ['path' => 'Site/Status', 'data' => ['data' => ['status' => 500, 'msg' => 'APP维护中', 'content' => $content]]]);
            }
            if ($status['int_value'] == 3) {
                $context->getAdapter()->plan('NotifyApp', ['path' => 'Site/Status', 'data' => ['data' => ['status' => 500, 'msg' => '维护中', 'content' => $content]]]);
                $context->getAdapter()->plan('NotifyClient', ['path' => 'Website/Status', 'data' => ['status' => 500, 'msg' => '维护中']]);
            }
        }
    }
}
