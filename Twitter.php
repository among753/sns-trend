<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 2013/07/21
 * Time: 22:24
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;

use SnsTrend\Model\Posts;
use SnsTrend\Model\Trends;
use WP_HTTP_Proxy;
use TwitterOAuth;
use Twitter_Autolink;


if (!class_exists('Twitter_Autolink'))
	require_once( SNS_TREND_ABSPATH . '/libs/Twitter/Autolink.php');

if(!class_exists('TwitterOAuth'))
	require_once( SNS_TREND_ABSPATH . '/libs/twitteroauth/twitteroauth.php' );


/**
 * Class Twitter
 * @package SnsTrend
 */
class Twitter {

	/**
	 * @var TwitterOAuth
	 */
	public $connection;

	const TYPE = 'twitter';

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
	public $expired = 900;



	public $tweets = array();

	/**
	 * @var Trends
	 */
	public $trends;

	/**
	 * @var Posts
	 */
	protected $posts;



	public function __construct() {

		$this->trends = new Trends();
		$this->posts = new Posts();

		// optionから取得しセット
		$this->setProperty();

		/* Create a TwitterOauth object with consumer/my application tokens. */
		$this->connection = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->access_token, $this->access_token_secret);

		/* Proxy Setting */
		$proxy = new WP_HTTP_Proxy();
		if ( $proxy->is_enabled() ) $this->connection->setProxy($proxy->host(), $proxy->port());

	}

	protected function setProperty() {
		if ( $options = get_option($this->option_name) ) {
			$this->options = array_merge($this->options, $options);
			foreach ($this->options as $key => $value) {
				$this->$key = $value;
			}
			//echo "options:"; var_dump($this->options);//#TO#DO DEBUG
			if ($this->consumer_key && $this->consumer_secret) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Bearer Token optionsに保存
	 * sns_trend_twitter['bearer_access_token']
	 * sns_trend_twitter['bearer_access_token_expired']
	 */
	public function getAccessToken() {
		//
		/* OAuth 2 Bearer Token Use Application-only authentication */
		// 期限切れを確認
		if ((int)strtotime($this->bearer_access_token_expired) + $this->expired < date_i18n('U')) {

			$inv = $this->connection->invalidateBearerToken($this->connection->getBearerToken());
//			echo "invalidateBearerToken():"; var_dump($inv);//#TODO DEBUG
//			var_dump($this->connection);

			// 再発行
			$this->bearer_access_token = $this->connection->getBearerToken();
			if (is_string($this->bearer_access_token)) {
				// optionsに保存
				$this->options['bearer_access_token'] = $this->bearer_access_token;
				$this->options['bearer_access_token_expired'] = current_time('mysql');
				update_option($this->option_name, $this->options);
			}
//			echo "再発行："; var_dump($this->bearer_access_token);//#TODO DEBUG

		} else {
			$this->connection->setBearerToken($this->bearer_access_token);
//			echo "optionsからセット："; var_dump($this->bearer_access_token);//#TODO DEBUG
		}

	}


	/**
	 * twitterAPIにアクセスしてツイートを取得
	 * https://dev.twitter.com/docs/api/1.1/get/search/tweets
	 *
	 * @param $param
	 * @internal param string $keyword
	 * @return array
	 */
	public function search( $param ) {
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

		$result = $this->connection->get('search/tweets', $param);
		if (isset($result->errors)) {
			//#TODO errorsの種類により分岐する　１９５
			var_dump($result);
			$this->options['bearer_access_token_expired'] = date_i18n("Y-m-d H:i:s", current_time('timestamp') - $this->expired);
			update_option($this->option_name, $this->options);
			$this->tweets = "Bearer Tokenが無効 リロードしてください。";
		} else {
//			echo "x_rate_limit_remaining: "; var_dump( $this->connection->http_header['x_rate_limit_remaining'] );
			$this->tweets = $result;
		}
		return $this->tweets;
	}

	public function invalidate() {
		return $this->connection->invalidateBearerToken( $this->connection->getBearerToken() );
	}

	/**
	 * @return array
	 */
	public function getTweets()
	{
		return $this->tweets;
	}

	public static function consolidatedQuery($title, $keywords) {
		return $query = $title . " OR " . preg_replace("/,/", " OR ", $keywords);
	}

	public function save( $post, $tweets=array() ) {
		if (empty($tweets))
			$tweets = $this->tweets;

		if ( empty($tweets->statuses) )
			return false;

		foreach ($tweets->statuses as $tweet) {
			//#TODO データ整形

			$row = array(
				$this->trends->post_id => $post->ID,
				$this->trends->trend_type => self::TYPE,
				$this->trends->trend_id => $tweet->id_str,//32bitOSではint型でbigintがオーバーフローする・・
				$this->trends->trend_created_at => gmdate("Y-m-d H:i:s", (int)strtotime($tweet->created_at) + (int)date_i18n('Z')),//TODO ローカルタイム GMTも扱う？
				$this->trends->trend_title => $post->post_title,
				$this->trends->trend_text => $tweet->text,
				$this->trends->trend_user_id => $tweet->user->id,
				$this->trends->trend_data => json_encode($tweet),
				$this->trends->created => current_time('mysql'),
				$this->trends->modified => current_time('mysql'),
			);
//			var_dump(date_i18n('Z'),$row);

			$result = $this->trends->save($row);
			$results[$post->ID][$result][] = $tweet->id_str;

		}

		//TODO postmetaにsns_trend_countを保存
//				$trend_count['day'];
//				$trend_count['week'];
//				$trend_count['month'];
//				$trend_count['year'];
//				$trend_count['all'];

		// 全時間取得
		$trend_count_all = $this->trends->get_count($post->ID, $term='all');
//		var_dump($post->ID, $trend_count_all);
		// postmetaに保存
		$result = update_post_meta($post->ID, $this->posts->meta["trend_count_all"], $trend_count_all);
		if ( !$result )
			$trend_count_all = false;
		$results[$post->ID]['count'] = $trend_count_all;

		return $results;
	}

	public function save_all() {


	}

	/**
	 * render single tweet
	 *
	 * @param $tweet
	 * @param null $return
	 * @internal param object $tweets
	 * @return int|string
	 */
	public static function render_twitter_list( $tweet, $return = null ) {
		$text = Twitter_Autolink::create($tweet->text)
			->setNoFollow(false)
			->addLinks();
		$data = <<< EOF
<li>
	<p class="twitter_icon"><a href="http://twitter.com/{$tweet->user->screen_name}" target="_blank"><img src="{$tweet->user->profile_image_url}" alt="icon" width="46" height="46" /></a></p>
	<div class="twitter_tweet">
		<p><span class="twitter_content">{$text}</span><span class="twitter_date">{$tweet->created_at}</span></p>
	</div>
</li>
EOF;
		return ( $return === true ) ? $data : print $data;
	}


}
