<?php

/**
 * Class StationSwitchSetting
 * @description 全站开关设置详情类
 * @author Black
 * @date 2019-03-08
 * @link Websocket: Website/Setting/StatuibSwichSetting
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Setting;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class StationSwitchSetting extends CheckLogin {

    public function onReceiveLogined(Context $context, Config $config) {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限']);
            return;
        }
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('web_acceptable', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);
            return;
        }

        //仅能展示以及修改公告数据
        $mysql_staff = $config->data_staff;
        $mysql_user = $config->data_user;
        $mysql_public = $config->data_public;
        $cache = $config->cache_site;
        $lastDta = [];
        $site_name_sql = "select str_value  from site_setting where setting_key ='site_name' ";
        $site_name = iterator_to_array($mysql_staff->query($site_name_sql));
        $site_status_sql = "select int_value  from site_setting where setting_key ='site_status' ";
        $site_status = iterator_to_array($mysql_staff->query($site_status_sql));
        $onLineUser_sql = "select count(lose_time=0 or null ) as userNumber from user_session ";
        $user_number = iterator_to_array($mysql_user->query($onLineUser_sql));
        $lastDta['site_data'] = [
            'site_name' => $site_name[0]['str_value'] ? $site_name[0]['str_value'] : '',
            'online_user' => $user_number[0]['userNumber'] ? $user_number[0]['userNumber'] : 0,
            'site_status' => $site_status[0]['int_value'] ? $site_status[0]['int_value'] : 0
        ];

        $maintainData['lottery'] = [];
        $maintainData['external']=[];
        $normalData = [];
        $game_local_sql = "select * from  lottery_game where  acceptable !=1 ";
        $play_local_sql = "select * from  lottery_game_play where  acceptable !=1 ";
        $game_external_sql = "select * from  external_game  where  acceptable !=1  ";
        $game_local_normal = iterator_to_array($mysql_staff->query($game_local_sql));
        $play_local_normal = iterator_to_array($mysql_staff->query($play_local_sql));
        $game_external = iterator_to_array($mysql_staff->query($game_external_sql));

        $normal_lottery_sql = "select count(official=1 or null) as official_number,count(official!=1 or null) as local_number  from  lottery_game  ";
        $normal_external_sql = "select interface_key,count(acceptable=1 or null) as external_number from  external_game group by interface_key ";
        $normal_external = iterator_to_array($mysql_staff->query($normal_external_sql));
        $normal_lottery_sata = iterator_to_array($mysql_staff->query($normal_lottery_sql));
        $game_play_sql="select play_key,play_name from lottery_play";
        $game_play_list = iterator_to_array($mysql_public->query($game_play_sql));
        $gameList=[];

        // 所有游戏的名称
        $lottery = [];
        $allGame = [];
        $sql = 'Select `game_key`,`game_name` From `lottery_game`';
        foreach($mysql_public->query($sql) as $v) {
            $lottery[$v['game_key']] = $v['game_name'];
        }
        $sql = 'Select `game_key`,`game_name` From `external_game`';
        foreach($mysql_public->query($sql) as $v) {
            $allGame[$v['game_key']] = $v['game_name'];
        }

        foreach($game_play_list as $game){
            $gameList+=[$game['play_key']=>$game['play_name']];
        }
        if (!empty($game_local_normal)) {
            foreach ($game_local_normal as $loclaGame) {
                $game_name = $lottery[$loclaGame['game_key']];
                $maintainData['lottery'][] = ['game_key' => $loclaGame['game_key'], 'play_key' => '', 'game_name' => $game_name];
            }
        }
        if (!empty($play_local_normal)) {
            foreach ($play_local_normal as $play_local_Game) {   
               $game_name = $lottery[$play_local_Game['game_key']];
               $play_name=$gameList[$play_local_Game['play_key']];
                $maintainData['lottery'][] = ['game_key' => $play_local_Game['game_key'], 'play_key' => $play_local_Game['play_key'],'game_name' => $game_name . '-' . $play_name];
            }
        }

        if (!empty($game_external)) {
            foreach ($game_external as $game_external) {
                switch ($game_external['category_key']) {
                        case 'video';
                            $category_name = '视讯';
                            break;
                        case 'game';
                            $category_name = '游戏';
                            break;
                        case 'cards';
                            $category_name = '棋牌';
                            break;
                        case 'hunter';
                            $category_name = '捕猎';
                            break;
                        case 'sports';
                            $category_name = '体育';
                            break;
                    }
                    switch ($game_external['interface_key']) {
                        case 'fg';
                            $category_nameTranslation = 'FG电子';
                            break;
                        case 'ky';
                            $category_nameTranslation = '开元棋牌';
                            break;
                        case 'lb';
                            $category_nameTranslation = 'Lebo体育';
                            break;
                        case 'ag';
                            $category_nameTranslation = 'AG视讯';
                            break;
                    }
                $gameName = $allGame[$game_external['game_key']];
                $maintainData['external'][] = ['platform_key' => $game_external['interface_key'], 'game_key' => $game_external['game_key'],"game_name"=>$category_nameTranslation."-".$gameName];
            }
        }

        if (!empty($normal_external)) {
            foreach ($normal_external as $externalNumber) {
                switch ($externalNumber['interface_key']) {
                        case 'fg';
                            $category_nameTranslation = 'FunGaming';
                            break;
                        case 'ky';
                            $category_nameTranslation = '开元棋牌';
                            break;
                        case 'lb';
                            $category_nameTranslation = 'Lebo体育';
                            break;
                        case 'ag';
                            $category_nameTranslation = 'AsiaGaming';
                            break;
                    }
                $normalData['interface'][] = [
                    'interface_key' => $externalNumber['interface_key'],
                    'external_number' => $externalNumber['external_number'],
                    'interface_name' => $category_nameTranslation,
                ];
            }
        }
        if (!empty($normal_lottery_sata)) {
            $normalData['lottery'][] = [
                "interface_name"=>"官方彩票",
                'external_number' => $normal_lottery_sata[0]['official_number']
            ];
            $normalData['lottery'][] = [
                "interface_name"=>"自开彩",
                'external_number' =>  $normal_lottery_sata[0]['local_number']
            ];
            
        }

        $announcement_sql = "select * from  site_setting  where setting_key='maintenance_announcement'";
        $announcement_data = iterator_to_array($mysql_staff->query($announcement_sql));
        if (empty($announcement_data)) {
            $announcement_sql = "insert site_setting (setting_key,description,data_type,str_value) values('maintenance_announcement','维护公告',2,'')";
            $mysql_staff->execute($announcement_sql);
            $announcement = '';
        } else {
            $announcement = $announcement_data[0]['str_value'];
        }
        $lastDta['maintainData'] = $maintainData;
        $lastDta['site_data'] += ['announcement'=>$announcement];
        $lastDta['normalData'] = $normalData;
        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $lastDta]);
        return;
    }

}
