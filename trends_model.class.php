<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 13/07/25
 * Time: 17:47
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;

/*
	This SQL query will create the table to store your object.

	CREATE TABLE `trends` (
	`trend_id` BIGINT NOT NULL auto_increment,
	`post_id` BIGINT NOT NULL,
	`trend_data` TEXT NOT NULL,
	`trend_created` DATETIME NOT NULL,
	`trend_modified` DATETIME NOT NULL,
	PRIMARY KEY  (`id`)) ENGINE=MyISAM;
*/
use wpdb;

/**
 * Class TrendsModel
 * @package SnsTrend
 */
class TrendsModel {
	/**
	 * @var string
	 */
	public $table_name;
	/**
	 * @var string BIGINT
	 */
	public $id = 'trend_id';
	/**
	 * @var string BIGINT
	 */
	public $post_id = 'post_id';
	/**
	 * @var string TEXT
	 */
	public $data = 'trend_data';
	/**
	 * @var string DATETIME
	 */
	public $created = 'trend_created';
	/**
	 * @var string DATETIME
	 */
	public $modified = 'trend_modified';

	/**
	 * @var array
	 */
	public $attribute_type = array(
		"trend_id"       => array('db_attributes' => array("NUMERIC", "BIGINT")),
		"post_id"        => array('db_attributes' => array("NUMERIC", "BIGINT")),
		"trend_data"     => array('db_attributes' => array("TEXT", "TEXT")),
		"trend_created"  => array('db_attributes' => array("TEXT", "DATETIME")),
		"trend_modified" => array('db_attributes' => array("TEXT", "DATETIME"))
	);

	public $query;

	/**
	 * @var wpdb
	 */
	public $wpdb;



	public function __construct() {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->table_name = $this->wpdb->prefix.'trends';

	}

	public function table_exists() {
		//データベースが存在するか確認
		$exists = $this->wpdb->get_var($this->wpdb->prepare("SHOW TABLES LIKE %s", $this->table_name));
		return $exists;
	}

	public function createTable() {
		//#TODO せっかくなのでpropertyを使って書く
		$sql = '
        CREATE TABLE '.$this->table_name.' (
          trend_id bigint(20) NOT NULL auto_increment,
          post_id bigint(20) NOT NULL,
          trend_data text,
          trend_created datetime,
          trend_modified datetime,
          PRIMARY KEY  (id)
        )';
		require_once ABSPATH."wp-admin/includes/upgrade.php";
		return $result = dbDelta($sql);
	}
	public function insert_example_data() {
		// Only insert the example data if no data already exists
		$sql = 'SELECT '.$this->id.' FROM '.$this->table_name.' LIMIT 1';
		if ( $this->wpdb->get_var($sql) )
			return false;

		// Insert example data
		$rows = array(
			array(
//						'trend_id' => 1,
				'post_id' => 3,
				'trend_data' => "serializedataが入ります",
				'trend_created' => current_time('mysql'),// WPで設定したローカル時間（'Y-m-d H:i:s'形式）
				'trend_modified' => current_time('mysql'),
			),
		);
		foreach($rows as $row) {
			$this->wpdb->insert($this->table_name, $row);
		}
	}

	/**
	 * $param 条件で絞り込み
	 * @param $param
	 */
	public function get($param) {

		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE {$this->post_id} = %d",
			$param['post_id']
		);

		return $this->wpdb->get_results($query, $param['output_type']);
	}

	/**
	 * @param $row
	 */
	public function save($row) {
		//var_dump($row);

		$data = array(
			$this->post_id  => 1,
			$this->data     => serialize($row),
			$this->created  => current_time('mysql'),
			$this->modified => current_time('mysql')
		);
		$this->wpdb->insert( $this->table_name, $data, array('%d', '%s', '%s', '%s') );
		//$this->wpdb->update( $this->table_name, array( 'column1' => 'value1', 'column2' => 'value2' ), array( 'ID' => 1 ), array( '%s', '%d' ), array( '%d' ) );
		//#TODO エラー処理 エラー返す
		return $result = true;
	}

}
?>
