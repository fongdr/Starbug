<?php
namespace Starbug\Db\Query;
interface QueryInterface {
	public function addSelection($field, $alias = false);
	public function getSelection();
	public function removeSelection($alias);
	public function setTable($table, $alias = false);
	public function getTable($alias = false);
	public function hasTable($alias = false);
	public function addTable($table, $alias = false);
	public function getTables();
	public function getAlias($table = false);
	public function addJoin($table, $alias = false);
	public function addInnerJoin($table, $alias = false);
	public function addLeftJoin($table, $alias = false);
	public function addRightJoin($table, $alias = false);
	public function addJoinOne($column, $target, $alias = false);
	public function addJoinMany($column, $target, $alias = false);
	public function getJoin($alias);
	public function getCondition();
	public function getHavingCondition();
	public function addCondition($field, $value = "", $operator = "=", $ops = []);
	public function addWhere($condition, $ops = []);
	public function addHavingCondition($field, $value = "", $operator = "=", $ops = []);
	public function addHavingWhere($condition, $ops = []);
	public function createCondition();
	public function createOrCondition();
	public function setParameter($name, $value = null);
	public function getParameter($name);
	public function getParameters();
	public function addGroup($column);
	public function getGroup();
	public function setGroup(array $group);
	public function addSort($column, $direction = 0);
	public function getSort();
	public function setLimit($limit);
	public function getLimit();
	public function setSkip($skip);
	public function getSkip();
	public function setValue($field, $value);
	public function getValue($field);
	public function getValues();
	public function setValues(array $values);
	public function getMode();
	public function setMode($mode);
	public function isSelect();
	public function isInsert();
	public function isUpdate();
	public function isDelete();
	public function isTruncate();
	public function addExclusion($column);
	public function removeExclusion($column);
	public function isExcluded($column);
}
