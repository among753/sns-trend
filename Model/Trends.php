<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 13/07/25
 * Time: 17:47
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend\Model;
use wpdb;

/**
 * Class TrendsModel
 * @package SnsTrend\Model
 */
class Trends {
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
  trend_created_at DATETIME DEFAULT '1900-00-00 00:00:00' NOT NULL,
  trend_title TEXT NOT NULL,
  trend_text TEXT NOT NULL,
  trend_url VARCHAR(255) NOT NULL,
  trend_user_id BIGINT NOT NULL,
  trend_data TEXT NOT NULL,
  created  DATETIME DEFAULT '1900-00-00 00:00:00' NOT NULL,
  modified DATETIME DEFAULT '1900-00-00 00:00:00' NOT NULL,
  PRIMARY KEY  (id),
  KEY (post_id),
  KEY (trend_user_id),
  UNIQUE KEY type_id (trend_type, trend_id)
)
        ";
		require_once ABSPATH . "wp-admin/includes/upgrade.php";
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
//				$this->id => 3,
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
	 *
	 * @param null $param
	 * @param string $output_type
	 * @return mixed
	 */
	public function get( $wheres=null, $orderby=null, $limit=null, $output = OBJECT ) {

		//#TODO orderby limit を追加する
		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name}" . $this->get_where($wheres),
			$wheres
		);
//		var_dump($query);
		return $this->wpdb->get_results($query, $output);
	}


	public function get_col($col, $wheres=null, $orderby=null, $limit=null, $x = 0 ) {
		//#TODO orderby limit を追加する
		$query = $this->wpdb->prepare(
			"SELECT $col FROM {$this->table_name}" . $this->get_where($wheres),
			$wheres
		);
		var_dump($query);
		if ( $orderby )
			$query .= " ORDER BY $col " . $orderby;
		var_dump($query);
		return $this->wpdb->get_col($query, $x);
	}


	/**
	 * @param $row
	 */
	public function save( $row ) {
		//var_dump($row);	//$row = array();

		$default = array();
		$format = array();
		foreach ($this->attribute_type as $column => $attribute) {
			if ($attribute['default'] !== 'AUTO_INCREMENT') {
				$default[$column] = $attribute['default'];
				$format[$column] = $this->get_db_placeholder($column);
			}
		}
		$row = array_merge($default, $row);
//		var_dump($row,$format);
//		trigger_error(print_r($row,true));

		if ($this->data_exist($row[$this->trend_type], $row[$this->trend_id]) == 1) {
			return $this->wpdb->update(
				$this->table_name,
				$row,
				array($this->trend_type => $row[$this->trend_type], $this->trend_id => $row[$this->trend_id]),
				$format,
				array('%s', '%d')
			);
		} else {
			return $this->wpdb->insert( $this->table_name, $row, $format );
		}
	}

	/**
	 * データが存在するか
	 *
	 * @param $trend_type
	 * @param $trend_id
	 * @return null|string
	 */
	protected function data_exist($trend_type, $trend_id) {
		$data_exist = $this->wpdb->get_var($this->wpdb->prepare(
			"SELECT count($this->id) FROM $this->table_name WHERE $this->trend_type = %s AND $this->trend_id = %s",
			$trend_type,
			$trend_id
		));
//		var_dump($data_exist);
		return $data_exist;
	}


	/**
	 * $wpdb->prepare()用のplaceholderを返す。
	 * 32bitOSでBIGINTが扱えないのでstring型として扱う。
	 *
	 * @param $column
	 * @return string
	 */
	public function get_db_placeholder($column) {
		list($type, $type2) = $this->attribute_type[$column]['db_attributes'];
		if ( $type=='TEXT' || $type2=='BIGINT' )
			return '%s';
		elseif ($type2=='FLOAT')
			return '%f';
		else
			return '$d';
	}

	public function get_where($params) {
		$where='';
		if (is_array($params) && $params) {
			$where=" WHERE ";
			foreach ($params as $key => $value) {
				if ( $where != " WHERE " ) $where .= " AND ";
				$where .= $key . " = " . $this->get_db_placeholder($key);
			}
		}
//		var_dump($params, $where);
		return $where;
	}

	public function get_count($post_id, $term) {
		$term;
		$query = $this->wpdb->prepare(
			"
			SELECT count({$this->trend_created_at})
			FROM {$this->table_name}
			WHERE {$this->post_id}=%s AND
			{$this->trend_created_at} BETWEEN %s AND %s
			ORDER BY {$this->trend_created_at} DESC
			",
			$post_id,
			"1700-01-01 00:00:00","2019-08-06 18:50:11"
		);
		return $trend_created_at = $this->wpdb->get_var($query);
	}


}
