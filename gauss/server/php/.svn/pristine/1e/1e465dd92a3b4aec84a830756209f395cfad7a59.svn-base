<?php

/**
 * Class Popchange
 * @description App弹窗细小修改状态
 * @author Rose
 * @date 2019-02-15
 * @link Websocket: Website/Index/App/PopChange {"popup_id":1,“publish”:2}
 * @param int $popup_id 消息Id
 * @param int $publish 状态
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Index\App;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PopChange extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if($StaffGrade != 0) {
            $context->reply(['status'=>203, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $auth = json_decode($context->getInfo('StaffAuth'),true);
        if(!in_array('web_homepage',$auth)) {
            $context->reply(['status'=>203, 'msg' => '当前账号没有操作权限']);
            return;
        }

        //接收数据
        $data = $context->getData();
        $popup_id = $data['popup_id'];
        $publish = $data['publish'];

        if (!is_numeric($popup_id))
        {
            $context->reply(['status' => 201, 'msg' => '序号类型错误']);
            return;
        }

        if (!is_numeric($publish))
        {
            $context->reply(['status' => 202, 'msg' => '状态码类型错误']);
            return;
        }

        //连接数据库
        $staff_mysql = $config->data_staff;

        //修改状态
        $sql = 'UPDATE popup SET publish=:publish WHERE popup_id=:popup_id';
        $param = [
            ':publish' => $publish,
            ':popup_id' =>$popup_id
        ];
        $flag = $staff_mysql->execute($sql,$param);
        if ($flag)
        {
            $context->reply(['status' => 200, 'msg' => '修改成功']);
        }else
        {
            $context->reply(['status' => 204, 'msg' => '修改失败']);
        }

        //记录日志
        $operate_sql = 'INSERT INTO operate_log (staff_id,operate_key,detail,client_ip) VALUES (:staff_id,:operate_key,:detail,:client_ip)';
        $operate_param = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':operate_key' => 'web_homepage',
            ':detail' => '修改app弹窗消息状态',
            ':client_ip' => ip2long($context->getClientAddr())
        ];
        $staff_mysql->execute($operate_sql,$operate_param);
    }
}