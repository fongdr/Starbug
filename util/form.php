<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * This file is part of StarbugPHP
 * @file util/form.php
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup form
 */
/**
 * @defgroup form
 * form utility
 * @ingroup util
 */
$sb->provide("util/form");
/**
 * Used to build XHTML forms
 * @ingroup form
 */
class form {
	/**
	 * @var string The name of the model, if this form submits to a model
	 */
	var $model;
	/**
	 * @var string The name of the function, if this form submits to a model
	 */
	var $action;
	/**
	 * @var string The URL to submit to
	 */
	var $url;
	/**
	 * @var string The submission method (get or post)
	 */
	var $method;
	/**
	 * @var string The URL to post back to if there is an error
	 */
	var $postback;

	/**
	 * constructor. initializes properties
	 * @param string $args a named parameter string with any initial values
	 */
	function __construct($args="") {
		global $request;
		$request_tag = array("tag" => "form", "raw_tag" => "form");
		if (!in_array($request_tag, $request->tags)) $request->tags = array_merge($request->tags, array($request_tag));
		$args = starr::star($args);
		efault($args['url'], $args['uri']);
		efault($args['url'], $_SERVER['REQUEST_URI']);
		efault($args['method'], "post");
		efault($args['postback'], $request->path);
		$this->model = $args['model'];
		$this->action = $args['action'];
		$this->url = $args['url'];
		$this->method = $args['method'];
		$this->postback = $args['postback'];
	}

	/**
	 * outputs the opening form tag and some hidden inputs
	 * @param string $atts attributes for the form tag
	 */
	function open($atts="") {
		$open = '<form'.(($atts) ? " ".$atts : "").' action="'.$this->url.'" method="'.$this->method.'">'."\n";
		if ($this->method == "post") $open .= '<input class="postback" name="postback" type="hidden" value="'.$this->postback.'" />'."\n";
		if (!empty($this->action)) $open .= '<input class="action" name="action['.$this->model.']" type="hidden" value="'.$this->action.'" />'."\n";
		if (!empty($_POST[$this->model]['id'])) $open .= '<input id="id" name="'.$this->model.'[id]" type="hidden" value="'.$_POST[$this->model]['id'].'" />'."\n";
		return $open;
	}
	
	/**
	 * get the full name attribute
	 * eg. name becomes users[name]
	 * eg. name[] becomes users[name][]
	 * @param string $name the relative name
	 * @return the full name
	 */
	function get_name($name) {
		if (empty($this->model)) return $name;
		else if (false !== strpos($name, "[")) {
			$parts = explode("[", $name, 2);
			return $this->model."[".$parts[0]."][".$parts[1];
		} else return $this->model."[".$name."]";
	}

	/**
	 * get the POST or GET value from the relative name
	 * @param string $name the relative name
	 * @return string the GET or POST value
	 */
	function get($name) {
		$parts = explode("[", $name);
		if ($this->method == "post") $var = (empty($this->model)) ? $_POST : $_POST[$this->model];
		else $var = (empty($this->model)) ? $_GET : $_GET[$this->model];
		foreach($parts as $p) $var = $var[rtrim($p, "]")];
		return $var;
	}
	
	/**
	 * set the GET or POST value
	 * @param string $name the relative name
	 * @param string $value the value
	 */
	function set($name, $value) {
		$parts = explode("[", $name);
		$key = array_pop($parts);
		if (empty($this->model)) {
			if ($this->method == "post") $var = &$_POST;
			else $var = &$_GET;
		} else {
			if ($this->method == "post") $var = &$_POST[$this->model];
			else $var = &$_GET[$this->model];
		}
		foreach($parts as $p) {
			$var = &$var[rtrim($p, "]")];
		}
		$var[$key] = $value;
	}


	function fill_ops(&$ops) {
		$ops = starr::star($ops);
		$name = array_shift($ops);
		$ops['name'] = $name;
		//id, label, and class
		if (empty($ops['id'])) $ops['id'] = $ops['name'];
		if (empty($ops['label'])) $ops['label'] = str_replace("_", " ", ucwords($ops['name']));
		if (empty($ops['error'][$ops['name']])) $ops['error'][$ops['name']] = "This field is required.";
		$ops['class'] = (empty($ops['class'])) ? $ops['type'] : $ops['class']." ".$ops['type'];
	}

	function label(&$ops) {
		global $sb;
		$name = reset(explode("[", $ops['name']));
		if (!isset($ops['nolabel'])) $lab = '<label for="'.$ops['id'].'"'.((empty($ops['identifier_class'])) ? '' : ' class="'.$ops['identifier_class'].'"').'>'.$ops['label']."</label>";
		else unset($ops['nolabel']);
		if (isset($sb->errors[$this->model][$name])) foreach($sb->errors[$this->model][$name] as $err => $message) $lab .= "\n"."<span class=\"error\">".((!empty($ops['error'][$err])) ? $ops['error'][$err] : $message)."</span>";
		unset($ops['label']);
		unset($ops['error']);
		return $lab;
	}
	
	function form_control($tag, $ops, $self=false) {
		$ops['name'] = $this->get_name($ops['name']);
		$ops = array_merge(array($tag), $ops);
		return $this->tag($ops, $self);
	}

	function text($ops) {
		return $this->input("text", $ops);
	}

	function password($ops) {
		return $this->input("password", $ops);
	}

	function hidden($ops) {
		$ops = $ops."  nolabel:true";
		return $this->input("hidden", $ops);
	}

	function submit($ops="") {
		return $this->tag("input  type:submit".((empty($ops))? "" : "  ".$ops), true);
	}

	function file($ops) {
			$ops = $ops."  type:file";
			$this->fill_ops($ops);
			$input = $this->label($ops)."\n";
			if (!empty($_POST[$ops['name']])) $ops['value'] = $_POST[$ops['name']];
			$ops = array_merge(array("input"), $ops);
			return $input.$this->tag($ops, true);
	}

	function image($ops) {
		return $this->input("image", $ops);
	}

	function checkbox($ops) {
		$ops = $ops."  type:checkbox";
		$this->fill_ops($ops);
		$input = $this->label($ops)."\n";
		if ($this->get($ops['name']) == $ops['value']) $ops['checked'] = 'checked';
		return $input.$this->form_control("input", $ops, true);
	}

	function radio($ops) {
		return $this->input("radio", $ops);
	}

	function input($type, $ops) {
		$ops = $ops."  type:$type";
		$this->fill_ops($ops);
		$input = $this->label($ops)."\n";
		//POSTed or default value
		$var = $this->get($ops['name']);
		if (!empty($var)) $ops['value'] = $var;
		else if (!empty($ops['default'])) {
			$ops['value'] = $ops['default'];
			unset($ops['default']);
		}
		return $input.$this->form_control("input", $ops, true);
	}

	function select($ops, $options=array()) {
		$this->fill_ops($ops);
		$select = $this->label($ops)."\n";
		$var = $this->get($ops['name']);
		if ((empty($var)) && (!empty($ops['default']))) {
			$this->set($ops['name'], $ops['default']);
			unset($ops['default']);
		}
		if (!empty($ops['range'])) {
			$range = split(":", $ops['range']);
			for ($i=$range[0];$i<=$range[1];$i++) $options[$i] = $i;
			unset($ops['range']);
		}
		if (!empty($ops['caption'])) {
			if (!empty($ops['from'])) $options = query($ops['from']);
			$list = array();
			foreach ($options as $o) $list[$o[$ops['caption']]] = $o[$ops['value']];
			$options = $list; unset($ops['caption']); unset($ops['value']);
		}
		$ops['content'] = "";
		foreach ($options as $caption => $val) $ops['content'] .= "<option value=\"$val\"".(($this->get($ops['name']) == $val) ? " selected=\"true\"" : "").">$caption</option>\n";
		return $select.$this->form_control("select", $ops);
	}

	function date_select($ops) {
		global $sb;
		$sb->import("util/datepicker");
		$this->fill_ops($ops);
		$select = $this->label($ops)."\n";
		//FILL VALUES FROM POST OR DEFAULT
		$name = $ops['name'];
		$value = $this->get($name);
		efault($value, $ops['default']);
		if (!empty($value)) {
			$dt = strtotime($value);
			$this->set($name, array("year" => date("Y", $dt), "month" => date("m", $dt), "day" => date("d", $dt)));
			if (!empty($ops['time_select'])) $this->set($name."_time", array("hour" => date("h", $dt), "minutes" => date("i", $dt), "ampm" => date("a", $dt)));
		}
		//SETUP OPTION ARRAYS
		$month_options = array("Month" => "-1", "January" => "1", "February" => "2", "March" => "3", "April" => "4", "May" => "5", "June" => "6", "July" => "7", "August" => "8", "September" => "9", "October" => "10", "November" => "11", "December" => "12");
		$day_options = array("Day" => "-1");
		for($i=1;$i<32;$i++) $day_options["$i"] = $i;		
		$year = date("Y");
		$year_options = array("Year" => "-1", $year => $year, (((int) $year)+1) => (((int) $year)+1));
		//BUILD SELECT BOXES
		$select .= $this->select($name."[month]  id:".$ops['id']."-mm  nolabel:", $month_options);
		$select .= $this->select($name."[day]  id:".$ops['id']."-dd  nolabel:", $day_options);
		$select .= $this->select($name."[year]  id:".$ops['id']."  class:split-date range-low-".date("Y-m-d")." no-transparency  nolabel:", $year_options);
		//TIME
		if (!empty($ops['time_select'])) $select .= $this->time_select(array_merge(array($name), $ops));
		return $select;
	}

	function time_select($ops) {
		$this->fill_ops($ops);
		$select = $this->label($ops)."\n";
		//GET POSTED OR DEFAULT VALUE
		$value = $this->get($ops['name']);
		efault($value, $ops['default']);
		if ((!empty($value)) && (!is_array($value))) {
			$dt = strtotime($value);
			$this->set($ops['name'], array("hour" => date("h", $dt), "minutes" => date("i", $dt), "ampm" => date("a", $dt)));
		}
		//SETUP OPTION ARRAYS
		$hour_options = array("Hour" => "-1");
		for($i=1;$i<13;$i++) $hour_options[$i] = $i;
		$minutes_options = array("Minutes" => "-1", "00" => "00", "15" => "15", "30" => "30", "45" => "45");
		$ampm_options = array("AM" => "am", "PM" => "pm");
		//BUILD SELECT BOXES
		$select .= $this->select($ops['name']."[hour]  id:".$ops['id']."-hour  nolabel:", $hour_options);
		$select .= $this->select($ops['name']."[minutes]  id:".$ops['id']."-minutes  nolabel:", $minutes_options);
		$select .= $this->select($ops['name']."[ampm]  id:".$ops['id']."  nolabel:", $ampm_options);
		return $select;
	}
	
	function textarea($ops) {
		$this->fill_ops($ops);
		$input = $this->label($ops)."\n";
		efault($ops['cols'], "90");
		efault($ops['rows'], "90");
		//POSTed or default value
		$value = $this->get($ops['name']);
		if (!empty($ops['default'])) {
			efault($value, $ops['default']);
			unset($ops['default']);
		}
		efault($ops['content'], $value);
		//name close
		return $input.$this->form_control("textarea", $ops);
	}

	function tag($tag, $self=false) {
		if (is_array($tag)) $name = array_shift($tag);
		else {
			$parts = explode("  ", $tag, 2);
			$name = $parts[0];
			if (count($parts) > 1) $tag = starr::star($parts[1]);
		}
		$content = $tag['content']; unset($tag['content']); $str = "";
		foreach($tag as $key => $value) $str .= " $key=\"$value\"";
		return ($self) ? "<$name$str />" : "<$name$str>$content</$name>";
	}
	
}
/**
 * outputs a text field
 * @param string $ops the options
 * @ingroup form
 */
function text($ops) {
	global $global_form;
	echo $global_form->text($ops);
}
/**
 * outputs a password
 * @param string $ops the options
 * @ingroup form
 */
function password($ops) {
	global $global_form;
	echo $global_form->password($ops);
}
/**
 * outputs a hidden field
 * @param string $ops the options
 * @ingroup form
 */
function hidden($ops) {
	global $global_form;
	echo $global_form->hidden($ops);
}
/**
 * outputs a submit button
 * @param string $ops the options
 * @ingroup form
 */
function submit($ops="") {
	global $global_form;
	echo $global_form->submit($ops);
}
/**
 * outputs a file input
 * @param string $ops the options
 * @ingroup form
 */
function file_select($ops) {
	global $global_form;
	echo $global_form->file($ops);
}
/**
 * outputs an image input
 * @param string $ops the options
 * @ingroup form
 */
function image($ops) {
	global $global_form;
	echo $global_form->image($ops);
}
/**
 * outputs a checkbox input
 * @param string $ops the options
 * @ingroup form
 */
function checkbox($ops) {
	global $global_form;
	echo $global_form->checkbox($ops);
}
/**
 * outputs a radio button
 * @param string $ops the options
 * @ingroup form
 */
function radio($ops) {
	global $global_form;
	echo $global_form->radio($ops);
}
/**
 * outputs an input
 * @param string $ops the options
 * @ingroup form
 */
function input($type, $ops) {
	global $global_form;
	echo $global_form->input($type, $ops);
}
/**
 * outputs a select field
 * @param string $ops the field info
 * @param array $options the option elements
 * @ingroup form
 */
function select($ops, $options=array()) {
	global $global_form;
	echo $global_form->select($ops, $options);
}
/**
 * outputs a date select
 * @param string $ops the options
 * @ingroup form
 */
function date_select($ops) {
	global $global_form;
	echo $global_form->date_select($ops);
}
/**
 * outputs a time select
 * @param string $ops the options
 * @ingroup form
 */
function time_select($ops) {
	global $global_form;
	echo $global_form->time_select($ops);
}
/**
 * outputs a textarea
 * @param string $ops the options
 * @ingroup form
 */
function textarea($ops) {
	global $global_form;
	echo $global_form->textarea($ops);
}
/**
 * outputs a closing form tag
 * @ingroup form
 */
function close_form() {
	echo "</form>";
}
?>