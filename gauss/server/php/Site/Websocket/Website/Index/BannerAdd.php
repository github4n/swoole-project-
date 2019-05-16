<?php

/**
 * Class BannerAdd
 * @description 网站管理新增轮播图
 * @author Rose
 * @date 2018-12-01
 * @link Websocket: Website/Index/BannerAdd {"img_src":"","link_type":"","link_data":"","publish":"","start_time":"","stop_time":""}
 * @param string $img_src    图片
 * @param string $link_type  链接方式
 * @param string $link_data  链接地址
 * @param string $publish    状态
 * @param string $start_time 开始时间
 * @param string $stop_time  结束时间
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-08
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BannerAdd extends CheckLogin
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
        if (!in_array('web_homepage', $auth)) {
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限']);

            return;
        }

        $data = $context->getData();
        $mysql = $config->data_staff;
        $img_src = $data['img_src'];
        $link_type = $data['link_type'];
        $link_data = $data['link_data'];
        $publish = $data['publish'];
        $start_time = $data['start_time'];
        $stop_time = $data['stop_time'];
        if (empty($link_type)) {
            $context->reply(['status' => 209, 'msg' => '链接方式必须选择']);

            return;
        }
        if (!preg_match("/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/", $link_data)) {
            $context->reply(['status' => 500, 'msg' => '链接地址格式不正确！']);
            return;
        }
        if(mb_strlen($link_data) > 30){
            $context->reply(['status' => 206, 'msg' => '链接地址需控制在30个字符以内']);
            return;
        }
        if (empty($img_src)) {
            $context->reply(['status' => 204, 'msg' => '轮播图不能为空']);

            return;
        }
        if (empty($publish)) {
            $context->reply(['status' => 205, 'msg' => '状态必须选择']);

            return;
        }
        if ($publish == 1) {
            $publish = 1;
        } elseif ($publish == 2) {
            $publish = 0;
        } else {
            $context->reply(['status' => 206, 'msg' => '状态参数错误']);

            return;
        }
        if (empty($start_time)) {
            $context->reply(['status' => 207, 'msg' => '开始时间不能为空']);

            return;
        } else {
            $start_time = strtotime($start_time);
        }
        if (empty($stop_time)) {
            $context->reply(['status' => 208, 'msg' => '结束时间不能为空']);

            return;
        } else {
            $stop_time = strtotime($stop_time);
        }
        if($stop_time < $start_time) {
            $context->reply(['status' => 500, 'msg' => '开始时间不能大于结束时间']);
            return;
        }
        $sql = 'INSERT INTO carousel SET start_time=:start_time, stop_time=:stop_time, add_time = :add_time, img_src=:img_src, link_type=:link_type, link_data=:link_data, publish=:publish ';
        $param = [
            ':start_time' => $start_time,
            ':stop_time' => $stop_time,
            ':add_time' => time(),
            ':img_src' => $img_src,
            ':link_type' => $link_type,
            ':link_data' => $link_data,
            ':publish' => $publish,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '新增失败']);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'web_homepage',
            ':detail' => '新增首页轮播图',
        ];
        $mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '新增成功']);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('Index/AppBanner', [], time());
    }
}
