<?php
namespace Starbug\Core;
class TableDisplay extends ItemDisplay {
	public $type = "table";
	public $template = "table";
	function build($options = array()) {
		$this->options = $options;
		$this->attributes["class"][] = "table";
		$this->attributes["class"][] = "table-bordered";
		$this->attributes["class"][] = "table-striped";
		$this->attributes["class"][] = "table-hover";
		$this->build_display($options);
	}
}
