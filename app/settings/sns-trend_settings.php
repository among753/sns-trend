<?php

class SnsTrendSettings extends MvcSettings {

	var $settings = array(
		'admin_version_id' => array(
			'type' => 'select',
			'label' => 'Admin Version',
			'options_method' => 'get_all_versions',
			'default' => 1
		),
		'public_version_id' => array(
			'type' => 'select',
			'label' => 'Public Version',
			'options_method' => 'get_all_versions',
			'default' => 1
		),
		'show_search_form' => array(
			'type' => 'checkbox',
			'default' => 1
		),
		'show_version_list' => array(
			'type' => 'checkbox',
			'default' => 0
		)
	);
	
	public function get_all_versions() {
		$Trend = mvc_model('Trend');
		$trends = $Trend->find();
		$list = array();
		foreach ($trends as $version) {
			$list[$version->id] = $version->name;
		}
		return $list;
	}
	
}

?>