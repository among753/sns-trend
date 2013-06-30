<?php

class TrendKeyword extends MvcModel {

	var $primary_key = 'keyword_id';
	var $display_field = 'word';
	var $belongs_to = array('TrendList');
	//var $includes = array('TrendList');

}

?>