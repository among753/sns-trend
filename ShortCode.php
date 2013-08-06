<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2013/08/03
 * Time: 19:04
 */

namespace SnsTrend;
use SnsTrend\Model\Trends;

/**
 * Class ShortCode
 * @package SnsTrend
 */
class ShortCode {

	/**
	 * @var Trends
	 */
	protected $trends;

	public function __construct() {

		$this->trends = new Trends();


		add_shortcode('sns-trend-list', array($this, 'snsTrendList'));
	}

	public function snsTrendList($atts) {
		global $post;
		$post_id   = $post->ID;
		$trend_type = 'twitter';
		$count = 10;
		extract(shortcode_atts(array(
			'post_id'    => $post_id,
			'trend_type' => $trend_type,
			'count'      => $count
		), $atts));



		//#TODO post_type post_id で絞り込み検索
		$result = $this->trends->get(array(
			$this->trends->trend_type => $trend_type,
			$this->trends->post_id => $post_id
		),null,10);
		//var_dump($result);

		//#TODO 表示
		foreach ($result as $tweet) {
			$trend_data = unserialize($tweet->trend_data);
//			var_dump($trend_data);
			Twitter::render_twitter_list(array($trend_data));
		}

		return $post_id;
	}

}
