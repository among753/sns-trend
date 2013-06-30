<?php

class TrendList extends MvcModel {

	var $display_field = 'name';
	var $has_many = array('TrendKeyword' => array('foreign_key' => 'trend_list_id'));
	var $includes = array('TrendKeyword');
	
	public function after_find($object) {
		//var_dump($object->trend_keywords);
		if (isset($object->trend_keywords)) {
			$keywords = array();
			foreach($object->trend_keywords as $Keyword) {
				$keywords[] = $Keyword->word;
			}
			$object->trend_keyword_names = implode(', ', $keywords);
//			$object->name = $object->trend_keyword_names;
// 			if (isset($object->venue)) {
// 				$object->name .= ' at '.$object->venue->name;
// 			}
		}
	}
		

}

?>