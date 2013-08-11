<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 2013/07/10
 * Time: 23:59
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;

use SnsTrend\Model\Posts;
use SnsTrend\Model\Trends;
use wpdb;

/**
 * Class SnsTrendData
 * @package SnsTrend
 */
class Data {

	const PAGE = 'sns_trend_data';

	/**
	 * @var
	 */
	public $data;

	/**
	 * @var Trends Model
	 */
	public $trends;

	/**
	 * @var Posts TODO Posts Modelを作る
	 */
	public $posts;

	/* @var Twitter */
	public $twitter;

	public function __construct() {

		// Model
		$this->trends = new Trends();
		$this->posts  = new Posts();

		// 管理メニューに追加するフック
		add_action('admin_menu', array($this, 'add_pages'));

	}

	public function add_pages() {
		// カスタムのトップレベルメニューにサブメニューを追加:
		$hook_suffix = add_submenu_page("edit.php?post_type=" . CustomPostType::POST_TYPE, __('Trend Data'), __('Trend Data'), 'administrator', self::PAGE, array($this, 'render_trend_data_list'));

		// edit.php?post_type=trend&page=sns_trend_data
		add_action("admin_head-{$hook_suffix}", array($this, 'admin_head_action'));

	}


	/**
	 *
	 *
	 * @return array|\OAuthRequest|string
	 */
	public function admin_head_action() {
		/** @var $wpdb wpdb */
		global $wpdb;

		$this->twitter = new Twitter();


		//#TODO $_GETの処理
		$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : "";
		$post_id = (isset($_REQUEST['post'])) ? $_REQUEST['post'] : "" ;

		switch ($action) {
			case 'save':
				if (!$post_id) return false;
				//#TODO twitter class ajaxでの呼び出しを考慮して作る

				//#TODO nonce check


				// postデータとpostmetaデータを取得
				$post = get_post( $post_id );
				$trend_keywords = get_post_meta( $post_id , $this->posts->meta["trend_keywords"] , true );
//				var_dump($post, $trend_keywords);


				// TODO Twitter 後で他SNSのデータ検索保存を実装
				// post_titleとtrend_keywordsからTwitterを検索
				$this->twitter->getAccessToken();
				$param = array(
					'q' => Twitter::consolidatedQuery($post->post_title, $trend_keywords),
					'count' => '100', // The number of tweets to return per page, up to a maximum of 100. Defaults to 15.
				);
				$result = $this->twitter->search($param);

				// 取得データを保存
				$this->twitter->save($post, $result);

				return $this->data = $result;
			case 'invalidate':
				return $this->data = $this->twitter->invalidate();// TODO 設定画面に移動
			default:
				return $this->data = "";
		}

	}

	public function render_trend_data_list() {

//		var_dump($this->data);
		//#TODO DEBUG twitterからデータを取得した時はDEBUG表示
		if ($this->data)
//			Twitter::render_twitter_list($this->data->statuses);

		//#TODO データの一覧を出力
		echo "<strong>#TODO pagenationのパラメーターにsaveがついて毎回保存しちゃう。</strong>";//#TODO

		$sns_trend_list_table = new ListTable();
		$param = array(
			"wheres" => array(
				'trend_type' => 'twitter'
			)
		);

		if ( isset($_REQUEST['post']) ) $param['post_id'] = $_REQUEST['post'];//TODO ???


		$sns_trend_list_table->set_prepare_items_param($param);// prepare_items()が引数取れないのでここでset
		$sns_trend_list_table->prepare_items();

		global $title;
		?>
		<div class="wrap">
			<div id="icon-edit" class="icon32"><br/></div>
			<h2>
				<?php esc_html_e($title); ?>
			</h2>
			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form id="trends-filter" method="get">

				<?php $sns_trend_list_table->search_box(__('search'), 'trend'); ?>
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php esc_attr_e($_REQUEST['page']); ?>" />
				<input type="hidden" name="post_type" value="<?php esc_attr_e($_REQUEST['post_type']); ?>" />

				<!-- param -->
				<input type="hidden" name="trend_type" value="
				<?php if (isset($_REQUEST['trend_type'])) esc_attr_e($_REQUEST['trend_type']); ?>" />

				<!-- Now we can render the completed list table -->
				<?php $sns_trend_list_table->display() ?>

			</form>
		</div>
		<?php
	}

}
