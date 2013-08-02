<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 2013/07/10
 * Time: 23:59
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;


use wpdb;

class SnsTrendData {

	/**
	 * @var
	 */
	public $data;

	public $post_type = 'trend';
	public $page = 'sns_trend_data';

	/**
	 * @var TrendsModel
	 */
	public $trends;

	/* @var SnsTrendTwitter */
	public $twitter;

	public function __construct() {

		if (!class_exists('SnsTrendListTable'))
			require_once( SNS_TREND_ABSPATH . '/sns_trend_list_table.class.php' );

		if(!class_exists('TrendsModel'))
			require_once SNS_TREND_ABSPATH . "/trends_model.class.php";
		$this->trends = new TrendsModel();

		if(!class_exists('SnsTrendTwitter'))
			require_once SNS_TREND_ABSPATH . "/sns_trend_twitter.class.php";

		// 管理メニューに追加するフック
		add_action('admin_menu', array($this, 'add_pages'));

	}

	public function add_pages() {
		// カスタムのトップレベルメニューにサブメニューを追加:
		$hook_suffix = add_submenu_page("edit.php?post_type={$this->post_type}", __('Trend Data'), __('Trend Data'), 'administrator', $this->page, array($this, 'render_trend_data_list'));

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

		$this->twitter = new SnsTrendTwitter();


		//#TODO $_GETの処理
		$action = $_REQUEST['action'];
		$post_id = ($_REQUEST['post']) ? $_REQUEST['post'] : "" ;

		switch ($action) {
			case 'save':
				if (!$post_id) return;
				//#TODO twitter class ajaxでの呼び出しを考慮して作る

				//#TODO nonce check

				$this->twitter->getAccessToken();

				$query = $wpdb->prepare(
					"
					SELECT
					  *
					FROM
					  $wpdb->posts AS P
					  LEFT JOIN
					  $wpdb->postmeta AS PM
					   ON P.ID = PM.post_id
					  WHERE  P.ID = %d
					  AND PM.meta_key = %s
  					",
					$post_id,
					$option_name="trend_keywords"
				);

				$row = $wpdb->get_row($query);
				//echo "row:";var_dump($row);

				$param = array(
					'q' => SnsTrendTwitter::consolidatedQuery($row->post_title, $row->meta_value),
					'count' => '3', // The number of tweets to return per page, up to a maximum of 100. Defaults to 15.
				);
				$result = $this->twitter->search($param);

				$this->twitter->save($row);

				return $this->data = $result;
			case 'invalidate':
				return $this->data = $this->twitter->invalidate();
			default:
				return $this->data = "";
		}

	}

	public function render_trend_data_list() {

		//#TODO DEBUG twitterからデータを取得した時はDEBUG表示
		$this->twitter->render_twitter_list($this->data);

		//#TODO データの一覧を出力
		echo "<strong>#TODO pagenationのパラメーターにsaveがついて毎回保存しちゃう。</strong>";//#TODO

		$sns_trend_list_table = new SnsTrendListTable($this->trends);
		$param = array(
			'trend_type' => 'twitter'
		);
		if ( $_REQUEST['post'] ) array_push($param, array('post_id' => $_REQUEST['post']));


		$sns_trend_list_table->prepare_items($param);

		global $title;
		?>
		<div class="wrap">
			<div id="icon-edit" class="icon32"><br/></div>
			<h2>
				<?php esc_html_e($title); ?>
			</h2>
			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form id="movies-filter" method="get">
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<!-- Now we can render the completed list table -->
				<?php $sns_trend_list_table->display() ?>

			</form>
		</div>
		<?php
	}

}
