<?php

/**
 * Class BannerChange
 * @description 网站管理/停用启用类
 * @author Rose
 * @date 2018-12-01
 * @link Websocket: Website/Index/BannerChange {"carousel_id":"75","acceptable":2}
 * @param string $carousel_id 轮播图Id
 * @param string 状态，1：启用，2：停用
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-26
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BannerChange extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);
            return;
        }

        // 操作权限检测
        $auth = json_decode($context->getInfo('StaffAuth'), true);
        if (!in_array('web_homepage', $auth)) {
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $data = $context->getData();
        $mysql = $config->data_staff;
        $carousel_id = $data['carousel_id'];
        if (!is_numeric($carousel_id)) {
            $context->reply(['status' => 205, 'msg' => '参数错误']);
            return;
        }
        $acceptable = $data['acceptable'];
        if (!empty($acceptable)) {
            if ($acceptable == 1) {
                $publish = 1;
            } elseif ($acceptable == 2) {
                $publish = 0;
            } else {
                $context->reply(['status' => 204, 'msg' => '应用状态有误']);
                return;
            }
        } else {
            $context->reply(['status' => 206, 'msg' => '提交修改的信息']);

            return;
        }
        $sql = 'UPDATE carousel SET publish=:publish  WHERE carousel_id=:carousel_id';
        $param = [':carousel_id' => $carousel_id, ':publish' => $publish];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '修改成功']);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('Index/AppBanner', [], time());
    }
}
