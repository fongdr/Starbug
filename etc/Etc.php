<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * This file is part of StarbugPHP
 * @file etc/Etc.php The project wide configuration file
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup etc
 */
include(BASE_DIR."/etc/Host.php");
/**
 * holds configuration constants
 * access configuration constants via this class using Etc::CONSTANT_NAME
 * @ingroup etc
 */
class Etc extends Host {
	/**
	* the name of your application
	*/
	const WEBSITE_NAME = "Starbug";
	/**
	* a tagline or short description for your application
	*/
	const TAGLINE = "Fresh XHTML and CSS, just like mom used to serve!";

	/**
	* CSS directory
	*/
	const STYLESHEET_DIR = "app/public/stylesheets/";
	/**
	* image directory
	*/
	const IMG_DIR = "app/public/images/";

/**
 * default template used for missing and forbidden pages
 */
	const DEFAULT_TEMPLATE = "templates/View";
	/**
	* default page to load when request uri is /
	*/
	const DEFAULT_PATH = "home";
}
include(BASE_DIR."/etc/constraints.php");
?>
