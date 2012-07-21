<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * @file core/app/templates/grid.php
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup templates
 * Default template implementation to display a list of data in a grid with options to edit and delete.
 *
 * Available variables:
 * - $query: a starbug style query string. eg: "users  select:first_name,last_name,id  where:memberships & 2  orderby:last_name ASC"
 * - $columns: (optional) an array of column overrides. set a column to false to hide it
 * - $attributes: (optional) attributes for the table
 * - $view: (optional) view name. only show fields within this view
 */
	js("starbug/grid/EnhancedGrid");
	$attributes = star($attributes);

	//SET UP ATTRIBUTES
	$attributes['model'] = $model;
	$attributes['data-dojo-props'] = array();
	efault($attributes['id'], $attributes['model']."_grid");
	efault($attributes['jsId'], $attributes['id']);
	efault($attributes['style'], "width:100%");
	efault($attributes['autoHeight'], "100");
	efault($attributes['rowsPerPage'], "100");
	efault($attributes['data-dojo-type'], "starbug.grid.EnhancedGrid");
	if (!empty($attributes['orderColumn'])) efault($attributes['plugins'], array("nestedSorting" => true, "dnd" => true));
	else efault($attributes['plugins'], array("nestedSorting" => true));
	if ($query) $attributes['action'] = $query;
	foreach ($attributes as $k => $v) {
		if (!in_array($k, array("id", "jsId", "class", "style", "data-dojo-type", "data-dojo-props"))) {
			$attributes['data-dojo-props'][$k] = $v;
			unset($attributes[$k]);
		}
	}
	$attributes['data-dojo-props'] = trim(str_replace('"', "'", json_encode($attributes['data-dojo-props'])), '{}');
	
	//SET UP COLUMNS
	efault($columns, array());
	$ordered_columns = array();
	$options = schema($model);
	foreach ($options['fields'] as $name => $field) {
		$field['field'] = $name;
		$name = ucwords(str_replace('_',' ',$name));
		
		if ($options['list'] == "all") efault($field['list'], true);
		else efault($field['list'], false);

		if (!empty($field['views'])) {
			$field_views = explode(",", $field['views']);
			$field['list'] = (in_array($view, $field_views));
		}
		efault($field['width'], "auto");
		if ((($field['display']) && ($field['list'])) || isset($columns[$name])) $ordered_columns[$name] = empty($columns[$name]) ? $field : $columns[$name];
	}
	$ordered_columns["Options"] = empty($columns["Options"]) ? "field:id  width:100  cellType:starbug.grid.cells.Options  options:'Edit':'".uri($request->path)."/update/%id%', 'Delete':'javascript:sb.post({\'action[$model]\':\'delete\', \'".$model."[id]\':%id%}, \'return confirm(\\\\\'Are you sure you want to delete this item?\\\\\')\');'" : $columns["Options"];
	
	//RENDER TABLE
	assign("attributes", $attributes);
	assign("columns", $ordered_columns);
	render("table");
	
?>
