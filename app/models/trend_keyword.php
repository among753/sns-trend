<?php

class TrendKeyword extends MvcModel {

	var $primary_key = 'keyword_id';
	var $display_field = 'keyword_id';
	var $belongs_to = array('TrendList');
	
}

?>