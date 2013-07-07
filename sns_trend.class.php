<?php
/*
Plugin Name: SNS Trend Develop
Plugin URI: https://github.com/among753/sns-trend
Description: SNS Trend Ranking
Author: among753
Version: 0.1.0
Author URI: https://github.com/among753
*/


class SnsTrend {

	public $version = "0.9";

	public $db_version = 0;

	public $tables = array();

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;

		//オプションからsns_trend_db_versionというデータを取得する。なければ0。
		$this->db_version = get_option('sns_trend_db_version', 0);

		$this->init();
	}


	protected function init() {

		$this->tables = array(
			'trends' => $this->wpdb->prefix.'trends',
			'trend_lists' => $this->wpdb->prefix.'trend_lists',
			'trend_keywords' => $this->wpdb->prefix.'trend_keywords',
			'trend_datas' => $this->wpdb->prefix.'trend_datas'
		);
		// hook
		//var_dump("うわああああああああ");


	}

	public function activate() {
		//データベースが存在するか確認
		$is_db_exists = $this->wpdb->get_var($this->wpdb->prepare("SHOW TABLES LIKE %s", $this->tables['trend']));
		if($is_db_exists){
			//データベースが最新かどうか確認
			if($this->db_version >= $this->version){
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
          id int(11) NOT NULL auto_increment,
          name varchar(255) NOT NULL,
          url varchar(255) default NULL,
          description text,
          address1 varchar(255) default NULL,
          address2 varchar(255) default NULL,
          city varchar(100) default NULL,
          state varchar(5) default NULL,
          zip varchar(20) default NULL,
          PRIMARY KEY  (id)
        )';
		dbDelta($sql);
	}

	public function deactivate() {
		//var_dump("bbbbb");
	}

}



//Make Instance
global $sns_trend;
$sns_trend = new SnsTrend();
//Register Activation Hook.
register_activation_hook(__FILE__, array($sns_trend, "activate"));
register_deactivation_hook(__FILE__, array($sns_trend, "deactivate"));

?>