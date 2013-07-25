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
	`id` BIGINT NOT NULL auto_increment,
	`post_id` BIGINT NOT NULL,
	`data` TEXT NOT NULL,
	`created` DATETIME NOT NULL,
	`modified` DATETIME NOT NULL,
	PRIMARY KEY  (`id`)) ENGINE=MyISAM;
*/

/**
 * Class TrendsModel
 * @package SnsTrend
 */
class TrendsModel {
	/**
	 * @var BIGINT
	 */
	public $id;
	/**
	 * @var BIGINT
	 */
	public $post_id;
	/**
	 * @var TEXT
	 */
	public $data;
	/**
	 * @var DATETIME
	 */
	public $created;
	/**
	 * @var DATETIME
	 */
	public $modified;

	public $attribute_type = array(
		"id" => array('db_attributes' => array("NUMERIC", "BIGINT")),
		"post_id" => array('db_attributes' => array("NUMERIC", "BIGINT")),
		"data" => array('db_attributes' => array("TEXT", "TEXT")),
		"created" => array('db_attributes' => array("TEXT", "DATETIME")),
		"modified" => array('db_attributes' => array("TEXT", "DATETIME"))
	);

	public $query;

	// WordPress DB class
	public $wpdb;

	// table name
	public $table;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->table = $this->wpdb->prefix.'trends';
	}

	public function table_exists() {
		//データベースが存在するか確認
		$exists = $this->wpdb->get_var($this->wpdb->prepare("SHOW TABLES LIKE %s", $this->table));
		return $exists;
	}

	public function createTable() {
		//#TODO せっかくなのでpropertyを使って書く
		$sql = '
        CREATE TABLE '.$this->table.' (
          id bigint(20) NOT NULL auto_increment,
          post_id bigint(20) NOT NULL,
          data text,
          created datetime,
          modified datetime,
          PRIMARY KEY  (id)
        )';
		require_once ABSPATH."wp-admin/includes/upgrade.php";
		return $result = dbDelta($sql);
	}
	public function insert_example_data() {
		// Only insert the example data if no data already exists
		$sql = '
		SELECT
			id
		FROM
			'.$this->table.'
		LIMIT
			1';
		if ( $this->wpdb->get_var($sql) )
			return false;

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
			$this->wpdb->insert($this->table, $row);
		}
	}

	public function get() {

	}

	public function save($rows) {

	}

}
?>
