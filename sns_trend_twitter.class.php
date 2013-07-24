<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 2013/07/21
 * Time: 22:24
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;

use WP_HTTP_Proxy;
use TwitterOAuth;

class SnsTrendTwitter {

	public $keyword             = 'ワードプレス';

	public $tweet               = array();

	public $option_name         = 'sns_trend_twitter';

	public $consumer_key        = '';
	public $consumer_secret     = '';
	public $access_token        = '';
	public $access_token_secret = '';

	public $connection;

	public function __construct() {

		if(!class_exists('TwitterOAuth'))
			require_once( SNS_TREND_ABSPATH . '/libs/twitteroauth/twitteroauth.php' );

		if (!class_exists('Twitter_Autolink'))
			require_once( SNS_TREND_ABSPATH . '/libs/Twitter/Autolink.php');

		// optionから取得
		if ( $sns_trend_twitter = get_option($this->option_name) ) {
			$this->consumer_key = $sns_trend_twitter['consumer_key'];
			$this->consumer_secret = $sns_trend_twitter['consumer_secret'];
			$this->access_token = $sns_trend_twitter['access_token'];
			$this->access_token_secret = $sns_trend_twitter['access_token_secret'];

			/* Create a TwitterOauth object with consumer/my application tokens. */
			$this->connection = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->access_token, $this->access_token_secret);

			/* Proxy Setting */
			$proxy = new WP_HTTP_Proxy();
			if ($proxy->is_enabled()) {
				$url = 'http://';
				if ($proxy->use_authentication())
					$url .= $proxy->username() . ":" . $proxy->password() . "@";
				$url .= $proxy->host() . ":" . $proxy->port();
				$this->connection->setProxy($url);
			}

			//var_dump($this->connection);
		}


	}


	/**
	 *
	 * @param string $keyword
	 * @return array
	 */
	public function search($keyword='') {
		//#TODO twitterAPIにアクセスしてツイートを取得 パラメータからキーワードを受け取る

		//#TODO 使うかはどこで判断？
		/* OAuth 2 Bearer Token Use Application-only authentication */
		$this->connection->getBearerToken();

		if ($keyword) $this->keyword = $keyword;
		$param = array(
			"q" => urlencode($this->keyword),
			"count" => "20"
		);

		$this->tweet = $this->connection->get('search/tweets', $param);

		return $this->tweet;
	}

	public function set_db() {
		//#TODO set_db() trendsテーブルにデータを格納 どこに持たすか trend_data.class

	}

	/**
	 * @return array
	 */
	public function getTweet()
	{
		return $this->tweet;
	}

}