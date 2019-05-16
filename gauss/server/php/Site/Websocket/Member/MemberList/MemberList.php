<?php

namespace Site\Websocket\Member\MemberList;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * MemberList class.
 *
 * @description   会员管理-会员列表
 * @Author  blake
 * @date  2019-05-08
 * @links  Member/MemberList/MemberList
 * 参数：name:会员名,rel_name:真实姓名,level:会员层级,is_agent:是否代理（1是，2否）,agent_level:代理层级,major:大股东名字,Shareholder:股东名字,generaagent:总代理,ip:IP地址,tel:手机号,level_one:一级上线名称,level_tow:二级上线名称,level_three:三级上线名称,start_time:开始注册时间,end_time:结束注册时间
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class MemberList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('user_list_select', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $staffId = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo('StaffGrade');
        $MasterId = $context->getInfo('MasterId');
        $master_id = $MasterId == 0 ? $staffId : $MasterId;
        $data = $context->getData();
        $mysql = $config->data_report;
        $staff_mysql = $config->data_staff;
        $user_mysql = $config->data_user;
        $public_mysql = $config->data_public;
        $cache = $config->cache_site;
        $user_key = isset($data['user_key']) ? $data['user_key'] : '';
        $level = isset($data['level_id']) ? $data['level_id'] : '';
        $major_name = isset($data['major_name']) ? $data['major_name'] : '';
        $minor_name = isset($data['minor_name']) ? $data['minor_name'] : '';
        $agent_name = isset($data['agent_name']) ? $data['agent_name'] : '';
        $ip = isset($data['ip']) ? $data['ip'] : '';
        $phone_number = isset($data['phone_number']) ? $data['phone_number'] : '';   //手机号
        $broker_1_name = isset($data['broker_1_name']) ? $data['broker_1_name'] : '';  //一级上线
        $broker_2_name = isset($data['broker_2_name']) ? $data['broker_2_name'] : '';  //二级上线
        $broker_3_name = isset($data['broker_3_name']) ? $data['broker_3_name'] : '';  //三级上线
        $start_time = isset($data['register_start_time']) ? $data['register_start_time'] : '';
        $end_time = isset($data['register_end_time']) ? $data['register_end_time'] : '';
        $user_name = isset($data['user_name']) ? $data['user_name'] : '';
        $time = '';
        $param = [];
        //子账号的权限信息  //会员层级列表
        $layer_list = $this->LayerManage($context, $config);
        if ($MasterId != 0) {
            $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
            $layers = [];

            foreach ($staff_mysql->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                $layers[] = $row['layer_id'];
            }
        }
        if (!empty($user_name)) {
            $user_name = ' AND user_name =:user_name ';
            $param['user_name'] = $user_name;
        }
        $limit = ' LIMIT 1000 ';
        if (!empty($user_key)) {
            $param[':user_key'] = $user_key;
            $user_key = ' AND user_key = :user_key ';
        }

        if (!empty($level)) {
            $param[':level'] = $level;
            $level = ' AND layer_id = :level ';
        }
        if (!empty($major_name)) {
            $param[':major_name'] = $major_name;
            $major_name = ' AND major_name = :major_name';
        }
        if (!empty($minor_name)) {
            $param[':minor_name'] = $minor_name;
            $minor_name = ' AND minor_name = :minor_name';
        }
        if (!empty($agent_name)) {
            $param[':agent_name'] = $agent_name;
            $agent_name = ' AND agent_name = :agent_name';
        }
        if (!empty($ip)) {
            $ip = ip2long($ip);
            $param[':ip'] = $ip;
            $ip = ' AND (register_ip = :ip OR login_ip = :ip )';
        }
        if (!empty($phone_number)) {
            $param[':phone_number'] = $phone_number;
            $phone_number = ' AND phone_number =:phone_number';
        }
        if (!empty($broker_1_name)) {
            $param[':broker_1_name'] = $broker_1_name;
            $broker_1_name = ' AND broker_1_key = :broker_1_name';
        }
        if (!empty($broker_2_name)) {
            $param[':broker_2_name'] = $broker_2_name;
            $broker_2_name = '  AND broker_2_key = :broker_2_name';
        }
        if (!empty($broker_3_name)) {
            $param[':broker_3_name'] = $broker_3_name;
            $broker_3_name = '  AND broker_3_key = :broker_3_name';
        }
        if (!empty($start_time) && !empty($end_time)) {
            $start_time = strtotime($start_time.' 00:00:00');
            $end_time = strtotime($end_time.' 23:59:59');
            $param[':start_time'] = $start_time;
            $param[':end_time'] = $end_time;
            $time = ' AND register_time BETWEEN :start_time AND :end_time';
        }
        $list = array();

        $order = ' order by user_id desc ';
        if ($StaffGrade == 0) {
            if (empty($MasterId)) {
                $sql = 'SELECT * FROM user_cumulate WHERE 1=1 '.$user_name.$user_key.$level.$major_name.$minor_name.$agent_name.$ip.$phone_number.$broker_1_name.$broker_2_name.$broker_3_name.$time.$order.$limit;
                $paramTrnaslation = $param;
            } else {
                $sql = 'SELECT * FROM user_cumulate WHERE layer_id in :layers '.$user_name.$user_key.$level.$major_name.$minor_name.$agent_name.$ip.$phone_number.$broker_1_name.$broker_2_name.$broker_3_name.$time.$order.$limit;
                $paramTrnaslation = array_merge($param, [':layers' => $layers]);
            }

            try {
                foreach ($mysql->query($sql, $paramTrnaslation) as $rows) {
                    $list[] = $rows;
                }
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '获取列表失败']);
                throw new \PDOException($e);
            }
        } elseif ($StaffGrade == 1) {
            if (empty($MasterId)) {
                $sql = 'SELECT * FROM user_cumulate WHERE major_id=:major_id '.$user_name.$user_key.$level.$major_name.$minor_name.$agent_name.$ip.$phone_number.$broker_1_name.$broker_2_name.$broker_3_name.$time.$order.$limit;
                $paramTrnaslation = array_merge([':major_id' => $master_id], $param);
            } else {
                $sql = 'SELECT * FROM user_cumulate WHERE layer_id in :layers  AND major_id=:major_id'.$user_name.$user_key.$level.$major_name.$minor_name.$agent_name.$ip.$phone_number.$broker_1_name.$broker_2_name.$broker_3_name.$time.$order.$limit;
                $paramTrnaslation = array_merge($param, [':major_id' => $master_id], [':layers' => $layers]);
            }

            try {
                foreach ($mysql->query($sql, $paramTrnaslation) as $rows) {
                    $list[] = $rows;
                }
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '获取列表失败']);
                throw new \PDOException($e);
            }
        } elseif ($StaffGrade == 2) {
            if (empty($MasterId)) {
                $sql = 'SELECT * FROM user_cumulate WHERE minor_id=:minor_id '.$user_name.$user_key.$level.$major_name.$minor_name.$agent_name.$ip.$phone_number.$broker_1_name.$broker_2_name.$broker_3_name.$time.$order.$limit;
                $paramTrnaslation = array_merge($param, [':minor_id' => $master_id]);
            } else {
                $sql = 'SELECT * FROM user_cumulate WHERE layer_id in :layers AND minor_id=:minor_id '.$user_name.$user_key.$level.$major_name.$minor_name.$agent_name.$ip.$phone_number.$broker_1_name.$broker_2_name.$broker_3_name.$time.$order.$limit;
                $paramTrnaslation = array_merge($param, [':minor_id' => $master_id], [':layers' => $layers]);
            }
            try {
                foreach ($mysql->query($sql, $paramTrnaslation) as $rows) {
                    $list[] = $rows;
                }
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '获取列表失败']);
                throw new \PDOException($e);
            }
        } elseif ($StaffGrade == 3) {
            if (empty($MasterId)) {
                $sql = 'SELECT * FROM user_cumulate WHERE agent_id=:agent_id '.$user_name.$user_key.$level.$major_name.$minor_name.$agent_name.$ip.$phone_number.$broker_1_name.$broker_2_name.$broker_3_name.$time.$order.$limit;
                $paramTrnaslation = array_merge($param, [':agent_id' => $master_id]);
            } else {
                $sql = 'SELECT * FROM user_cumulate WHERE layer_id in :layers  AND agent_id=:agent_id '.$user_name.$user_key.$level.$major_name.$minor_name.$agent_name.$ip.$phone_number.$broker_1_name.$broker_2_name.$broker_3_name.$time.$order.$limit;
                $paramTrnaslation = array_merge($param, [':agent_id' => $master_id], [':layers' => $layers]);
            }

            try {
                foreach ($mysql->query($sql, $paramTrnaslation) as $rows) {
                    $list[] = $rows;
                }
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '获取列表失败']);
                throw new \PDOException($e);
            }
        } else {
            $context->reply(['status' => 205, 'msg' => '当前登录账号没有访问权限']);

            return;
        }
        // $context->reply(['status' => 204, 'msg' => $list]);

        // return;
        $user_list = array();
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $user_list[$key]['user_id'] = $val['user_id'];
                $user_list[$key]['user_key'] = $val['user_key'];
                $user_list[$key]['user_name'] = $val['user_name'];
                $user_list[$key]['layer_id'] = $val['layer_id'];
                $user_list[$key]['layer_name'] = $val['layer_name'];
                $user_list[$key]['major_name'] = $val['major_name'];
                $user_list[$key]['minor_name'] = $val['minor_name'];
                $user_list[$key]['agent_name'] = $val['agent_name'];
                $user_list[$key]['money'] = $this->intercept_num($val['money']);
                $user_list[$key]['broker_1_key'] = empty($val['broker_1_key']) ? '' : $val['broker_1_key'];
                $user_list[$key]['broker_1_id'] = $val['broker_1_id'];
                $user_list[$key]['broker_2_key'] = empty($val['broker_2_key']) ? '' : $val['broker_2_key'];
                $user_list[$key]['broker_2_id'] = $val['broker_2_id'];
                $user_list[$key]['broker_3_key'] = empty($val['broker_3_key']) ? '' : $val['broker_3_key'];
                $user_list[$key]['broker_3_id'] = $val['broker_3_id'];
                $user_list[$key]['brokerage'] = $this->intercept_num($val['brokerage']);
                $user_list[$key]['register_ip_time'] = empty($val['register_time']) ? '' : date('Y-m-d H:i:s', $val['register_time']).'/';
                $register_ip = empty($val['register_ip']) ? '' : long2ip($val['register_ip']);

                $address = '';
                $ip = !empty($val['register_ip']) ? $val['register_ip'] : '';
                if ($ip != '') {
                    $ipTranslation = substr($ip, 0, 8);
                    $ip = long2ip($ip);
                    $ipSaved = json_decode($cache->hget('ipList', $ipTranslation));
                    if (!empty($ipSaved)) {
                        $address = ' '.'('.$ipSaved[0]->region.' '.$ipSaved[0]->city.')';
                    } else {
                        $ip_sql = 'select * from ip_address where ip_net=:ip_net ';
                        $ip_result = iterator_to_array($public_mysql->query($ip_sql, [':ip_net' => $ipTranslation]));
                        if (!empty($ip_result)) {
                            $address = ' '.'('.$ip_result[0]['region'].' '.$ip_result[0]['city'].')';
                            $cache->hset('ipList', $ipTranslation, json_encode($ip_result));
                        }
                    }
                }

                $user_list[$key]['register_ip_time'] .= $register_ip.$address;
                $user_list[$key]['login_ip_time'] = empty($val['login_time']) ? '' : date('Y-m-d H:i:s', $val['login_time']).'/';
                $login_ip = empty($val['login_ip']) ? '' : long2ip($val['login_ip']);

                $address = '';
                $ip = !empty($val['login_ip_time']) ? $val['login_ip_time'] : '';
                if ($ip != '') {
                    $ipTranslation = substr($ip, 0, 8);
                    $ip = long2ip($ip);
                    $ipSaved = json_decode($cache->hget('ipList', $ipTranslation));
                    if (!empty($ipSaved)) {
                        $address = ' '.'('.$ipSaved[0]->region.' '.$ipSaved[0]->city.')';
                    } else {
                        $ip_sql = 'select * from ip_address where ip_net=ip_net ';
                        $ip_result = iterator_to_array($public_mysql->query($ip_sql, [':ip_net' => $ipTranslation]));
                        if (!empty($ip_result)) {
                            $address = ' '.'('.$ip_result[0]['region'].' '.$ip_result[0]['city'].')';
                            $cache->hset('ipList', $ipTranslation, json_encode($ip_result));
                        }
                    }
                }

                $user_list[$key]['login_ip_time'] .= $login_ip.$address;
                $user_list[$key]['invite_code'] = $val['invite_code'];
                $user_list[$key]['profit_all'] = $this->intercept_num($val['profit_all']);
                if ($val['register_device'] == 0) {
                    $user_list[$key]['register_device'] = 'PC';
                } else {
                    $user_list[$key]['register_device'] = '手机';
                }
            }
        }
        //返回所有的会员层级的列表
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'level_list' => $layer_list,
            'list' => $user_list,
        ]);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'user_list_select',
            ':detail' => '查看会员列表信息',
        ];
        $staff_mysql->execute($sql, $params);
    }
}
