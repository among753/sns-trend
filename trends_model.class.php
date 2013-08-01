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
	 * @var string CREATE TABLE
	 */
	public $table_name;
	/**
	 * @var string BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT
	 */
	public $id = 'id';
	/**
	 * @var string BIGINT NOT NULL, INDEX
	 */
	public $post_id = 'post_id';
	/**
	 * @var string VARCHAR(100) NOT NULL, INDEX
	 */
	public $trend_type = 'trend_type';
	/**
	 * @var string BIGINT NOT NULL
	 */
	public $trend_id = 'trend_id';
	/**
	 * @var string VARCHAR(64) NOT NULL
	 */
	public $trend_created_at = 'trend_created_at';
	/**
	 * @var string LONGTEXT NOT NULL
	 */
	public $trend_title = 'trend_title';
	/**
	 * @var string LONGTEXT NOT NULL
	 */
	public $trend_text = 'trend_text';
	/**
	 * @var string VARCHAR(255) NOT NULL
	 */
	public $trend_url = 'trend_url';
	/**
	 * @var string BIGINT NOT NULL, INDEX
	 */
	public $trend_user_id = 'trend_user_id';
	/**
	 * @var string LONGTEXT NOT NULL
	 */
	public $trend_data = 'trend_data';
	/**
	 * @var string DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL
	 */
	public $created = 'created';
	/**
	 * @var string DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL
	 */
	public $modified = 'modified';

	/**
	 * @var array
	 */
	public $attribute_type = array();

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

		$this->attribute_type = array(
			$this->id       => array(
				'db_attributes' => array("NUMERIC", "BIGINT"),
				'default' => 'AUTO_INCREMENT'
			),
			$this->post_id        => array(
				'db_attributes' => array("NUMERIC", "BIGINT"),
				'default' => 0
			),
			$this->trend_type     => array(
				'db_attributes' => array("TEXT", "VARCHAR(100)"),
				'default' => ''
			),
			$this->trend_id     => array(
				'db_attributes' => array("NUMERIC", "BIGINT"),
				'default' => 0
			),
			$this->trend_created_at     => array(
				'db_attributes' => array("TEXT", "VARCHAR(64)"),
				'default' => ''
			),
			$this->trend_title     => array(
				'db_attributes' => array("TEXT", "TEXT"),
				'default' => ''
			),
			$this->trend_text     => array(
				'db_attributes' => array("TEXT", "TEXT"),
				'default' => ''
			),
			$this->trend_url     => array(
				'db_attributes' => array("TEXT", "VARCHAR(255)"),
				'default' => ''
			),
			$this->trend_user_id     => array(
				'db_attributes' => array("NUMERIC", "BIGINT"),
				'default' => 0
			),
			$this->trend_data     => array(
				'db_attributes' => array("TEXT", "TEXT"),
				'default' => ''
			),
			$this->created  => array(
				'db_attributes' => array("TEXT", "DATETIME"),
				'default' => '0000-00-00 00:00:00'
			),
			$this->modified => array(
				'db_attributes' => array("TEXT", "DATETIME"),
				'default' => '0000-00-00 00:00:00'
			)
		);

	}

	public function table_exists() {
		//データベースが存在するか確認
		$exists = $this->wpdb->get_var($this->wpdb->prepare("SHOW TABLES LIKE %s", $this->table_name));
		return $exists;
	}


	/**
	 *
	 */
	public function createTable() {
		//#TODO せっかくなのでpropertyを使って書く
		$sql = "
CREATE TABLE ".$this->table_name." (
  id bigint(20) NOT NULL auto_increment,
  post_id bigint(20) NOT NULL,
  trend_type VARCHAR(100) NOT NULL,
  trend_id BIGINT NOT NULL,
  trend_created_at VARCHAR(64) NOT NULL,
  trend_title TEXT NOT NULL,
  trend_text TEXT NOT NULL,
  trend_url VARCHAR(255) NOT NULL,
  trend_user_id BIGINT NOT NULL,
  trend_data TEXT NOT NULL,
  created  DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
  modified DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
  PRIMARY KEY  (id),
  KEY (post_id),
  KEY (trend_user_id),
  UNIQUE KEY type_id (trend_type, trend_id)
)
        ";
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
				$this->id => 3,
				$this->trend_type => 'twitter',
				$this->trend_id => 1,
				$this->trend_data => "serialize data",
				$this->created => current_time('mysql'),
				$this->modified => current_time('mysql'),
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
			$param[$this->post_id]
		);

		return $this->wpdb->get_results($query, $param['output_type']);
	}

	/**
	 * @param $row
	 */
	public function save($row) {
		//svar_dump($row);	//$row = array();

		$default = array();
		$format = array();
		foreach ($this->attribute_type as $column => $attribute) {
			if ($attribute['default'] !== 'AUTO_INCREMENT') {
				$default[$column] = $attribute['default'];
				switch ($attribute['db_attributes'][0]) {
					case 'NUMERIC':
						$format[$column] = '%d';
						break;
					case 'TEXT':
						$format[$column] = '%s';
						break;
					case 'FLOAT':
						$format[$column] = '%f';
						break;
				}
			}
		}
		$row = array_merge($default, $row);
		var_dump($row,$format);


		$table_exist = $this->wpdb->get_var($this->wpdb->prepare(
			"SELECT count($this->id) FROM $this->table_name WHERE $this->trend_type = %s AND $this->trend_id = %d",
			$row[$this->trend_type],
			$row[$this->trend_id]
		));
		if ($table_exist == 1) {
			$this->wpdb->update(
				$this->table_name,
				$row,
				array($this->trend_type => $row[$this->trend_type], $this->trend_id => $row[$this->trend_id]),
				$format,
				array('%s', '%d')
			);
		} else {
			$this->wpdb->insert( $this->table_name, $row, $format );
		}


		//$this->wpdb->update( $this->table_name, array( 'column1' => 'value1', 'column2' => 'value2' ), array( 'ID' => 1 ), array( '%s', '%d' ), array( '%d' ) );
		//#TODO エラー処理 エラー返す
		return $result = true;
	}

}
?>
