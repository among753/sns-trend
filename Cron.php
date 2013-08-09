<?php
/**
 * Created by PhpStorm.
 * User: KS
 * Date: 2013/08/08
 * Time: 23:17
 */

namespace SnsTrend;


use SnsTrend\Model\Posts;

class Cron {

	const MY_SCHEDULE = 'my_schedule';


	/**
	 * @var Model\Posts
	 */
	protected $posts;

	/**
	 * @var Twitter
	 */
	protected $twitter;

	public function __construct() {

		$this->posts = new Posts();
		$this->twitter = new Twitter();


		//TODO cron schedule
		add_action(self::MY_SCHEDULE, array($this, 'do_schedule'));
		//　cron_schedulesに追加
		add_filter( 'cron_schedules', array($this, 'filter_cron_schedules') );
	}

	public function do_schedule() {
		set_time_limit(180);// time out 防止

		//TODO Twitterからデータを取得してDBに保存する。
		$this->twitter->getAccessToken();

		$posts = $this->posts->get_posts_trends();
		foreach ($posts as $post) {

			$trend_keywords = get_post_meta( $post->ID , $this->posts->meta["trend_keywords"] , true );

			$param = array(
				'q' => Twitter::consolidatedQuery($post->post_title, $trend_keywords),
				'count' => '100', // The number of tweets to return per page, up to a maximum of 100. Defaults to 15.
			);
			$result = $this->twitter->search($param);

			// 取得データを保存
			$this->twitter->save($post, $result);

//			trigger_error(print_r($result,true));
//			trigger_error(print_r($posts,true));

		}

	}

	public function filter_cron_schedules( $schedules ) {
		return $schedules = array(
//			'5minute'      => array( 'interval' => 5 * MINUTE_IN_SECONDS,       'display' => __( 'Five minute' ) ),
			'5minute'      => array( 'interval' => 5 * 2 * MINUTE_IN_SECONDS / MINUTE_IN_SECONDS,       'display' => __( 'Five minute' ) ),
		);
	}

}