<?php
/*
Plugin Name: Sns Trend
Plugin URI: 
Description: 
Author: 
Version: 
Author URI: 
*/

register_activation_hook(__FILE__, 'sns_trend_activate');
register_deactivation_hook(__FILE__, 'sns_trend_deactivate');

function sns_trend_activate() {
	require_once dirname(__FILE__).'/sns_trend_loader.php';
	$loader = new SnsTrendLoader();
	$loader->activate();
}

function sns_trend_deactivate() {
	require_once dirname(__FILE__).'/sns_trend_loader.php';
	$loader = new SnsTrendLoader();
	$loader->deactivate();
}

?>