<?php
/**
 * Created by JetBrains PhpStorm.
 * User: KS
 * Date: 2013/07/10
 * Time: 23:59
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;

//use SnsTrend\SnsTrendListTable;
//use SnsTrend\TT_Example_List_Table;


class SnsTrendData {
	public $aaa="aaa";

	public function __construct() {


		if(!class_exists('TT_Example_List_Table')){
			require_once( SNS_TREND_ABSPATH . '/list-table-example.php' );
		}
		if(!class_exists('SnsTrendListTable')){
			require_once( SNS_TREND_ABSPATH . '/sns_trend_list_table.class.php' );
		}


	}

	public function init() {
		// 管理メニューに追加するフック
		add_action('admin_menu', array(&$this, 'mt_add_pages'));
	}

	public function mt_add_pages() {
		// カスタムのトップレベルメニューにサブメニューを追加:
		add_submenu_page("edit.php?post_type=trend", __('Trend Data'), __('Trend Data'), 'administrator', 'sns_trend_data_list', array($this, 'render_trend_data_list'));

	}

	public function render_trend_data_list() {
		//#TODO データの一覧を出力

		$sns_trend_list_table = new SnsTrendListTable();
		$sns_trend_list_table->prepare_items();

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
