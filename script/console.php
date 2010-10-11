<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * This file is part of StarbugPHP
 * @file script/console.php initiates the console
 * @see console
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup script
 */
	if (!defined('BASE_DIR')) define('BASE_DIR', str_replace("/script", "", dirname(__FILE__)));
	set_include_path(get_include_path().PATH_SEPARATOR.BASE_DIR);
	if (!defined('STDOUT')) define("STDOUT", fopen("php://stdout", "wb"));
	if (!defined('STDIN')) define("STDIN", fopen("php://stdin", "r"));
	include("etc/Etc.php");
	include("core/init.php");
	include("core/db/Schemer.php");
	global $schemer;
	$schemer = new Schemer($sb->db);
	$to = file_get_contents(BASE_DIR."/var/migration");
	//MOVE TO CURRENT MIGRATION
	$current = 0;
	while ($current < $to) {
		$migration = new $schemer->migrations[$current]();
		$migration->up();
		$current++;
	}
	$sb->import("util/cli");
?>
