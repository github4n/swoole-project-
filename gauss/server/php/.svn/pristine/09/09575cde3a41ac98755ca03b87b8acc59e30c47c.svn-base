<?php

/**
 * Class BannerDelete
 * @description 网站管理删除轮播图类
 * @author Rose
 * @date 2018-12-01
 * @link Websocket: Website/Index/BannerDelete {"carousel_id":[1,2]}
 * @param array $carousel_id 轮播图Id
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-26
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BannerDelete extends CheckLogin {

    public function onReceiveLogined(Context $context, Config $config) {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限']);
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
        $carousel_ids = $data['carousel_id'];
        if (empty($carousel_ids)) {
            $context->reply(['status' => 205, 'msg' => '参数不可为空']);
            return;
        }
        $carouselTranslation = '';
        foreach ($carousel_ids as $carousel_id) {
            if (!is_numeric($carousel_id)) {
                $context->reply(['status' => 205, 'msg' => '参数错误']);
                return;
            }
            $carouselTranslation .= $carousel_id . ',';
            $sql = "DELETE FROM carousel WHERE carousel_id=:carousel_id";
            $param = [':carousel_id' => $carousel_id];
            try {
                $mysql->execute($sql, $param);
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '删除失败']);
                throw new \PDOException($e);
            }
        }
        $carouselTranslation = rtrim($carouselTranslation, ',');
        $context->reply(['status' => 200, 'msg' => '删除成功']);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'web_homepage',
            ':detail' => '删除id为' . $carouselTranslation . '首页轮播图',
        ];
        $mysql->execute($sql, $params);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('Index/AppBanner', [], time());
    }

}
