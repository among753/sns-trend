<?php

class AdminTrendListsController extends MvcAdminController {
	
	var $default_columns = array(
			'id',
			'name', 
			'description', 
			'created', 
			'modified', 
			'trend_keyword_names' =>'きーわーど',
			'aaaaaa' => array('value_method' => 'test_method'), 
	);
	
	public function index() {
		$this->set_objects();
		//var_dump($this);
	}
	
	function example_page() {
	
	
		if (!empty($_POST)) {
				
			echo "ここでtwittersearch起動";
				
		} else {
				
		}
	
		$this->load_model('TrendList');
		$trend_lists = $this->TrendList->find();
		$this->set('trend_lists', $trend_lists);
		$this->set_objects();
	
	}
	
	
	function test_method($object) {
		//var_dump($object);
		return 'うあわあああああああああ';
	}
}

?>