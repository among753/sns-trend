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
	public $options = array(
		'consumer_key'                => '',
		'consumer_secret'             => '',
		'access_token'                => '',
		'access_token_secret'         => '',
		'bearer_access_token'         => '',
		'bearer_access_token_expired' => ''
	);
	public $consumer_key;
	public $consumer_secret;
	public $access_token;
	public $access_token_secret;
	public $bearer_access_token;
	public $bearer_access_token_expired;
	/**
	 * @var int Bearer Token 有効期限
	 */
	public $expired = 660;

	/**
	 * @var \TwitterOAuth
	 */
	public $connection;

	public function __construct() {

		if(!class_exists('TwitterOAuth'))
			require_once( SNS_TREND_ABSPATH . '/libs/twitteroauth/twitteroauth.php' );

		if (!class_exists('Twitter_Autolink'))
			require_once( SNS_TREND_ABSPATH . '/libs/Twitter/Autolink.php');


		// optionから取得しセット
		$this->setProperty();

		/* Create a TwitterOauth object with consumer/my application tokens. */
		$this->connection = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->access_token, $this->access_token_secret);

		/* Proxy Setting */
		$proxy = new WP_HTTP_Proxy();
		if ($proxy->is_enabled()) {
			//#TODO FIX twitteroauth.php に合わす setProxy($host, $port=80, $id='', $pass='')
			$url = 'http://';
			if ($proxy->use_authentication())
				$url .= $proxy->username() . ":" . $proxy->password() . "@";
			$url .= $proxy->host() . ":" . $proxy->port();
			$this->connection->setProxy($url);
		}

		//#TODO Bearer Token optionsに保存 sns_trend_twitter['bearer_access_token'] sns_trend_twitter['bearer_access_token_expired']
		/* OAuth 2 Bearer Token Use Application-only authentication */
		// 期限切れを確認
		if (strtotime($this->bearer_access_token_expired) + $this->expired < date_i18n('U')) {
			// Bearer Token 無効化
			$bet = $this->connection->getBearerToken();
			echo "getBearerToken() "; var_dump($bet);
			var_dump($this->connection);

			$inv = $this->connection->invalidateBearerToken( $bet );
			echo "invalidateBearerToken():"; var_dump($inv);
			var_dump($this->connection);

			sleep(4);

			// 再発行
			$this->bearer_access_token = $this->connection->getBearerToken();
			if (is_string($this->bearer_access_token)) {
				// optionsに保存
				$this->options['bearer_access_token'] = $this->bearer_access_token;
				$this->options['bearer_access_token_expired'] = current_time('mysql');
				update_option($this->option_name, $this->options);
			}
			echo "再発行："; var_dump($this->bearer_access_token);
		} else {
			$this->connection->setBearerToken($this->bearer_access_token);
			echo "optionsからセット："; var_dump($this->bearer_access_token);
		}



	}

	protected function setProperty() {
		if ( $options = get_option($this->option_name) ) {
			$this->options = array_merge($this->options, $options);
			foreach ($this->options as $key => $value) {
				$this->$key = $value;
			}
			var_dump($this->options);
			if ($this->consumer_key && $this->consumer_secret) {
				return true;
			}
		}
		return false;
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


//		$invalidate_bearer_token = $this->connection->invalidateBearerToken($bearer_token);
//		var_dump($invalidate_bearer_token);


		$result = $this->connection->get('search/tweets', $param);
		if (isset($result->errors)) {
			var_dump($result);
			$this->options['bearer_access_token_expired'] = date_i18n("Y-m-d H:i:s", time() - $this->expired);
			update_option($this->option_name, $this->options);
			return "Bearer Tokenが無効だったので有効期限を切らす";


		} else {
			var_dump( $this->connection->http_header['x_rate_limit_remaining'] );
			return $this->tweet = $result;
		}
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