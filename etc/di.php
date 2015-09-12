<?php
use \Interop\Container\ContainerInterface;
return array(
	'base_directory' => BASE_DIR,
	'modules' => array(
		"core" => "core/app",
		"db" => "modules/db",
		"users" => "modules/users",
		"emails" => "modules/emails",
		"files" => "modules/files",
		"comments" => "modules/comments",
		"css" => "modules/css",
		"js" => "modules/js",
		"theme" => "app/themes/starbug-1",
		"var" => "var",
		"app" => "app"
	),
	'database_name' => DEFAULT_DATABASE,
	'Starbug\Core\SettingsInterface' => DI\object('Starbug\Core\DatabaseSettings'),
	'Starbug\Core\*Interface' => DI\object('Starbug\Core\*'),
	'Starbug\Core\ResourceLocator' => DI\object()->constructor(DI\get('base_directory'), DI\get('modules')),
	'Starbug\Core\ModelFactory' => DI\object()->constructorParameter('base_directory', DI\get('base_directory')),
	'Starbug\Core\GenerateCssCommand' => DI\object()->constructorParameter('base_directory', DI\get('base_directory')),
	'Starbug\Core\ErrorHandler' => DI\object()->constructorParameter("exceptionTemplate", defined('SB_CLI') ? "exception-cli" : "exception-html"),
	'databases.default' => function(ContainerInterface $c) {
		$config = $c->get("Starbug\Core\ConfigInterface");
		$name = $c->get("database_name");
		$params = $config->get("db/".$name);
		return new PDO('mysql:host='.$params['host'].';dbname='.$params['db'], $params['username'], $params['password']);
	},
	'databases.test' => function(ContainerInterface $c) {
		$config = $c->get("Starbug\Core\ConfigInterface");
		$params = $config->get("db/test");
		return new PDO('mysql:host='.$params['host'].';dbname='.$params['db'], $params['username'], $params['password']);
	},
	'Starbug\Core\Database' => DI\object()
															->constructorParameter('database_name', DI\get('database_name'))
															->constructorParameter('pdo', DI\get('databases.default')),
	'Starbug\Core\Template' => DI\object()->constructorParameter('helpers', DI\get('Starbug\Core\HelperFactoryInterface')),
	'Starbug\Core\Schemer' => DI\object()->constructorParameter('modules', DI\get('modules'))

);
?>