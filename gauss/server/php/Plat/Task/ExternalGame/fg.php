<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 19-3-7
 * Time: 上午11:01
 */
namespace Plat\Task\ExternalGame;
use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;
use Lib\Task\Adapter;
class fg implements IHandler
{
    private $result = [
        'status' => 200,
        'msg' => '',
        'data' => []
    ];
    public function onTask(Context $context, Config $config)
    {
        [ 'data'  =>  $param ,'site_key' => $site_key] = $context->getData();
        $action = isset($param['action']) ? $param['action'] : '';
        if (!method_exists($this, $action)) {
            return;
        }
        $url = $config->fg_apiurl;
        $header = [
            "Accept:application/json",
            "merchantname:$config->fg_merchantname",
            "merchantcode:$config->fg_merchantcode"
        ];
        $param['site_key'] = $site_key;
        $adapter = new Adapter($config->cache_daemon);
        $result = $this->$action($url,$header,$param);
        if ($action == 'get_log_page') {
            $adapter->plan('NotifySite',['path' => 'ExternalGame/FgGameLog','data'=>['data' => $result]]);
            return;
        }
        $adapter->plan('NotifySite',['path'=>'ExternalGame/ExternalGameReceive','data' => ['data'=>$result]]);

    }

    /*
     * 启动游戏
     * openid       [必填]String  Players unique ID
     * game_code    [必填]String  Gamecode
     * game_type    [必填]String  as or h5
     * language     [必填]String  zh_CN or en
     * ip           [必填]String  Players client ip
     * return_url   [必填]String  Agent lobby url.
    */
    function launch_game($url,$header,$param) {
        $member_code =  $param['site_key'] . $param['interface_key'] . $param['user_key'];
        $password = md5($param['interface_key'] . $param['user_key']);
        //检查玩家是否存在
        $player_exist = $this->is_player_exist($url,$header,$member_code);
        //如果存在直接返回openid和相关数据
        if (isset($player_exist['res']->openid)) {
            $openid = $player_exist['res']->openid;
        } else {
            //不存在则创建
            $create_player = $this->create_new_player($url,$header,$member_code,$password);
            $openid = isset($create_player['res']->openid) ? $create_player['res']->openid : '';
        }
        //openid不为空
        if (!empty($openid)) {
            $data_arr['member_code'] = $member_code;
            $data_arr['game_code'] = $param['game_code'];
            $data_arr['game_type'] = $param['game_type'];
            $data_arr['language'] = $param['language'];
            $data_arr['ip'] = $param['ip'];
            $data_arr['return_url'] = $param['return_url'];
            $data_arr['owner_id'] = 0;
            $real_url = $url . '/v2/launch_game/';
            $res =  $this->http_curl($real_url, $header, 'GET', $data_arr);
            if (isset($res['res']->error_code)) {
                $this->result['status'] = $res['res']->error_code;
                $this->result['data'] = [
                    'user_id' => $param['user_id'],
                    'client_id' => $param['client_id'],
                    'interface_key' => $param['interface_key'],
                    'method' => $param['method'],
                    'fg_openid' => $openid,
                    'fg_member_code' => $member_code,
                    'fg_password' => $password,
                ];
                return $this->result;
            }

            $this->result['data'] = [
                'user_id' => $param['user_id'],
                'game_url' => $res['res']->game_url,
                'name' => $res['res']->name,
                'token' => $res['res']->token,
                'meta' => isset($res['res']->meta) ? $res['res']->meta : '',
                'client_id' => $param['client_id'],
                'method' => $param['method'],
                'interface_key' => $param['interface_key'],
                'fg_openid' => $openid,
                'fg_member_code' => $member_code,
                'fg_password' => $password,
            ];
            return $this->result;
        } else {
            $this->result['status'] = 403;
            $this->result['data'] = [
                'client_id' => $param['client_id'],
                'interface_key' => $param['interface_key'],
                'method' => $param['method'],
            ];
        }

    }


    /************************************************************************************** /

    /********************************** Log/游戏记录 ************************************** /

    /*
     * 分页采集数据
     * 第一次采集可以使用 : agent_log_by_page($gt)
     * 第二次根据第一次返回的page_key使用: agent_log_by_page($gt, $page_key)
     * 如果中间page_key丢失了,请使用上一次最新的数据id: agent_log_by_page($gt, $page_key,$id)
     * 如果已经拿到的数据是最新的那么page_key会返回none; id不存在page_key也返回none
     * gt           [必填]String    fish/poker/slot/fruit.
     * id           [非必填]String  the response field of game log.
     * page_key     [非必填]String  Page key for game log.
     * start_time   [非必填]Number  unix时间戳整数 长度 10.
     * end_time     [非必填]Number  unix时间戳整数 长度 10.
     */
    function get_log_page($url,$header,$param) {
        $gt = isset($param['gt']) ? $param['gt'] : '';
        $id = isset($param['id']) ? $param['id'] : '';
        $page_key = isset($param['page_key']) ? $param['page_key'] : '';
        $start_time  = isset($param['start_time']) ? $param['start_time'] : '';
        $end_time = isset($param['end_time']) ? $param['end_time'] : '';
        $action = '/v2/agent/log_by_page/gt/'.$gt;
        if($id){
            $action = $action.'/id/'.$id;
        }
        if($page_key){
            $action = $action.'/page_key/'.$page_key;
        }
        if($start_time && $end_time){
            $action = $action.'/start_time/'.$start_time.'/end_time/'.$end_time;
        }
        $real_url = $url.$action;
        $res =  $this->http_curl($real_url,$header,'GET');
        $res['gt'] = $gt;
        return $res;
    }

    /*
     * 获取指定起始时间范围内玩家下注记录的id列表
     * start_time和end_time 是unix时间戳 两个时间间隔不能超过2天
     * gt           [非必填]String  fish/rp/slot,default fish.
     * start_time   [必填]Number    unix时间戳整数 长度 10.
     * end_time     [必填]Number    unix时间戳整数 长度 10.
    */
    function get_log_ids($url,$header,$param) {
        $start_time = time() - 86400;
        $end_time = time();
        $gt = isset($param['gt']) ? $param['gt'] : 'fish';
        $real_url = $url . '/v2/agent_game_log_by_time/start_time/'.$start_time.'/end_time/'.$end_time.'/gt/'.$gt;
        return $this->http_curl($real_url, $header, 'GET');
    }

    /*
     * 获取指定起始时间范围内游戏记录数
     * gt           [非必填]String  fish/poker/slot/fruit.
     * start_time   [必填]Number    unix时间戳整数 长度 10.
     * end_time     [必填]Number    unix时间戳整数 长度 10.
    */
    function get_log_counts($gt = 'fish', $start_time, $end_time) {
        $real_url = self::$url .'agent/log_by_count/gt/'.$gt.'/start_time/'.$start_time.'/end_time/'.$end_time;
        return $this->http_curl($real_url, self::$header, 'GET');
    }

    /*
     * 获取指定起始时间范围内游戏记录详细数据
     * gt           [非必填]String  fish/poker/slot/fruit.
     * start_time   [必填]Number    unix时间戳整数 长度 10.
     * end_time     [必填]Number    unix时间戳整数 长度 10.
    *  size         [必填]Number    取值范围:1 - 3000
    */
    function get_log_datas($gt = 'fish', $start_time, $end_time, $size) {
        $real_url = self::$url.'agent/log_by_time/gt/'.$gt.'/start_time/'.$start_time.'/end_time/'.$end_time.'/size/'.$size;
        return $this->http_curl($real_url, self::$header, 'GET');
    }

    /*
     * 通过局id获取该局游戏记录详细数据
     * gt           [必填]String    rp, only support rp..
     * id           [必填]Number    取值范围:1 - 3000
    */
    function get_log_detail($gt = 'rp', $id ) {
        $real_url = self::$url.'agent_game_detail_by_id/id/'.$id.'/gt/'.$gt;
        return $this->http_curl($real_url, self::$header, 'GET');
    }

    /************************************* Player ***************************************** /
    /*
     * 重置玩家密码
     * user_name    [必填]String    username of the player
     * password     [必填]Number    new password.
     */
    function reset_player_password($user_name, $password ) {
        $data_arr['password'] = $password;
        $real_url = self::$url.'player_pwds/'.$user_name;
        return $this->http_curl($real_url, self::$header, 'PUT', $data_arr);
    }

    /*
     * 创建新玩家
     * member_code  [必填]String    Username of the Player 取值范围: 5,32.
     * password     [必填]String    Password of the Player 取值范围: 5,40.
    */
    function create_new_player($url, $header,$member_code,$password) {
        $data_arr['member_code'] = $member_code;
        $data_arr['password'] = $password;
        $real_url = $url.'/v2/players/';
        return $this->http_curl($real_url, $header, 'PUT', $data_arr);
    }


    /*
     * 存取玩家筹码
     * openid  [必填]String    Players unique ID.
     * amount  [必填]Number    Transfer negative or positive money using cents to descript
     * externaltransactionid   [必填]String    Transaction OrderID
    */
    function save_player_uchips($openid, $amount, $externaltransactionid ) {
        $real_url = self::$url.'player_uchips/'.$openid;
        $data['amount'] = $amount;
        $data['externaltransactionid'] = $externaltransactionid;
        return $this->http_curl($real_url, self::$header, 'PUT', $data);
    }

    /*
     * 捕猎排行派彩
     * game_id  [必填]Number   游戏记录id.
    */
    function player_fish_top($game_id) {
        $real_url = self::$url.'fish/player_rank/game_id/'.$game_id;
        return $this->http_curl($real_url,self::$header, 'GET');
    }

    /*
     * 查询玩家筹码
     * openid  [必填]String   Players unique ID.
    */
    function get_player_chips($openid) {
        $real_url = self::$url.'player_chips/'.$openid;
        return $this->http_curl($real_url,self::$header, 'GET');
    }

    /*
     * 检测玩家是否已经存在
     * username  [必填]String  Username of the Player.
    */
    function is_player_exist($url,$header,$username) {
        $real_url = $url .'/v2/player_names/'.$username;
        return $this->http_curl($real_url,$header, 'GET');
    }

    /*
     * 获取APP登录token二维码
     * openid  [必填]String   Players unique ID..
    */
    function get_token_qr($openid) {
        $real_url = self::$url.'app/get_token_qr/'.$openid;
        return $this->http_curl($real_url,self::$header, 'GET');
    }
    /************************************************************************************** /

    /*
     * curl 提交请求函数
     */
    function http_curl($url, $header=null, $type="GET", $body=null, $timeout='10000000', $returnHeader = 0){
        //1.创建一个curl资源
        $ch = curl_init();
        //2.设置URL和相应的选项
        curl_setopt($ch,CURLOPT_URL,$url);//设置url
        //1)设置请求头
        //设置为false,只会获得响应的正文(true的话会连响应头一并获取到)
        curl_setopt($ch, CURLOPT_HEADER,$returnHeader);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // 设置超时限制防止死循环
        //设置发起连接前的等待时间，如果设置为0，则无限等待。
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //3)设置提交方式
        switch($type){
            case "GET":
                curl_setopt($ch,CURLOPT_HTTPGET,true);
                break;
            case "POST":
                curl_setopt($ch,CURLOPT_POST,true);
                break;
            case "PUT"://使用一个自定义的请求信息来代替"GET"或"HEAD"作为HTTP请求。这对于执行"DELETE" 或者其他更隐蔽的HTT
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"PUT");
                break;
            case "DELETE":
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"DELETE");
                break;
        }
        //2)设备请求体
        if (is_array($body) && count($body) > 0 ) {
            if(is_array($body)){
                $formdata = http_build_query($body);
                //$formdata = json_encode($body);
            } else {
                $formdata = $body;
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $formdata);
        }
        //设置请求头
        if(count($header) > 0 && !empty($header)){
            curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        }
        //上传文件相关设置
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);// 从证书中检查SSL加密算

        //4)在HTTP请求中包含一个"User-Agent: "头的字符串。-----必设
        curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0.1)' ); // 模拟用户使用的浏览器

        //抓取URL并把它传递给浏览器
        $res = curl_exec($ch);
        // return $res;
        $http_code   = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        $resp['res']= json_decode($res);
        $resp['http_code']= $http_code;
        return $resp;
    }
}