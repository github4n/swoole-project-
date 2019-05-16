<?php

/**
 * Class StationBulletinModify
 * @description 全站开关设置公告修改类
 * @author Black
 * @date 2019-03-08
 * @link Websocket: Website/Setting/StationBulletinModify {"announcement":"这是一个测试"}
 * @param string $announcement 内容
 * @returnData {}
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Setting;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class StationBulletinModify extends CheckLogin {

    public function onReceiveLogined(Context $context, Config $config) {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限']);
            return;
        }
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('web_acceptable', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);
            return;
        }
        $data = $context->getData();
        $staff_mysql = $config->data_staff;
        $announcement = !empty($data['announcement']) ? $data['announcement'] : '';
        if (empty($announcement)) {
            $context->reply(['status' => 203, 'msg' => '公告信息不可为空']);
            return;
        }

        $announcement_sql = "update site_setting set str_value=:announcement where setting_key ='maintenance_announcement'";
        try {
            $staff_mysql->execute($announcement_sql, [':announcement' => $announcement]);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改站点公告失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '修改站点公告成功']);
    }
}
