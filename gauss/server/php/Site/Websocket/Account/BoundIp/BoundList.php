<?php

namespace Site\Websocket\Account\BoundIp;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 子账号绑定ip - 绑定ip列表接口
 * @author： leo
 * @date：   2019-04-08   
 * @link：   Account/BoundIp/BoundList {"staff_key":"name","bind_ip":"127.0.0.1","is_accurate":"yes"}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime:   交接时间：暂无
 * @param string  staff_key: 员工名
 * @param string  bind_ip: 绑定ip
 * @param string  is_accurate: 是否是精确查询  yes or no
 * @returnData: json;
 */

class BoundList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('slave_ip_select', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);
            return;
        }
        $StaffGrade = $context->getInfo('StaffGrade');
        $staffId = $context->getInfo('StaffId');
        $cache = $config->cache_site;
        $public_mysql = $config->data_public;
        $mysql = $config->data_staff;
        $data = $context->getData();
        $staff_key = isset($data['staff_key']) ? $data['staff_key'] : '';
        $bound_data = isset($data['bind_ip']) ? $data['bind_ip'] : '';
        //去除了模糊查询
        $is_accurate = isset($data['is_accurate']) ? $data['is_accurate'] : 'yes'; //是否是精确查询 yes or no
        $slave_list = [];
        $param = [];
        if (!empty($staff_key)) {
            $param[':staff_key'] = $staff_key;
            $staff_key = " AND staff_key = :staff_key";
        }
        $bound_ip = '';
        if (!empty($bound_data)) {
            $bound_data = ip2long($bound_data);
            if ($bound_data == false) {
                $context->reply([
                    'status' => 200,
                    'msg' => '获取成功',
                    'list' => $slave_list,
                ]);
                return;
            }
            $params[':bind_ip'] = $bound_data;
            $bound_ip = " AND bind_ip = :bind_ip ";
        }
        $order = ' ORDER BY add_time DESC';
        if ($StaffGrade == 0) {
            $sql = "SELECT * FROM staff_info_intact WHERE 1=1" . $staff_key;
        } else {
            $sql = "SELECT * FROM staff_info_intact WHERE master_id = :master_id" . $staff_key;
            $param[':master_id'] = $staffId;
        }
        $lists = iterator_to_array($mysql->query($sql, $param));
        try {
            if (!empty($lists)) {
                foreach ($lists as $rows) {
                    $staff_id = $rows['staff_id'];
                    $bind_sql = "SELECT * FROM staff_bind_ip WHERE staff_id = :staff_id" . $bound_ip . $order;
                    $params["staff_id"] = $staff_id;
                    $bind_result = iterator_to_array($mysql->query($bind_sql, $params));
                    if (!empty($bind_result)) {
                        foreach ($bind_result as $value) {
                            $ip = $value['bind_ip'];
                            if ($ip != 0) {
                                $ipTranslation = substr($ip, 0, 8);
                                $ip = long2ip($ip);
                                $ipSaved = json_decode($cache->hget('ipList', $ipTranslation));
                                if (!empty($ipSaved)) {
                                    $ip .= ' ' . '(' . $ipSaved[0]->region . ' ' . $ipSaved[0]->city . ')';
                                } else {
                                    $ip_sql = "SELECT * FROM ip_address WHERE ip_net = :ip_net ";
                                    $ip_net = [":ip_net" => $ipTranslation];
                                    $ip_result = iterator_to_array($public_mysql->query($ip_sql, $ip_net));
                                    if (!empty($ip_result)) {
                                        $ip .= ' ' . '(' . $ip_result[0]['region'] . ' ' . $ip_result[0]['city'] . ')';
                                        $cache->hset('ipList', $ipTranslation, json_encode($ip_result));
                                    }
                                }
                            }
                            $slave_list[] = [
                                'staff_id' => $staff_id,
                                'staff_key' => $rows['staff_key'],
                                'staff_name' => $rows['staff_name'],
                                'bind_ip' => $ip,
                                'creat_time' => $value['add_time']
                            ];
                        }
                    }
                }
                $slave_list = $this->arraySort($slave_list, 'creat_time', SORT_DESC);
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取失败']);
            throw new \PDOException($e);
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $slave_list,
        ]);
    }

    /**
     * 二维数组根据某个字段排序
     * @param array $array 要排序的数组
     * @param string $keys   要排序的键字段
     * @param string $sort  排序类型  SORT_ASC     SORT_DESC 
     * @return array 排序后的数组
     */
    public function arraySort($array, $keys, $sort = SORT_DESC)
    {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }
}
