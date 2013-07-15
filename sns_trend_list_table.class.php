<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 2013/07/15
 * Time: 20:41
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;

if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SnsTrendListTable extends \WP_List_Table {

	/**
	 * Constructor. The child class should call this constructor from its own constructor
	 *
	 * @param array $args An associative array with information about the current table
	 * @access protected
	 */
	function __construct( $args = array() ) {
		parent::__construct( array(
			'singular'=> 'wp_list_text_link', //Singular label
			'plural' => 'wp_list_test_links', //plural label, also this well be one of the table css class
			'ajax'	=> false //We won't support Ajax for this table
		) );
	}


}


