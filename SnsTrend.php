<?php
/*
Plugin Name: SNS Trend
Plugin URI: https://github.com/among753/sns-trend
Description: SNS Trend Ranking
Author: among753
Version: 0.1.0
Author URI: https://github.com/among753
*/

namespace SnsTrend;
use SplClassLoader;
use SnsTrend\Model\Trends;


define( 'SNS_TREND_ABSPATH', dirname( __FILE__ ) );


require SNS_TREND_ABSPATH . '/libs/SplClassLoader.php';
$class_loader_sns_trend = new SplClassLoader('SnsTrend', dirname(__DIR__));
$class_loader_sns_trend->register();


$sns_trend = new SnsTrend();



/**
 * Class SnsTrend
 * @package SnsTrend
 */
class SnsTrend {
	/**
	 * @var string DBをアップデートする場合は更新
	 */
	public $db_version = "0.1.0";
	public $option_db_version_name = 'sns_trend_db_version';

	public function __construct() {
		//Register Activation Hook.
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));


		// カスタムポストタイプ登録
		$trend = new CustomPostType();

		// trendsテーブルの一覧ページ
		$trend_data = new Data();

		// 設定画面を追加
		$trend_option = new Option();

		// ショートコードを設定
		$trend_short_code = new ShortCode();

		//#TODO widgets namespaceが使えない
		require_once SNS_TREND_ABSPATH . "/widgets/sns_trend_ranking_widget.php";
		add_action('widgets_init', function(){register_widget("SnsTrendRankingWidget");});

		//TODO ショートコードとかグローバル関数とか？
		require_once SNS_TREND_ABSPATH . "/functions.php";


		//#TODO 管理メニューに追加するフック example
		add_action('admin_menu', array($this, 'mt_add_pages'));
	}

	public function activate() {
		// 複数テーブルのアクティベート化 tableをmodel化してmodel単位で扱う
		$trends = new Trends();

		if($trends->table_exists()) {
			//データベースが最新かどうか確認
			if(version_compare(get_option($this->option_db_version_name, 0), $this->db_version, ">="))
				return;
		}
		//ここまで実行されているということはデータベース作成が必要

		//データベースが作成されない場合はSQLにエラーがあるので、$wpdb->show_errors(); と書いて確認してください
		$trends->createTable();

		// create の時のみサンプルデータをinsert
		$trends->insert_example_data();

		//データベースのバージョンを保存する
		update_option($this->option_db_version_name, $this->db_version);
	}

	public function deactivate() {
		//var_dump("bbbbb");
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

}


