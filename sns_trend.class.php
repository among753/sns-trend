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

	public $tables = array();

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;

		if(!class_exists('CustomPostType'))
			require_once SNS_TREND_ABSPATH . "/custom_post_type.class.php";

		if(!class_exists('MetaBox'))
			require_once SNS_TREND_ABSPATH . "/meta_box.class.php";

		if(!class_exists('SnsTrendData'))
			require_once SNS_TREND_ABSPATH . "/sns_trend_data.class.php";

		if (!class_exists('SnsTrendTwitter'))
			require_once SNS_TREND_ABSPATH . '/sns_trend_twitter.class.php';

		$this->init();
	}

	protected function init() {

		//#TODO ワードのリストはカスタム投稿タイプ'trend'を使う。postmetaにwordを設定。searchAPIで検索されたデータをtrendsテーブルに格納
		$this->tables = array(
			'trends' => $this->wpdb->prefix.'trends',
		);

		$trend = new CustomPostType('trend');

		$params = array(
			array(
				'meta_key'   => 'trend_keywords',
				'input_type' => 'text',
				'input_value' => 'らーめん',
				'description' => __("検索ワードを入力してください。"),
				'validate'   => array(
					'length'  => 100,
//					'require' => true
				),
//				'ajax'          => false, // 保存にajaxを使うか
			),
			array(
				'meta_key'   => 'radio_test',
				'input_type' => 'radio',
				'input_value' => array('ra-menn',"afdsfasd","あああああ"),
				'description' => __("検索ワードを選んでください。"),
				'validate'   => array(
					'length'  => 100,
					'require' => true
				),
				'ajax'          => false, // 保存にajaxを使うか
			),
			array(
				'meta_key'   => 'checkbox_test',
				'input_type' => 'checkbox',
				'input_value' => array('wattu',"bbbb","あああああいいい"),
				'description' => __("検索ワードを選んでください。（複数可）"),
				'validate'   => array(
					'length'  => 100,
					'require' => true
				),
				'ajax'          => false, // 保存にajaxを使うか
			),
		);
		$meta_box = new MetaBox(array(
			'id'            => 'meta_keywords',
			'title'         => _x('キーワード', 'word hosoku'),
			'params'         => $params,
//			'callback'      => 'trends_meta_html',
			'screen'        => $trend->post_type,
			'context'       => 'advanced',
			'priority'      => 'default',
			'callback_args' => null
		));

		$trend_data = new SnsTrendData();

		//#TODO 設定画面を追加
		add_action('admin_init', array($this, 'setting_options_page'));
		add_action('admin_menu', array($this, 'add_options_page'));


		//#TODO 管理メニューに追加するフック example
		add_action('admin_menu', array($this, 'mt_add_pages'));
	}


	/**
	 * 設定ページのセッティング
	 */
	public function setting_options_page() {
		//#TODO 一般設定セクションをここに作成

		//#TODO twitterAPIの設定項目を追加
		SnsTrendTwitter::setting_option();

		// hidden 'option_page' 'action' '_wpnonce' '_wp_http_referer' settings_fields($option_group) で出力
		register_setting( 'sns_trend_options_group', 'sns_trend' );
		// 一般設定
		add_settings_section('general', __('general'), array($this, 'twitter_section_text'), 'general');
		add_settings_field('color', __('Color'), array($this, 'setting_input'), 'general', 'general',
			array(
				'label_for' => 'general',
				'type' => 'text',
				'option_name' => 'sns_trend',
//				'option_name_key' => 'consumer_key'
			)
		);



		// hidden 'option_page' 'action' '_wpnonce' '_wp_http_referer' settings_fields($option_group) で出力
		register_setting( 'twitter_options_group', 'twitter_api', array($this, 'plugin_options_validate') );
		// セクションを設定 do_settings_sections('twitter') で出力
		add_settings_section('twitter-setting', 'Twitter OAuth settings', array($this, 'twitter_section_text'), 'twitter');
		// フィールドを設定 第4引数で指定した
		add_settings_field('consumer_key', 'CONSUMER_KEY', array($this, 'setting_input'), 'twitter', 'twitter-setting',
			array(
				'label_for' => 'twitter_api_consumer_secret',
				'type' => 'text',
				'option_name' => 'twitter_api',
				'option_name_key' => 'consumer_key'
			)
		);
		add_settings_field('consumer_secret', 'CONSUMER_SECRET', array($this, 'setting_input'), 'twitter', 'twitter-setting',
			array(
				'label_for' => 'twitter_api_consumer_secret',
				'type' => 'text',
				'option_name' => 'twitter_api',
				'option_name_key' => 'consumer_secret'
			)
		);
	}
	/**
	 * callback validate sanitize
	 * @param $input
	 * @return mixed
	 */
	function plugin_options_validate($input) {
		$newinput = $input;
		$newinput['text_string'] = trim($input['text_string']);
		if(!preg_match('/^[a-z0-9]{32}$/i', $newinput['text_string'])) {
			$newinput['text_string'] = '';
		}
		return $newinput;
	}
	/**
	 * callback add_settings_section()
	 * セクションにechoする
	 */
	public function twitter_section_text() {
		_e('<p>Main description of this section here.</p>');
	}
	/**
	 * callback add_settings_field()
	 */
	public function setting_input($args) {
		$type = $args['type'];
		$option_name = $args['option_name'];
		$option_name_key = $args['option_name_key'];

		$options = get_option($option_name);
		var_dump($options);

		switch ($type) {
			case 'text' :
				$input = "<input type='{$type}' id='{$option_name}_{$option_name_key}' name='{$option_name}[{$option_name_key}]' size='40' value='{$options[$option_name_key]}'>";
				break;
			default :
				break;
		}
		echo $input;
	}

	/**
	 * 設定ページ
	 */
	public function add_options_page() {
	//#TODO 設定用ページ
		add_options_page(__('SNS Trend'), __('SNS Trend'), 'administrator', 'options', array($this, 'render_options_page'));
	}
	/**
	 * Render options page.
	 */
	public function render_options_page() {
		global $title;
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2>
				<?php _e($title); ?>
			</h2>
			<form method="post"action="options.php">
				<?php settings_fields('sns_trend_options_group'); ?>
				<?php settings_fields('twitter_options_group'); ?>
				<?php do_settings_sections('general'); ?>
				<?php do_settings_sections('twitter'); ?>
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes'); ?>">
			</form>
		</div>
		<?php
	}


	/**
	 * menu画面追加サンプル
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
		add_management_page('Test Manage', 'Test Manage', 'administrator', 'testmanage', 'mt_manage_page');
		// 新しいトップレベルメニューを追加(分からず屋):
		add_menu_page('Test Toplevel', 'Test Toplevel', 'administrator', __FILE__, 'mt_toplevel_page');
		// カスタムのトップレベルメニューにサブメニューを追加:
		add_submenu_page(__FILE__, 'Test Sublevel', 'Test Sublevel', 'administrator', 'sub-page', 'mt_sublevel_page');
		// カスタムのトップレベルメニューに二つ目のサブメニューを追加:
		add_submenu_page(__FILE__, 'Test Sublevel 2', 'Test Sublevel 2', 'administrator', 'sub-page2', 'mt_sublevel_page2');
	}


	public function activate() {
		//#TODO 複数テーブルのアクティベート化 tableをmodel化してmodel単位で扱う

		//データベースが存在するか確認
		$is_db_exists = $this->wpdb->get_var($this->wpdb->prepare("SHOW TABLES LIKE %s", $this->tables['trends']));
		if($is_db_exists){
			//データベースが最新かどうか確認
			if(version_compare(get_option('sns_trend_db_version', 0), $this->db_version, ">=")){
				//必要なければ関数を終了
				return;
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
		//#TODO create の時のみサンプルデータをinsert
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



// ------------------------------------------------------------------
// Add all your sections, fields and settings during admin_init
// ------------------------------------------------------------------
//

function eg_settings_api_init() {
	// Add the section to reading settings so we can add our
	// fields to it
	add_settings_section('eg_setting_section',
		'Example settings section in reading',
		'\SnsTrend\eg_setting_section_callback_function',
		'reading');

	// Add the field with the names and function to use for our new
	// settings, put it in our new section
	add_settings_field('eg_setting_name',
		'Example setting Name',
		'\SnsTrend\eg_setting_callback_function',
		'reading',
		'eg_setting_section');

	// Register our setting so that $_POST handling is done for us and
	// our callback function just has to echo the <input>
	register_setting('reading','eg_setting_name');
}// eg_settings_api_init()

add_action('admin_init', '\SnsTrend\eg_settings_api_init');

// ------------------------------------------------------------------
// Settings section callback function
// ------------------------------------------------------------------
//
// This function is needed if we added a new section. This function
// will be run at the start of our section
//

function eg_setting_section_callback_function() {
	echo '<p>Intro text for our settings section</p>';
}

// ------------------------------------------------------------------
// Callback function for our example setting
// ------------------------------------------------------------------
//
// creates a checkbox true/false option. Other types are surely possible
//

function eg_setting_callback_function() {
	echo '<input name="eg_setting_name" id="gv_thumbnails_insert_into_excerpt" type="checkbox" value="1" class="code" ' . checked( 1, get_option('eg_setting_name'), false ) . ' /> Explanation text';
}
?>