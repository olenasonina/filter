<?php
ini_set("display_errors", "On");
ini_set("display_startup_errors", "On");
ini_set("error_reporting", "-1");
ini_set("log_errors", "Off");
ini_set("memory_limit", "50M");
set_time_limit (0);
date_default_timezone_set("Europe/Kiev");

define('ROOT', dirname(__FILE__));


include_once (ROOT.'/functions/filter.php');
include_once (ROOT.'/functions/db_functions.php');
include_once (ROOT.'/main.view.php');