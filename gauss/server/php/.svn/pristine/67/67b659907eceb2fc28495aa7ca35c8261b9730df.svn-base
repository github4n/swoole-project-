<?php

/**
 * Class BulletinEditUpdate.
 * @description 会员公告修改更新类
 * @author Rose
 * @date 2018-12-03
 * @link Websocket: Website/Message/BulletinEditUpdate {"layer_message_id":1,"title":"","publish":"0","layer_id":"2","start_time":"2018-12-10","stop_time":"2018-12-10","content":"修改测试公告内容","image_file":""}
 * @param int $layer_message_id 公告Id
 * @param string $title 标题
 * @param string $publish 状态
 * @param string $layer_id 层级Id
 * @param string $start_time 开始时间
 * @param string $stop_time 结束时间
 * @param string $content 内容
 * @param string $image_file 图片
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-15
 */

namespace Site\Websocket\Website\Message;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BulletinEditUpdate extends CheckLogin
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
        if (!in_array('web_message', $auth)) {
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $data = $context->getData();
        $mysql = $config->data_user;
        $mysql_staff = $config->data_staff;
        $layer_message_id = $data['layer_message_id'];
        if (!is_numeric($layer_message_id)) {
            $context->reply(['status' => 204, 'msg' => '参数类型错误']);
            return;
        }
        $title = $data['title'];
        $publish = $data['publish'];
        $layer_id = intval($data['layer_id']);
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
            $kTrim = new BulletinAdd;
            $title = $kTrim->trimEmpty($title);
        }

        if (mb_strlen($title) < 4 || mb_strlen($title) > 40) {
            $context->reply(['status' => 206, 'msg' => '公告标题长度在4-40个字符之间']);
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
        if (empty($content)) {
            $context->reply(['status' => 207, 'msg' => '内容描述不能为空']);
            return;
        }

        $sql = 'UPDATE layer_message SET  title=:title, layer_id=:layer_id,start_time=:start_time,stop_time=:stop_time,cover=:cover,content=:content,publish=:publish WHERE layer_message_id=:layer_message_id';
        $param = [
            ':layer_message_id' => $layer_message_id,
            ':title' => $title,
            ':layer_id' => $layer_id,
            ':start_time' => $start_time,
            ':stop_time' => $stop_time,
            ':cover' => $cover ?: 0,
            ':content' => $content,
            ':publish' => $publish == 1 ?: 0,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '修改成功']);
        //添加日志信息
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'web_message',
            ':detail' => '修改会员公告id为'.$layer_message_id.'的信息内容',
        ];
        $mysql_staff->execute($sql, $params);
    }
}
