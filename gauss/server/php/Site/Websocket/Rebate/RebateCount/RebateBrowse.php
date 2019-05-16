<?php

/**
 * Class RebateBrowse
 * @description 返水及派发详情类
 * @author Blake
 * @date 2019-02-21
 * @link Websocket: Rebate/RebateCount/RebateBrowse {"layer_id":1,"daily":"20190105","username":""}
 * @param string $layer_id 层级Id
 * @param string $daily 日期
 * @param string $username 用户名
 * @returnData {status: 200, list: []}
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-08
 */

namespace Site\Websocket\Rebate\RebateCount;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

class RebateBrowse extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('subsidy_report', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);
            return;
        }

        $data = $context->getData();
        $mysql_report = $config->data_report;
        $mysql_user = $config->data_user;
        $sql = 'select * from daily_user_subsidy  where 1=1  ';
        $layer_id = !empty($data['layer_id']) ? $data['layer_id'] : ''; //层级$userId
        $deliverTime = !empty($data['daily']) ? $data['daily'] : ''; //时间
        $user_name = !empty($data['user_name']) ? $data['user_name'] : ''; //人名搜索
        $layer = json_decode($config->cache_site->hget('LayerList', 'allLayer'), true);
        $layer = array_combine(array_column($layer, 'layer_id'), array_column($layer, 'layer_name'));
        $paramSearch = [];

        if (empty($layer_id) || empty($deliverTime)) {
            $context->reply(['status' => 203, 'msg' => '时间和层级均不可为空']);
            return;
        }
        if (!empty($layer_id)) {
            $paramSearch[':layer_id'] = $layer_id;
            $sql .= ' AND layer_id = :layer_id ';
        }

        if (!empty($deliverTime)) {
            $paramSearch[':deliverTime'] = $deliverTime;
            $sql .= ' AND daily = :deliverTime ';
        }

        if (!empty($user_name)) {
            $user_name = $data['user_name'];
            $paramSearch[':user_name'] = $user_name;
            $sql .= ' AND user_key = :user_name ';
        }

        // 如果为非站长登录只查询该用户下属的会员信息
        $staffGrade = $context->getInfo('StaffGrade');
        if ($staffGrade > 0) {
            // 添加下属会员信息查询条件
            $field = $staffGrade == 1 ? 'major_id' : ($staffGrade == 2 ? 'minor_id' : 'agent_id');
            $masterId = $context->getInfo('MasterId');
            $paramSearch[':fieldId'] = $masterId > 0 ? $masterId : $context->getInfo('StaffId');
            $sql .= ' And `'.$field.'` = :fieldId ';
        }

        // 判断是否自动派发
        $sqlAuto = 'Select `layer_id` From `daily_layer_subsidy` Where `daily` = :daily And `layer_id` = :layerId And `auto_deliver` < 1';
        $paramAuto = [
            ':daily' => $deliverTime,
            ':layerId' => $layer_id
        ];
        $subsidys = [];
        foreach($mysql_report->query($sqlAuto, $paramAuto) as $v) {
            $subsidys[] = $v['layer_id'];
        }

        $list = iterator_to_array($mysql_report->query($sql, $paramSearch));
        $last_data = [];
        $transantion = [];
        $transantion['lottery'] = [];
        $interface_key_data = [];
        $external_game_data = [];

        $mysql_public = $config->data_public;
        $lottery_game_sql = 'select game_key,game_name from lottery_game';
        $external_model_sql = 'select category_key,interface_key,game_key,game_name from external_game';
        foreach ($mysql_public->query($lottery_game_sql) as $value) {
            $transantion['lottery'] += [$value['game_key'] => [['bet' => 0, 'subsidy' => 0, 'game_name' => $value['game_name']]],
            ];
        }
        foreach ($mysql_public->query($external_model_sql) as $value) {
            switch ($value['category_key']) {
                case 'video':
                    $category_name = '视讯';
                    break;
                case 'game':
                    $category_name = '游戏';
                    break;
                case 'cards':
                    $category_name = '棋牌';
                    break;
                case 'hunter':
                    $category_name = '捕猎';
                    break;
                case 'sports':
                    $category_name = '体育';
                    break;
            }
            $interface_key_data += [$value['game_key'] => $value['interface_key']];
            $external_game_data[] = $value['game_key'];
        }
        $transantion['fg'] = ['bet' => 0, 'subsidy' => 0, 'game_name' => 'FG电子'];
        $transantion['ky'] = ['bet' => 0, 'subsidy' => 0, 'game_name' => '开元棋牌'];
        if (!empty($list)) {
            foreach ($list as $v) {
                $subsidy_data = $transantion;
                $daily = $v['daily'];
                $userId = $v['user_id'];
                $betSum = 0;
                $subsidySum = 0;
                $game_subsidy_sql = 'select user_id,game_key, bet_amount ,subsidy,category_key from  daily_user_game_subsidy where daily=:daily and user_id = :userId';
                $param = [
                    ':daily' => $daily,
                    ':userId' => $userId,
                ];
                $game_subsidy_list = iterator_to_array($mysql_report->query($game_subsidy_sql, $param));
                foreach ($game_subsidy_list as $value) {
                    $betSum += $value['bet_amount'];
                    $subsidySum += $value['subsidy'];
                    if (in_array($value['game_key'], $external_game_data) || $value['game_key'] == 'fg' || $value['game_key'] == 'ky') {
                        $interface_translation = isset($interface_key_data[$value['game_key']]) ? $interface_key_data[$value['game_key']] : $value['game_key'];
                        if ($interface_translation == 'fg') {
                            $subsidy_data[$interface_translation]['bet'] += $value['bet_amount'];
                            $subsidy_data[$interface_translation]['subsidy'] += $value['subsidy'];
                        }
                        if ($interface_translation == 'ky') {
                            $subsidy_data[$interface_translation]['bet'] += $value['bet_amount'];
                            $subsidy_data[$interface_translation]['subsidy'] += $value['subsidy'];
                        }
                    } else {
                        $subsidy_data['lottery'][$value['game_key']][0]['bet'] += $value['bet_amount'];
                        $subsidy_data['lottery'][$value['game_key']][0]['subsidy'] += $value['subsidy'];
                    }
                }

                // 保留小数点后两位
                if(!empty($subsidy_data['lottery'])) {
                    foreach($subsidy_data['lottery'] as $key => $val) {
                        $subsidy_data['lottery'][$key][0]['bet'] = $this->intercept_num($val[0]['bet']);
                        $subsidy_data['lottery'][$key][0]['subsidy'] = $this->intercept_num($val[0]['subsidy']);
                    }
                }

                if(!empty($subsidy_data['fg'])) {
                    $subsidy_data['fg']['bet'] = $this->intercept_num($subsidy_data['fg']['bet']);
                    $subsidy_data['fg']['subsidy'] = $this->intercept_num($subsidy_data['fg']['subsidy']);
                }

                if(!empty($subsidy_data['ky'])) {
                    $subsidy_data['ky']['bet'] = $this->intercept_num($subsidy_data['ky']['bet']);
                    $subsidy_data['ky']['subsidy'] = $this->intercept_num($subsidy_data['ky']['subsidy']);
                }

                $subsidy_data += [
                    'daily' => $daily,
                    'user_id' => $v['user_id'],
                    'layer' => isset($layer[$v['layer_id']]) ? $layer[$v['layer_id']] : '--',
                    'layer_id' => $v['layer_id'],
                    'user_key' => $v['user_key'],
                    'cumulate_subsidy' => $this->intercept_num($subsidySum),
                    'betSum' => $this->intercept_num($betSum),
                ];

                if (!empty($subsidys) && in_array($layer_id, $subsidys)) {
                    $subsidy_data += [
                        'is_automatic' => '是',
                    ];
                } else {
                    $subsidy_data += [
                        'is_automatic' => '否',
                    ];
                }

                if ($v['deliver_time'] == 0) {
                    $subsidy_data += [
                        'is_distribute' => '否',
                        'distribute_time' => '',
                    ];
                } else {
                    $subsidy_time = date('Y-m-d H:i:s', $v['deliver_time']);
                    $subsidy_data += [
                        'is_distribute' => '是',
                        'distribute_time' => $subsidy_time,
                    ];
                }
                $last_data[] = $subsidy_data;
            }
        } else {
            // 无数据时候返回彩种名称
            $context->reply(['status' => 200, 'list' => [], 'lottery' => $transantion['lottery']]);
            return;
        }

        $context->reply(['status' => 200, 'list' => $last_data]);
        return;
    }
}
