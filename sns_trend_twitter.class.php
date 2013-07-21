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
//#TODO optionに格納する
// access token is geted from https://dev.twitter.com/apps
		define('CONSUMER_KEY', 'xxxxxxxxxxxxxx');
		define('CONSUMER_SECRET', 'xxxxxxxxxxxxxxxxxxxxxxx');

// callback
		define('OAUTH_CALLBACK', 'http://example.com/twitteroauth/callback.php');

// access token is geted from https://dev.twitter.com/apps
		define('OAUTH_TOKEN', 'xxxxxxxxxxxxxxxxxxxx');
		define('OAUTH_TOKEN_SECRET', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxx');

// proxy
		define('PROXY_URL', 'http://proxyurl:80');

	}

	public function search($keyword='') {
		//#TODO search_tweet() twitterAPIにアクセスしてツイートを取得

		$q = ($keyword) ? $keyword : "#GitHub";
		$param = array("q" => urlencode($q), "count" => "20");


		/* Create a TwitterOauth object with consumer/my application tokens. */
		//$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

		/* Create a TwitterOauth object with consumer */
		$connection = new \TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

		/* Proxy Setting */
		//$connection->setProxy(PROXY_URL);

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