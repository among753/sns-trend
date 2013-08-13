<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 13/08/07
 * Time: 14:25
 */

namespace SnsTrend\Model;
use SnsTrend\CustomPostType;
use WP_Query;
use wpdb;

class Posts {

	/**
	 * @var string CREATE TABLE
	 */
	public $table_name;
	/**
	 * @var string BIGINT PRIMARY KEY NOT NULL AUTO_INCREMENT
	 */
	public $ID = 'ID';


	public $meta = array(
		"trend_keywords" => "_trend_keywords",
		"trend_count_all" => "_trend_count_all"
	);

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
			$this->ID       => array(
				'db_attributes' => array("NUMERIC", "BIGINT"),
				'default' => 'AUTO_INCREMENT'
			),
/*			$this->post_id        => array(
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
			)*/
		);

	}

	public function get_posts_trends( $args=array() ) {
		$default = array(
			'post_type'       => CustomPostType::POST_TYPE,
			'numberposts' => -1,
			'order' => 'DESC',
			'orderby' => 'meta_value_num',// string:meta_value number:meta_value_num
			'meta_key' => $this->meta['trend_count_all']
		);
		$sss= new WP_Query(array_merge($default, $args));
		var_dump($sss);
		return get_posts(array_merge($default, $args));
	}

} 