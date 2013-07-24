<?php

namespace SnsTrend;


/**
 * Class SnsTrend
 */
class SnsTrend {

	/**
	 * @var string プラグインをアップデートする場合は更新
	 */
	public $version = "0.1";
	/**
	 * @var string DBをアップデートする場合は更新
	 */
	public $db_version = "0.1.4";

	public $wpdb;
	public $tables = array();

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->tables = array( 'trends' => $this->wpdb->prefix.'trends' );

		if(!class_exists('CustomPostType'))
			require_once SNS_TREND_ABSPATH . "/custom_post_type.class.php";

		if(!class_exists('SnsTrendData'))
			require_once SNS_TREND_ABSPATH . "/sns_trend_data.class.php";

		if (!class_exists('SnsTrendTwitter'))
			require_once SNS_TREND_ABSPATH . '/sns_trend_twitter.class.php';

		if (!class_exists('SnsTrendOption'))
			require_once SNS_TREND_ABSPATH . '/sns_trend_option.class.php';

		$this->setPage();
	}

	protected function setPage() {
		// カスタムポストタイプ登録
		$trend = new CustomPostType('trend');

		// trendsテーブルの一覧ページ
		$trend_data = new SnsTrendData();

		// 設定画面を追加
		$trend_option = new SnsTrendOption();

		//#TODO 管理メニューに追加するフック example
		add_action('admin_menu', array($this, 'mt_add_pages'));
	}

	/**
	 * #TODO menu画面追加サンプル
	 */
	public function mt_add_pages() {

		// mt_options_page() はTest Optionsサブメニューのページコンテンツを表示
		function mt_options_page() {
			echo "<h2>Test Options</h2>";
		}
		// mt_manage_page()はTest Manageサブメニューにページコンテんツを表示
		function mt_manage_page() {
			echo "<h2>Test Manage</h2>";
		}
		// mt_toplevel_page()は カスタムのトップレベルメニューのコンテンツを表示
		function mt_toplevel_page() {
			echo "<h2>Test Toplevel</h2>";
		}
		// mt_sublevel_page() はカスタムのトップレベルメニューの
		// 最初のサブメニューのコンテンツを表示
		function mt_sublevel_page() {
			echo "<h2>Test Sublevel</h2>";
		}
		// mt_sublevel_page2() はカスタムのトップレベルメニューの
		// 二番目のサブメニューを表示
		function mt_sublevel_page2() {
			echo "<h2>Test Sublevel 2</h2>";
		}
		// 設定メニュー下にサブメニューを追加:
		add_options_page('Test Options', 'Test Options', 'administrator', 'testoptions', '\SnsTrend\mt_options_page');
		// 管理メニューにサブメニューを追加
		add_management_page('Test Manage', 'Test Manage', 'administrator', 'testmanage', '\SnsTrend\mt_manage_page');
		// 新しいトップレベルメニューを追加(分からず屋):
		add_menu_page('Test Toplevel', 'Test Toplevel', 'administrator', __FILE__, '\SnsTrend\mt_toplevel_page');
		// カスタムのトップレベルメニューにサブメニューを追加:
		add_submenu_page(__FILE__, 'Test Sublevel', 'Test Sublevel', 'administrator', 'sub-page', '\SnsTrend\mt_sublevel_page');
		// カスタムのトップレベルメニューに二つ目のサブメニューを追加:
		add_submenu_page(__FILE__, 'Test Sublevel 2', 'Test Sublevel 2', 'administrator', 'sub-page2', '\SnsTrend\mt_sublevel_page2');
	}


	public function activate() {
		//#TODO 複数テーブルのアクティベート化 tableをmodel化してmodel単位で扱う

		//データベースが存在するか確認
		$is_db_exists = $this->wpdb->get_var($this->wpdb->prepare("SHOW TABLES LIKE %s", $this->tables['trends']));
		if($is_db_exists) {
			//データベースが最新かどうか確認
			if(version_compare(get_option('sns_trend_db_version', 0), $this->db_version, ">=")) {
				return;//必要なければ関数を終了
			}
		}
		//ここまで実行されているということはデータベース作成が必要
		//必要なファイルを読み込み
		require_once ABSPATH."wp-admin/includes/upgrade.php";
		//dbDeltaを実行
		//データベースが作成されない場合はSQLにエラーがあるので、
		//$wpdb->show_errors(); と書いて確認してください
		$this->activateDB();

		//データベースのバージョンを保存する
		update_option("sns_trend_db_version", $this->version);
	}

	protected function activateDB(){

		$sql = '
        CREATE TABLE '.$this->tables['trends'].' (
          id bigint(20) NOT NULL auto_increment,
          post_id bigint(20) NOT NULL,
          data text,
          created datetime,
          modified datetime,
          PRIMARY KEY  (id)
        )';
		$result = dbDelta($sql);
		// create の時のみサンプルデータをinsert
		$this->insert_example_data($result);
	}

	protected function insert_example_data($result) {

		// Only insert the example data if no data already exists
		$sql = '
		SELECT
			id
		FROM
			'.$this->tables['trends'].'
		LIMIT
			1';
		$data_exists = $this->wpdb->get_var($sql);
		if ($data_exists) {
			return false;
		}

		// Insert example data
		$rows = array(
			array(
//						'id' => 1,
				'post_id' => 3,
				'data' => "serializedataが入ります",
				'created' => current_time( 'mysql' ),// WPで設定したローカル時間（'Y-m-d H:i:s'形式）
				'modified' => current_time( 'mysql' ),
			),
		);
		foreach($rows as $row) {
			$this->wpdb->insert($this->tables['trends'], $row);
		}
	}

	public function deactivate() {
		//var_dump("bbbbb");
	}

}

?>