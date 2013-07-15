<?php
/*
Plugin Name: SNS Trend Develop
Plugin URI: https://github.com/among753/sns-trend
Description: SNS Trend Ranking
Author: among753
Version: 0.1.0
Author URI: https://github.com/among753
*/

namespace SnsTrend;

define( 'SNS_TREND_ABSPATH', dirname( __FILE__ ) );


//Load required files
require_once SNS_TREND_ABSPATH . "/sns_trend.class.php";
//require_once SNS_TREND_ABSPATH . "widgets".DIRECTORY_SEPARATOR."popular.php";
require_once SNS_TREND_ABSPATH . "/functions.php";

//Make Instance
global $sns_trend;
$sns_trend = new SnsTrend();
//Register Activation Hook.
register_activation_hook(__FILE__, array($sns_trend, "activate"));
register_deactivation_hook(__FILE__, array($sns_trend, "deactivate"));

?>