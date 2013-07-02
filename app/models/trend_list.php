<?php

class TrendList extends MvcModel {

	//var $primary_key = 'id';
	var $display_field = 'name';
	// #TODO has_manyのtableのupdateが効かない（未実装）core/mvc_model.php:417
	var $has_many = array('TrendKeyword' => array('foreign_key' => 'list_id'));
	var $includes = array('TrendKeyword');

	// カスタム投稿タイプ（mvc_trend_list）
	//var $wp_post = array('post_type' => true);
	var $wp_post = array(
		'post_type' => array(
			'args' => array(
				'show_in_menu' => true,
				'supports' => array('title')
			),
			'fields' => array(
				'post_title' => 'generate_post_title()',
				'post_name' => '$name',// slug
				'post_content' => '$description',
				'post_status' => 'draft',
				'post_date' => '$created',
				'post_date_gmt' => '$created'
			)
		)
	);

	function generate_post_title($object) {
		return 'The '.$object->name;
	}

	public function after_find($object) {
		if (isset($object->trend_keywords)) {
			$keywords = array();
			foreach($object->trend_keywords as $Keyword) {
				$keywords[] = $Keyword->word;
			}
			$object->keywords_str = implode(', ', $keywords);
			$object->keywords = $keywords;
//			$object->name = $object->trend_keyword_names;
// 			if (isset($object->venue)) {
// 				$object->name .= ' at '.$object->venue->name;
// 			}
		}
	}

	public function after_save($object) {
		$this->update_sort_name($object);
	}

	// Use "Colosseum, The" instead of "The Colosseum" for the sort_name
	public function update_sort_name($object) {
//		$sort_name = $object->name;
//		$article = 'The';
//		$article_ = $article.' ';
//		if (strcasecmp(substr($sort_name, 0, strlen($article_)), $article_) == 0) {
//			$sort_name = substr($sort_name, strlen($article_)).', '.$article;
//		}
//		$this->update($object->__id, array('sort_name' => $sort_name));
	}
}

?>