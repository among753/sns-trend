<?php
/**
 * Created by PhpStorm.
 * User: sasaki
 * Date: 13/08/13
 * Time: 18:40
 */

namespace SnsTrend;


use SnsTrend\Model\Posts;
use WP_Query;


/**
 * Class ArchiveTrend
 * archive page (post type: trend)
 *
 * @package SnsTrend
 */
class ArchiveTrend {


	public function __construct() {
		add_action( 'pre_get_posts', array($this, 'change_posts_per_page') );
	}

	/**
	 * main query hack
	 *
	 * @param WP_Query $query
	 */
	public function change_posts_per_page( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! is_post_type_archive(CustomPostType::POST_TYPE))
			return;
		// 全カウント順に
		$posts = new Posts();
		$query->set( 'order', 'DESC' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'meta_key', $posts->meta['trend_count_all'] );
	}


} 