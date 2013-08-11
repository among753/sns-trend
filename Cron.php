<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2013/08/08
 * Time: 23:17
 */

namespace SnsTrend;

use SnsTrend\Model\Posts;

/**
 * wp-cron.phpで定期的に実行される。
 *
 * wp-cron.phpを5分間隔でキックする。
 * *\/5 * * * * cd /WordPressDir ; php -q wp-cron.php > /dev/null >2>&1
 * *\/5 * * * * wget -q --spider http://localhost/wp-cron.php > /dev/null
 *
 * Class Cron
 * @package SnsTrend
 */
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

		//TODO rate limit を考慮する

		$this->twitter->getAccessToken();

		$posts = $this->posts->get_posts_trends();
		foreach ($posts as $post) {

			$trend_keywords = get_post_meta( $post->ID , $this->posts->meta["trend_keywords"] , true );

			$param = array(
				'q' => Twitter::consolidatedQuery($post->post_title, $trend_keywords),
				'count' => '100', // The number of tweets to return per page, up to a maximum of 100. Defaults to 15.
			);
			$result = $this->twitter->search($param);
			echo "x_rate_limit_remaining: "; var_dump( $this->twitter->connection->http_header['x_rate_limit_remaining'] );
//			trigger_error( "x_rate_limit_remaining: " . $this->twitter->connection->http_header['x_rate_limit_remaining'] );



			// 取得データを保存
			$result = $this->twitter->save($post, $result);
//			echo "save: "; var_dump($result);

		}

	}

	public function filter_cron_schedules( $schedules ) {
		return $schedules = array(
			'5minute'      => array( 'interval' => 5 * MINUTE_IN_SECONDS,       'display' => __( 'Five minute' ) ),
//			'5minute'      => array( 'interval' => 10 * MINUTE_IN_SECONDS / MINUTE_IN_SECONDS,       'display' => __( 'Five minute' ) ),
		);
	}

}