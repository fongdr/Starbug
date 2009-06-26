<?php
/**
* FILE: etc/init.php
* PURPOSE: provide application wide functionality
* 
* This file is part of StarbugPHP
*
* StarbugPHP - web service development kit
* Copyright (C) 2008-2009 Ali Gangji
*
* StarbugPHP is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* StarbugPHP is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with StarbugPHP.  If not, see <http://www.gnu.org/licenses/>.
*/
include("core/db/adodb_lite/adodb.inc.php");
$db = ADONewConnection('mysql');
$db->Connect(Etc::DB_HOST, Etc::DB_USERNAME, Etc::DB_PASSWORD, Etc::DB_NAME);
function empty_nan(&$val, $default="") {if(!isset($val) || !is_numeric($val)) $val = $default;}
function dfault(&$val, $default="") {if(!isset($val)) $val = $default;}
function efault(&$val, $default="") {if(empty($val)) $val = $default;}
function rA($str="") {return Starr::rstar($str);}
function D_exists($obj) {return file_exists("app/models/".ucwords($obj).".php");}
function D($obj, $data) {$obj = ucwords($obj); if (include_once("app/models/".$obj.".php")) $obj = new $obj($data, strtolower($obj)); else return false; return $obj;}
function P($var) {return Etc::PREFIX.$var;}
function uri($path) {return Etc::WEBSITE_URL.$path;}
class sb {
	function load($what) {
		if (strpos($what, "core/") === 0) $what = "core/plugins".substr($what, 4);
		else $what = "plugins/".$what;
		if (file_exists($what.".php")) include($what.".php");
		else {
			$token = split("/", $what); $token = $what."/".end($token).".php";
			if (file_exists($token)) include($token);
		}
	}
}
?>
