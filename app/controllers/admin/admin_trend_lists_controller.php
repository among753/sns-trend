<?php

class AdminTrendListsController extends MvcAdminController {
	
	var $default_columns = array(
			'id',
			'name', 
			'description', 
			'created', 
			'modified', 
			'keywords_str' =>'keywords',
			'aaaaaa' => array('value_method' => 'test_method'), 
	);
	
	public function index() {
		$this->set_objects();
		//var_dump($this);
	}
	
	public function edit() {
		// edit form
        $this->set_trend_keywords();
		$this->verify_id_param();
		$this->create_or_save();
		$this->set_object();
		
		// TrendData index where list_id
		$this->load_model('TrendData');
		$trend_Datas = $this->TrendData->find_by_id($this->params['id']);
        $this->set('trend_Datas', $trend_Datas);
		
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

	private function set_trend_keywords()
	{
		$this->load_model('TrendKeyword');
//		$trend_keywords = $this->TrendKeyword->find_by_list_id($this->params['id'], array('selects' => array('keyword_id', 'word')));
		$trend_keywords = $this->TrendKeyword->find(array('selects' => array('keyword_id', 'list_id', 'word')));
		$this->set('trend_keywords', $trend_keywords);
		//var_dump($trend_keywords);
	}
}

?>