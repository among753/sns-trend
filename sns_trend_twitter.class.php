<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 2013/07/21
 * Time: 22:24
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;


class SnsTrendTwitter {

	public $keyword = 'ワードプレス';

	public $tweet = array();

	public function __construct() {

		if(!class_exists('TwitterOAuth'))
			require_once( SNS_TREND_ABSPATH . '/libs/twitteroauth/twitteroauth.php' );

		/* Autolink */
		if (!class_exists('Twitter_Autolink'))
			require_once( SNS_TREND_ABSPATH . '/libs/Twitter/Autolink.php');


		$this->init();
		//$this->search($this->keyword);
		//$this->set_db();
		//$this->get_tweet();

	}

	public function init() {

		//#TODO init() 初期化 パラメータからキーワードを受け取る config

	}


	public function search($keyword='') {
		//#TODO search_tweet() twitterAPIにアクセスしてツイートを取得

		$q = ($keyword) ? $keyword : "#GitHub";
		$param = array("q" => urlencode($q), "count" => "20");


		/* Create a TwitterOauth object with consumer/my application tokens. */
		//$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

		/* Create a TwitterOauth object with consumer */
		$sns_trend_twitter = get_option('sns_trend_twitter');
		//var_dump($sns_trend_twitter);
		$connection = new \TwitterOAuth($sns_trend_twitter['consumer_key'], $sns_trend_twitter['consumer_secret']);

		/* Proxy Setting */
		if (defined('WP_PROXY_HOST')) {
			$proxy = (defined('WP_PROXY_PORT')) ? WP_PROXY_HOST.":".WP_PROXY_PORT : WP_PROXY_HOST;
			$connection->setProxy($proxy);
		}

		/* OAuth 2 Bearer Token */
		$connection->getBearerToken();

		$this->tweet = $connection->get('search/tweets', $param);

		var_dump($connection->http_header['x_rate_limit_remaining']);

		return $this->tweet;
	}

	public function set_db() {
		//#TODO set_db() trendsテーブルにデータを格納 どこに持たすか

	}

	/**
	 * APIで取得したデータを返す
	 * @return array
	 */
	public function get_tweet() {
		return $this->tweet;
	}

}