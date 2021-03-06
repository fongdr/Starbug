<?php
//set the appropriate level of error reporting
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE | E_PARSE | E_ERROR);

include("vendor/autoload.php");

$factory = new Starbug\Core\ContainerFactory(str_replace("/core", "", dirname(__FILE__)));
$container = $factory->build($args);

date_default_timezone_set($container->get('time_zone'));
$container->get("Starbug\Core\ErrorHandler")->register();
