<?php

namespace Site\Task\Index;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;
/** 
 * Banner.php
 * @description 检测滚动图是否过期任务
 * @Author Kayden 
 * @date 2019-05-09
 * @links Index/Banner 
 * @modifyAuthor nathan
 * @modifyTime 2019-05-09
 */
class Banner implements Ihandler
{
		public function onTask(Context $context, Config $config)
		{
				$now = time();
				// 查询未启用的滚动图
				$sql = 'Select `carousel_id` From `carousel` Where (`stop_time` <= :now Or `start_time` > :now) And `publish` = 1';
				$param = [':now' => $now];
				$mysql = $config->__get('data_staff');
				$sqlUpadte = 'Update `carousel` Set `publish` = 0 Where `carousel_id` = :carousel_id';
				foreach($mysql->query($sql, $param) as $v) {
						// 停用已到期或未启用的滚动图
						$param = [':carousel_id' => $v['carousel_id']];
						$mysql->execute($sqlUpadte, $param);
				}
				// 获取新的滚动图列表
				$sql = 'Select `carousel_id`,`img_src`,`link_type`,`link_data` From `carousel` Where `stop_time` > :now And `start_time` < :now And `publish` = 1';
				$param = [':now' => $now];
				$banner = iterator_to_array($mysql->query($sql, $param));
				$adapter = $context->getAdapter();
				$adapter->plan('NotifyApp', ['path' => 'Index/Banner', 'data' => ['banner' => $banner]]);
		}
}