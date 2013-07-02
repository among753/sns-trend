<?php


/**
 * Class SnsTrendLoader
 * http://takahashifumiki.com/web/programing/1440/
 */
class SnsTrendLoader extends MvcPluginLoader {

	var $db_version = '1.0';
	var $tables = array();

	protected $wpdb = null;
	protected $file_includer = null;

	function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->file_includer = new MvcFileIncluder();
		$this->init();
	}

	function init() {
	
		// Include any code here that needs to be called when this class is instantiated
	
		$this->tables = array(
			'trends' => $this->wpdb->prefix.'trends',
			'trend_lists' => $this->wpdb->prefix.'trend_lists',
			'trend_keywords' => $this->wpdb->prefix.'trend_keywords',
			'trend_datas' => $this->wpdb->prefix.'trend_datas'
		);
	
	}
	
	function activate() {
	
		// This call needs to be made to activate this app within WP MVC
		
		$this->activate_app(__FILE__);
		
		// Perform any databases modifications related to plugin activation here, if necessary

		require_once ABSPATH.'wp-admin/includes/upgrade.php';
	
		add_option('sns_trend_db_version', $this->db_version);
		
		// Use dbDelta() to create the tables for the app here
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
		
		$sql = '
        CREATE TABLE '.$this->tables['trend_lists'].' (
          id int(11) NOT NULL auto_increment,
          post_id BIGINT(20) NOT NULL,
          name varchar(255) NOT NULL,
          description text,
          created datetime,
          modified datetime,
          PRIMARY KEY  (id)
        )';
		dbDelta($sql);
		$sql = '
        CREATE TABLE '.$this->tables['trend_keywords'].' (
          keyword_id int(11) NOT NULL auto_increment,
          list_id int(11) default NULL,
          word varchar(255) NOT NULL,
          created datetime,
          modified datetime,
          PRIMARY KEY  (keyword_id),
          KEY list_id (post_id)
        )';
		dbDelta($sql);
		$sql = '
        CREATE TABLE '.$this->tables['trend_datas'].' (
          id int(11) NOT NULL auto_increment,
          list_id int(11) default NULL,
          value text,
          created datetime,
          modified datetime,
          PRIMARY KEY  (id),
          KEY list_id (list_id)
        )';
		dbDelta($sql);
		
		$this->insert_example_data();
	}

	function deactivate() {
	
		// This call needs to be made to deactivate this app within WP MVC
		
		$this->deactivate_app(__FILE__);
		
		// Perform any databases modifications related to plugin deactivation here, if necessary
	
	}

	function insert_example_data() {
	
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
						'name' => "さんぷる",
						'url' => "http://localhost/",
						'description' => "さんぷる",
						'address1' => "さんぷる",
						'address2' => "さんぷる",
						'city' => "さんぷる",
						'state' => "さんぷる",
						'zip' => "さんぷる",
				),
		);
		foreach($rows as $row) {
			$this->wpdb->insert($this->tables['trends'], $row);
		}
	
		$rows = array(
					array(
//				        'id' => 1,
						'post_id' => 0,
						'name' => "WordPress",
						'description' => "WordPressの説明",
						'created' => "2013-1-10 15:00",
						'modified' => "2013-1-10 15:00"
				),
				array(
//						'id' => 2,
						'post_id' => 10,
						'description' => "Movable Typeの説明",
						'created' => "2013-1-10 15:00",
						'modified' => "2013-1-10 15:00"
				),
		);
		foreach($rows as $row) {
			$this->wpdb->insert($this->tables['trend_lists'], $row);
		}
	
		$rows = array(
				array(
//						'keyword_id' => 1,
						'list_id' => 1,
						'word' => "ワードプレス",
						'created' => "2013-1-10 15:00",
						'modified' => "2013-1-10 15:00"
				),
				array(
//						'keyword_id' => 2,
						'list_id' => 1,
						'word' => "ワープロ",
						'created' => "2013-1-10 15:00",
						'modified' => "2013-1-10 15:00"
				),
				array(
//						'keyword_id' => 3,
						'list_id' => 2,
						'word' => "ムーバブルタイプ",
						'created' => "2013-1-10 15:00",
						'modified' => "2013-1-10 15:00"
				),
		);
		foreach($rows as $row) {
			$this->wpdb->insert($this->tables['trend_keywords'], $row);
		}
	
		$rows = array(
				array(
//						'id' => 1,
						'list_id' => 1,
						'value' => "WordPressってすごいですね！なんたって！！！",
						'created' => "2013-1-10 15:00",
						'modified' => "2013-1-10 15:00"
				),
				array(
//						'id' => 1,
						'list_id' => 1,
						'value' => "ワードプレスワードプレスワードプレスワードプレスワードプレス",
						'created' => "2013-1-10 15:00",
						'modified' => "2013-1-10 15:00"
				),
				array(
//						'id' => 1,
						'list_id' => 2,
						'value' => "Mobable Type (笑)",
						'created' => "2013-1-10 15:00",
						'modified' => "2013-1-10 15:00"
				),
		);
		foreach($rows as $row) {
			$this->wpdb->insert($this->tables['trend_datas'], $row);
		}
		
	}
	
}

?>