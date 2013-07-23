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

	public $keyword             = 'ワードプレス';

	public $tweet               = array();

	public $option_name         = 'sns_trend_twitter';

	public $consumer_key        = '';
	public $consumer_secret     = '';
	public $access_token        = '';
	public $access_token_secret = '';

	public $connection = null;




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

		// optionから取得
		if ( $sns_trend_twitter = get_option($this->option_name) ) {
			$this->consumer_key = $sns_trend_twitter['consumer_key'];
			$this->consumer_secret = $sns_trend_twitter['consumer_secret'];
			$this->access_token = $sns_trend_twitter['access_token'];
			$this->access_token_secret = $sns_trend_twitter['access_token_secret'];

			/* Create a TwitterOauth object with consumer/my application tokens. */
			$this->connection = new \TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->access_token, $this->access_token_secret);

			/* Proxy Setting */
			if (defined('WP_PROXY_HOST')) {
				$proxy = (defined('WP_PROXY_PORT')) ? WP_PROXY_HOST.":".WP_PROXY_PORT : WP_PROXY_HOST;
				$this->connection->setProxy($proxy);
			}

			//#TODO 使うかはどこで判断？
			/* OAuth 2 Bearer Token */
			$this->connection->getBearerToken();// Use Application-only authentication
			//var_dump($this->connection);
		}


	}


	/**
	 *
	 * @param string $keyword
	 * @return array
	 */
	public function search($keyword='') {
		//#TODO search_tweet() twitterAPIにアクセスしてツイートを取得 パラメータからキーワードを受け取る

		$q = ($keyword) ? $keyword : "#GitHub";
		$param = array("q" => urlencode($q), "count" => "20");




		$this->tweet = $this->connection->get('search/tweets', $param);

		var_dump($this->connection->http_header['x_rate_limit_remaining']);

		return $this->tweet;
	}

	public function set_db() {
		//#TODO set_db() trendsテーブルにデータを格納 どこに持たすか trend_data.class

	}

	/**
	 * APIで取得したデータを返す
	 * @return array
	 */
	public function get_tweet() {
		return $this->tweet;
	}

}