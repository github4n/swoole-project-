<?php

/**
 * Class BureauData
 * @description 彩票局数据查询类，本接口数据暂不区分体系数据为报表总数据
 * @author Kayden
 * @date 2019-04-08
 * @link Websocket：Lottery/BureauData/BureauData {"game_key":"","period":""}
 * @param string $game_key 彩种Key
 * @param string $period 期号
 * @modifyAuthor Kayden
 * @modifyTime 2019-04-08
 */

namespace Site\Websocket\Lottery\BureauData;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BureauData extends CheckLogin
{
  public function onReceiveLogined(Context $context, Config $config)
  {
    $staffId = $context->getInfo('StaffId');
    $staffGrade = $context->getInfo('StaffGrade');
    $data = $context->getData();

    //检查权限
    $auth = json_decode($context->getInfo('StaffAuth'));
    if(!in_array('game_period_report', $auth)) {
        $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);
        return;
    }

    //接收数据
    $game_key = empty($data['game_key']) ? null : $data['game_key'];
    $period = empty($data['period']) ? null : $data['period'];

    //查询彩票列表
    $public_mysql = $config->data_public;
    $lottery_list = [];
    $game_list = [];
    $model_list = json_decode($context->getInfo('ModelList'), true);
    $game_sql = 'Select `game_key` From `lottery_game` Where `model_key` = :model_key';
    foreach ($model_list as $k => $v) {
      $game_list[$k]['model_name'] = $v['model_name'];
      $game_param = [':model_key' => $v['model_key']];
      foreach ($public_mysql->query($game_sql, $game_param) as $v_2) {
        $lottery_list[] = [
          'game_key' => $v_2['game_key'],
          'game_name' => $context->getInfo($v_2['game_key'])
        ];
      }
      $game_list[$k]['game_list'] = $lottery_list;
      unset($lottery_list);
    }

    //搜索条件
    $period_search = Null;
    $game_key_search = Null;
    $paramSearch = [];
    if (!empty($game_key)) {
      if (empty($context->getInfo($game_key))) {
        $context->reply(['status' => 202, 'msg' => '彩票名称错误', 'data' => $game_key]);
        return;
      }
      $game_key_search = ' And `game_key` = :game_key';
      $paramSearch[':game_key'] = $game_key;
    }

    if (!empty($period)) {
      $period_search = ' And `period` = :period';
      $paramSearch[':period'] = $period;
    }

    // 获取一千条局数据
    $mysqlReport = $config->data_report;
    $list = [];
    $sql = 'Select `game_key`,`period`,`open_time`,`user_count`,`bet_count`,`wager_count`,`bet_amount`,`wager_amount`,`rebate_amount`,`bonus_amount`,`profit_amount` From `lottery_period` Where 1 ' . $game_key_search . $period_search . ' Order By `open_time` Desc Limit 1000';

    foreach($mysqlReport->query($sql, $paramSearch) as $v) {
      $v['open_time'] = date('Y-m-d H:i:s', $v['open_time']);
      // 保留小数点后两位
      $v['bet_amount'] = $this->intercept_num($v['bet_amount']);
      $v['bonus_amount'] = $this->intercept_num($v['bonus_amount']);
      $v['profit_amount'] = $this->intercept_num($v['profit_amount']);
      $v['rebate_amount'] = $this->intercept_num($v['rebate_amount']);
      $v['wager_amount'] = $this->intercept_num($v['wager_amount']);
      $list[] = $v;
    }
    
    $context->reply([
      'status' => 200,
      'msg' => '数据获取成功',
      'game_list' => $game_list,
      'list' => $list
    ]);
  }
}