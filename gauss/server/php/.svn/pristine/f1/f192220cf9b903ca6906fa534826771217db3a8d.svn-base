<?php

/**
 * Class BannerEditUpdate
 * @description 网站管理提交修改信息类
 * @author Rose
 * @date 2018-12-01
 * @link Websocket: Website/Index/BannerEditUpdate {"carousel_id":1,"img_src":"","link_type":"","link_data":"","publish":1,"start_time":"","stop_time":""}
 * @param int    $carousel_id 轮播图Id
 * @param string $img_src     图片地址
 * @param string $link_type   链接类型
 * @param string $link_data   链接地址
 * @param int    $publish     状态
 * @param string $start_time  开始时间
 * @param string $stop_time   结束时间
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-08
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BannerEditUpdate extends CheckLogin
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
        $img_src = $data['img_src'];
        $link_type = $data['link_type'];
        $link_data = $data['link_data'];
        $publish = $data['publish'];
        $start_time = $data['start_time'];
        $stop_time = $data['stop_time'];
        if (empty($carousel_id)) {
            $context->reply(['status' => 203, 'msg' => '参数类型错误']);
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
        if (empty($link_type)) {
            $context->reply(['status' => 209, 'msg' => '链接方式必须选择']);
            return;
        }
        if (!preg_match("/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/", $link_data)) {
            $context->reply(['status' => 210, 'msg' => '链接地址格式不正确！']);
            return;
        }
        if(mb_strlen($link_data) > 30){
            $context->reply(['status' => 206, 'msg' => '链接地址需控制在30个字符以内']);
            return;
        }
        $sql = 'UPDATE carousel SET start_time=:start_time, stop_time=:stop_time, img_src=:img_src, link_type=:link_type, link_data=:link_data, publish=:publish WHERE carousel_id=:carousel_id';
        $param = [
            ':carousel_id' => $carousel_id,
            ':start_time' => $start_time,
            ':stop_time' => $stop_time,
            ':img_src' => $img_src,
            ':link_type' => $link_type,
            ':link_data' => $link_data,
            ':publish' => $publish,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '修改成功']);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'web_homepage',
            ':detail' => '修改id为'.$carousel_id.'首页轮播图',
        ];
        $mysql->execute($sql, $params);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('Index/AppBanner', [], time());
    }
}
