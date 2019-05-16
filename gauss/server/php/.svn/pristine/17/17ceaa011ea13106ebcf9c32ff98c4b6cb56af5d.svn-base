<?php

/**
 * Class PromotionEdit
 * @description 优惠活动编辑类
 * @author Rose
 * @date 2018-12-07
 * @link Websocket: Promotion/Manage/PromotionEdit {"promotion_id":1}
 * @param int $promotion_id 活动Id
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Promotion\Manage;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PromotionEdit extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);
            return;
        }

        // 操作权限检测
        $auth = json_decode($context->getInfo('StaffAuth'), true);
        if (!in_array('web_promotion', $auth)) {
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $data = $context->getData();
        $mysql = $config->data_staff;
        $promotion_id = $data['promotion_id'];
        if(!is_numeric($promotion_id)){
            $context->reply(['status' => 205, 'msg' => '参数类型错误']);
            return;
        }
        
        $sql = 'SELECT * FROM promotion WHERE promotion_id=:promotion_id';
        $param = [
            ':promotion_id' => $promotion_id,
        ];
        $list = array();
        try{
            foreach ($mysql->query($sql,$param) as $row){
                $list = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取数据失败']);
            throw new \PDOException($e);
        }
        if(empty($list)){
            $context->reply(['status' => 206, 'msg' => '查询信息为空,检查参数是否正确']);
            return;
        }
        $list['start_time'] = date('Y-m-d H:i:s', $list['start_time']);
        $list['stop_time'] = date('Y-m-d H:i:s', $list['stop_time']);
        $list['add_time'] = date('Y-m-d H:i:s', $list['add_time']);
        $context->reply(['status' => 200, 'msg' => '获取数据成功', 'info' => $list]);
    }
}