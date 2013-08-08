<?php
/**
 * Created by PhpStorm.
 * User: KS
 * Date: 2013/08/08
 * Time: 23:17
 */

namespace SnsTrend;


class Cron {

	const MY_SCHEDULE = 'my_schedule';

	public function __construct() {
		//TODO cron schedule
		add_action(self::MY_SCHEDULE, array($this, 'do_this_hourly'));
		//　cron_schedulesに追加
		add_filter( 'cron_schedules', array($this, 'filter_cron_schedules') );
	}

	public function do_this_hourly() {
		// do something every hour
		trigger_error('クーロン動いてる？');

		//TODO Twitterからデータを取得してDBに保存する。
		$twitter = new Twitter();



	}

	public function filter_cron_schedules( $schedules ) {
		return $schedules = array(
			'5minute'      => array( 'interval' => 5 * MINUTE_IN_SECONDS,       'display' => __( 'Five minute' ) ),
//			'5minute'      => array( 'interval' => 5,       'display' => __( 'Five minute' ) ),
		);
	}

}