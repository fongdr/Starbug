<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * This file is part of StarbugPHP
 * @file util/dojo.php
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup dojo
 */
/**
 * @defgroup dojo
 * dojo utility
 * @ingroup util
 */
$sb->provide("util/dojo");
/**
 * The dojo class. A wrapper for dojo javascript toolkit
 * @ingroup dojo
 */
class dojo {
	var $behaviors = array();
	var $toggles = array();
	function dojo() {
		global $request;
		$request->tags[] = array("tag" => "dojo", "raw_tag" => "dojo");
	}
	function behavior($query, $event, $action) {
		$merge = array($event => array($action));
		if (empty($this->behaviors[$query])) $this->behaviors[$query] = array();
		$this->behaviors[$query] = array_merge_recursive($this->behaviors[$query], $merge);
	}
	function xhr($query, $action, $url, $params="", $event="onclick") {
		$url = "'".uri("")."'+".$url;
		$this->attach($query, "sb.xhr", "action:$action  url:$url".(empty($params) ? "" : "  ".$params), $event);
	}
	function attach($query, $action, $params="", $event="onclick") {
		$params = starr::star($params);
		if (!empty($params['pre'])) {
			$object = $params['pre']."var args = {";
			unset($params['pre']);
		} else $object = "var args = {";
		if (!empty($params['form'])) efault($params['method'], "'post'");
		foreach ($params as $name => $value) $object .= $name." : ".$value.", ";
		$object .= "evt : evt};";
		//$object .= "console.log(args);";
		$action = $object.$action."({args : args});";
		$this->behavior($query, $event, $action);
	}
	function toggle($query, $toggler, $node, $params="") {
		$params = starr::star($params);
		$default = $params['default'];
		$this->attach($query, "sb.toggle", "node:'#$node'  toggler:$toggler");
		$this->toggles[$toggler] = array("node" => $node, "default" => $default, "toggler" => $toggler);
		if ($params['add']) $this->toggles[$toggler]['add'] = $params['add'];
	}
}
/**
 * A global instance of the dojo class
 * @ingroup dojo
 */
global $dojo;
$dojo = new dojo();
?>
