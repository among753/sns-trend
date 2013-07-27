<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 2013/07/10
 * Time: 23:59
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;

use Twitter_Autolink;

class SnsTrendData {

	/**
	 * @var
	 */
	public $data;

	public $post_type = 'trend';
	public $page = 'sns_trend_data_list';

	public $trends;

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

		// edit.php?post_type=trend&page=sns_trend_data_list
		add_action("admin_head-{$hook_suffix}", array($this, 'admin_head_action'));

	}

	public function admin_head_action() {

		if (isset($_REQUEST['action'])) {
			//var_dump($_REQUEST);
			switch ($_REQUEST['action']) {
				case 'search':
					//#TODO twitter class ajaxでの呼び出しを考慮して作る nonce check
					$twitter = new SnsTrendTwitter();

					$param = array(
						'q' => 'うわああああああああ',
					);
					$this->data = $twitter->search($param);
					//var_dump($this->data);
					//#TODO 重複を考慮してDBに保存
					//#TODO データ整形
					foreach ($this->data->statuses as $row) {
						//単純にインサート 重複チェックは行う
						//$this->trends->save($row);
					}

					break;
				default :
					break;
			}
		}
	}

	public function render_trend_data_list() {

		$this->render_twitter_list($this->data);



		//#TODO データの一覧を出力
		$sns_trend_list_table = new SnsTrendListTable($this->trends);
		$param = array(
			'post_id' => 1,
			'output_type' => 'ARRAY_A'
		);
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

	public function render_twitter_list($data) {
		if (!$data) return false;
		foreach($data->statuses as $status){
			$text = Twitter_Autolink::create($status->text)
				->setNoFollow(false)
				->addLinks();
			echo '<li>'.PHP_EOL;
			echo '<p class="twitter_icon"><a href="http://twitter.com/'.$status->user->screen_name.'" target="_blank"><img src="'.$status->user->profile_image_url.'" alt="icon" width="46" height="46" /></a></p>'.PHP_EOL;
			echo '<div class="twitter_tweet"><p><span class="twitter_content">'.$text.'</span><span class="twitter_date">'.$status->created_at.'</span></p></div>'.PHP_EOL;
			echo "</li>".PHP_EOL;
		}
	}

}
