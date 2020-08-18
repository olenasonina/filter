<?php

ini_set("display_errors", "On");
ini_set("display_startup_errors", "On");
ini_set("error_reporting", "-1");
ini_set("log_errors", "On");
ini_set("memory_limit", "50M");
set_time_limit (0);
date_default_timezone_set("Europe/Kiev");

define('ROOT', "/var/www/arjen.ua/data/www/arjen.ua/insta/instashopping");

require_once ROOT.'/vendor/autoload.php';
require_once ROOT.'/config.php';
require_once ROOT.'/components/ConnectToBD.php';
require_once ROOT.'/components/SelectDataFromBD.php';
require_once ROOT.'/components/InsertDataToBd.php';

$connect = new ConnectToBD(Data::getBdData());

require_once ROOT.'/main.php';