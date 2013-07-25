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
	 * twitterAPIにアクセスしてツイートを取得
	 * https://dev.twitter.com/docs/api/1.1/get/search/tweets
	 * @param string $keyword
	 * @return array
	 */
	public function search($param) {
		$default = array(
			'q' => '', // A UTF-8, URL-encoded search query of 1,000 characters maximum, including operators.
			'geocode' => '', // Example Values: 37.781157,-122.398720,1mi(km)
			'lang' => '', // Example Values: eu
			'locale' => '', // (only ja is currently effective).
			'result_type' => 'mixed', // Example Values: mixed, recent, popular
			'count' => '', // The number of tweets to return per page, up to a maximum of 100. Defaults to 15.
			'until' => '', // Returns tweets generated before the given date. Example Values: 2012-09-01
			'since_id' => '', // Returns results with an ID greater than (that is, more recent than) the specified ID.
			'max_id' => '', // Returns results with an ID less than (that is, older than) or equal to the specified ID.
			'include_entities' => '', // The entities node will be disincluded when set to false.
			'callback' => '', // If supplied, the response will use the JSONP format with a callback of the given name.
		);

		//#TODO 使うかはどこで判断？
		/* OAuth 2 Bearer Token Use Application-only authentication */
		$this->connection->getBearerToken();

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