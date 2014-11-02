<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
class QueryTest extends PHPUnit_Framework_TestCase {

	/**
	 * Test the IteratorAggregate interface
	 */
	function test_iteration() {
		$query = new query("uris");
		foreach ($query as $uri) {
			$this->assertArrayHasKey("id", $uri);
			$this->assertArrayHasKey("path", $uri);
			break;
		}
	}

	/**
	 * Test table aliases
	 */
	function test_alias() {
		$query = new query("uris as pages");

		//expected output
		$expected = "SELECT pages.* FROM `".P("uris")."` AS `pages`";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test join delimiters:
	 * , - comma for inner join
	 * < - left angle bracket for left join
	 * > - right angle bracket for right join
	 */
	function test_inner_join() {
		$query = new query("uris,users");

		//expected output
		$expected = "SELECT uris.* FROM `".P("uris")."` AS `uris` INNER JOIN `".P("users")."` AS `users` ON uris.owner=users.id";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	function test_left_join() {
		$query = new query("uris<users");

		//expected output
		$expected = "SELECT uris.* FROM `".P("uris")."` AS `uris` LEFT JOIN `".P("users")."` AS `users` ON uris.owner=users.id";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	function test_right_join() {
		$query = new query("uris>users");

		//expected output
		$expected = "SELECT uris.* FROM `".P("uris")."` AS `uris` RIGHT JOIN `".P("users")."` AS `users` ON uris.owner=users.id";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}


	/**
	 * Test ON clauses in query models
	 */
	function test_join_on() {
		$query = new query("uris,users on uris.id=users.id");

		//expected output
		$expected = "SELECT uris.* FROM `".P("uris")."` AS `uris` INNER JOIN `".P("users")."` AS `users` ON uris.id=users.id";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}


	/**
	 * Test aliases in query models
	 */
	function test_join_aliases() {
		$query = new query("uris as pages,users as people");

		//expected output
		$expected = "SELECT pages.* FROM `".P("uris")."` AS `pages` INNER JOIN `".P("users")."` AS `people` ON pages.owner=people.id";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test ON clauses and aliases together in query models
	 */
	function test_join_on_and_aliases() {
		$query = new query("uris as pages,users as people on pages.id=people.id");

		//expected output
		$expected = "SELECT pages.* FROM `".P("uris")."` AS `pages` INNER JOIN `".P("users")."` AS `people` ON pages.id=people.id";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test ON clauses and aliases together using extended syntax
	 */
	function test_join_on_and_aliases_extended() {
		$query = new query("uris as pages");

		$query->innerJoin("users as people")->on("pages.id=people.id");
		//this is also valid
		//$query->innerJoin("users as people on pages.id=people.id");

		//expected output
		$expected = "SELECT pages.* FROM `".P("uris")."` AS `pages` INNER JOIN `".P("users")."` AS `people` ON pages.id=people.id";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test select
	 */
	function test_select() {
		$query = new query("uris");
		$query->select("CONCAT(id, ' ', path) as path");

		// the code above should produce the query below
		$expected = "SELECT CONCAT(id, ' ', path) as path FROM `".P("uris")."` AS `uris`";

		//compare queries
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test expansions in select clauses made by using the dot syntax on reference fields.
	 * For example, uris.owner references users.id, and therefore you can select fields of the references row using the syntax:
	 * SELECT uris.owner.first_name
	 */
	function test_select_expansion() {
		$query = new query("uris");
		$query->select("CONCAT(owner.first_name, ' ', owner.last_name) as name");

		// the code above should produce the query below
		$expected = "SELECT CONCAT(uris_owner.first_name, ' ', uris_owner.last_name) as name ".
			"FROM `".P("uris")."` AS `uris` LEFT JOIN `".P("users")."` AS `uris_owner` ON uris_owner.id=uris.owner";

		//compare queries
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test where clauses
	 */
	function test_where() {
		$query = new query("uris");
		$query->where("uris.path LIKE '%path%'");

		//expected output
		$expected = "SELECT uris.* FROM `".P("uris")."` AS `uris` WHERE uris.path LIKE '%path%'";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test parameterized conditions
	 */
	function test_condition() {
		$query = new query("uris");
		$query->condition("uris.path", "%path%", "LIKE");

		//expected output
		$expected = "SELECT uris.* FROM `".P("uris")."` AS `uris` WHERE uris.path LIKE :default0";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("%path%", $query->parameters[":default0"]);
	}

	/**
	 * Test expansions in condition fields
	 */
	function test_condition_expansion() {
		$query = new query("uris");
		$query->condition("uris.owner.email", "root");

		//expected output
		$expected = "SELECT uris.* FROM `".P("uris")."` AS `uris` LEFT JOIN `".P("users")."` AS `uris_owner` ON uris_owner.id=uris.owner WHERE uris_owner.email = :default0";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("root", $query->parameters[":default0"]);
	}

	/**
	 * Test expansions in condition fields
	 */
	function test_condition_expansion_select() {
		$query = query("uris")->select("uris.owner.email")->condition("uris.owner.email", "root");


		//expected output
		$expected = "SELECT uris_owner.email FROM `".P("uris")."` AS `uris` LEFT JOIN `".P("users")."` AS `uris_owner` ON uris_owner.id=uris.owner WHERE uris_owner.email = :default0";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("root", $query->parameters[":default0"]);
	}

	/**
	 * Test expansions in condition fields
	 */
	function test_condition_expansion_many() {
		$query = new query("terms");
		$query->condition("images.mime_type", "image/png");

		//expected output
		$expected = "SELECT terms.* FROM `".P("terms")."` AS `terms` WHERE :default0 IN (SELECT terms_images.mime_type FROM ".P("terms_images")." terms_images_lookup INNER JOIN ".P("files")." terms_images ON terms_images.id=terms_images_lookup.files_id WHERE terms_images_lookup.terms_id=terms.id)";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("image/png", $query->parameters[":default0"]);
	}

	/**
	 * Test expansions in condition fields
	 * when not using =, !=, IN, or NOT IN the sub-query comparison does not work, and therefore the query should join to obtain the field for comparison
	 */
	function test_condition_expansion_many_join() {
		$query = new query("terms");
		$query->condition("images.mime_type", "image/%", "LIKE")->group("terms.id");

		//expected output
		$expected = "SELECT terms.* FROM `".P("terms")."` AS `terms` LEFT JOIN `".P("terms_images")."` AS `terms_images_lookup` ON terms_images_lookup.terms_id=terms.id LEFT JOIN `".P("files")."` AS `terms_images` ON terms_images.id=terms_images_lookup.files_id WHERE terms_images.mime_type LIKE :default0 GROUP BY terms.id";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("image/%", $query->parameters[":default0"]);
	}

	/**
	 * Test expansions in condition fields
	 */
	function test_condition_expansion_category() {
		$query = new query("settings");
		$query->condition("settings.category.slug", "general");

		//expected output
		$expected = "SELECT settings.* FROM `".P("settings")."` AS `settings` LEFT JOIN `".P("terms")."` AS `settings_category` ON settings_category.id=settings.category WHERE settings_category.slug = :default0";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("general", $query->parameters[":default0"]);
	}

	/**
	 * Test expansions in condition fields
	 */
	function test_condition_expansion_category_explicit() {
		$query = new query("settings");
		$query->condition("settings.category.term", "General");

		//expected output
		$expected = "SELECT settings.* FROM `".P("settings")."` AS `settings` LEFT JOIN `".P("terms")."` AS `settings_category` ON settings_category.id=settings.category WHERE settings_category.term = :default0";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("General", $query->parameters[":default0"]);
	}

	/**
	 * Test expansions in condition fields
	 */
	function test_condition_expansion_terms() {
		$query = new query("uris");
		$query->condition("uris.groups", "user", "!=");

		//expected output
		$expected = "SELECT uris.* FROM `".P("uris")."` AS `uris` WHERE :default0 NOT IN (SELECT uris_groups.slug FROM ".P("uris_groups")." uris_groups_lookup INNER JOIN ".P("terms")." uris_groups ON uris_groups.id=uris_groups_lookup.groups_id WHERE uris_groups_lookup.uris_id=uris.id)";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("user", $query->parameters[":default0"]);
	}

	/**
	 * Test expansions in condition fields and specify the comparator field explicitly
	 */
	function test_condition_expansion_terms_explicit_comparator() {
		$query = new query("uris");
		$query->condition("uris.groups.term", "User", '!=');

		//expected output
		$expected = "SELECT uris.* FROM `".P("uris")."` AS `uris` WHERE :default0 NOT IN (SELECT uris_groups.term FROM ".P("uris_groups")." uris_groups_lookup INNER JOIN ".P("terms")." uris_groups ON uris_groups.id=uris_groups_lookup.groups_id WHERE uris_groups_lookup.uris_id=uris.id)";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("User", $query->parameters[":default0"]);
	}

	/**
	 * Test expansions in where clauses
	 */
	function test_where_expansion_terms() {
		$query = new query("uris");
		$query->where(":group NOT IN uris.groups")->param("group", "user");

		//expected output
		$expected = "SELECT uris.* FROM `".P("uris")."` AS `uris` WHERE :group NOT IN (SELECT uris_groups.slug FROM ".P("uris_groups")." uris_groups_lookup INNER JOIN ".P("terms")." uris_groups ON uris_groups.id=uris_groups_lookup.groups_id WHERE uris_groups_lookup.uris_id=uris.id)";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("user", $query->parameters[":group"]);
	}

	/**
	 * Test grouping
	 */
	function test_grouping() {
		$query = new query("uris");
		$query->select("COUNT(*) as count")->group("type");

		//expected output
		$expected = "SELECT COUNT(*) as count FROM `".P("uris")."` AS `uris` GROUP BY type";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test expansions in GROUP BY clauses
	 */
	function test_grouping_expansion() {
		$query = new query("uris");
		$query->select("COUNT(*) as count")->group("owner.first_name");

		//expected output
		$expected = "SELECT COUNT(*) as count FROM `".P("uris")."` AS `uris` LEFT JOIN `".P("users")."` AS `uris_owner` ON uris_owner.id=uris.owner GROUP BY uris_owner.first_name";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test expansions in GROUP BY clauses
	 */
	function test_grouping_expansion_terms() {
		$query = new query("uris");
		$query->select("COUNT(*) as count")->group("uris.groups");

		//expected output
		$expected = "SELECT COUNT(*) as count FROM `".P("uris")."` AS `uris` LEFT JOIN `".P("uris_groups")."` AS `uris_groups_lookup` ON uris_groups_lookup.uris_id=uris.id LEFT JOIN `".P("terms")."` AS `uris_groups` ON uris_groups.id=uris_groups_lookup.groups_id GROUP BY uris_groups.slug";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test having
	 */
	function test_having_condition() {
		$query = new query("terms");
		$query->group("terms.taxonomy")->select("terms.taxonomy,COUNT(*) as count")->havingCondition("count", "0", ">");

		//expected output
		$expected = "SELECT terms.taxonomy,COUNT(*) as count FROM `".P("terms")."` AS `terms` GROUP BY terms.taxonomy HAVING count > :having0";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("0", $query->parameters[":having0"]);
	}

	/**
	 * Test having
	 */
	function test_sorting() {
		$query = new query("terms");
		$query->sort("taxonomy")->sort("slug", 1)->sort("created", -1);

		//expected output
		$expected = "SELECT terms.* FROM `".P("terms")."` AS `terms` ORDER BY taxonomy, slug ASC, created DESC";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	/**
	 * Test limit
	 */
	function test_limit() {
		$query = new query("terms");
		$query->limit(5)->skip(10);

		//expected output
		$expected = "SELECT terms.* FROM `".P("terms")."` AS `terms` LIMIT 10, 5";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	function test_or_condition() {
		$query = new query("terms");
		$query->condition("taxonomy", "uris_categories")->orCondition("taxonomy", "files_category");

		//expected output
		$expected = "SELECT terms.* FROM `".P("terms")."` AS `terms` WHERE taxonomy = :default0 || taxonomy = :default1";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("uris_categories", $query->parameters[":default0"]);
		$this->assertSame("files_category", $query->parameters[":default1"]);
	}

	function test_or_where() {
		$query = new query("terms");
		$query->where("taxonomy = :tax1")->orWhere("taxonomy = :tax2")->params(array("tax1" => "uris_categories", "tax2" => "files_category"));

		//expected output
		$expected = "SELECT terms.* FROM `".P("terms")."` AS `terms` WHERE taxonomy = :tax1 || taxonomy = :tax2";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("uris_categories", $query->parameters[":tax1"]);
		$this->assertSame("files_category", $query->parameters[":tax2"]);
	}

	function test_or_where_shorter() {
		$query = new query("terms");
		$query->where("taxonomy = :tax1 || taxonomy = :tax2")->params(array("tax1" => "uris_categories", "tax2" => "files_category"));

		//expected output
		$expected = "SELECT terms.* FROM `".P("terms")."` AS `terms` WHERE taxonomy = :tax1 || taxonomy = :tax2";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("uris_categories", $query->parameters[":tax1"]);
		$this->assertSame("files_category", $query->parameters[":tax2"]);
	}

	function test_or_condition_set() {
		$query = new query("terms");

		$query->condition("taxonomy", "uris_categories");

		//recent is an arbritrary set name
		$query->open("recent", "||");
		$query->condition("created", date("Y-m-d")." 00:00:00", ">=")->andCondition("taxonomy", "files_category");

		//expected output
		$expected = "SELECT terms.* FROM `".P("terms")."` AS `terms` WHERE taxonomy = :default0 || (created >= :recent0 && taxonomy = :recent1)";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("uris_categories", $query->parameters[":default0"]);
		$this->assertSame(date("Y-m-d")." 00:00:00", $query->parameters[":recent0"]);
		$this->assertSame("files_category", $query->parameters[":recent1"]);
	}

	function test_search_and() {
		$query = new query("users");
		$query->search("ali gangji", "first_name,last_name");

		//expected output
		$expected = "SELECT users.* FROM `".P("users")."` AS `users` WHERE ((first_name LIKE '%ali%' OR last_name LIKE '%ali%') AND (first_name LIKE '%gangji%' OR last_name LIKE '%gangji%'))";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	function test_search_or() {
		$query = new query("users");
		$query->search("ali or gangji", "first_name,last_name");

		//expected output
		$expected = "SELECT users.* FROM `".P("users")."` AS `users` WHERE ((first_name LIKE '%ali%' OR last_name LIKE '%ali%') or (first_name LIKE '%gangji%' OR last_name LIKE '%gangji%'))";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	function test_search_fields() {
		$query = new query("users");
		$query->search("ali", "first_name");
		$query->search("gangji", "last_name");

		//expected output
		$expected = "SELECT users.* FROM `".P("users")."` AS `users` WHERE ((first_name LIKE '%ali%')) && ((last_name LIKE '%gangji%'))";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	function test_multivalue_term_expansion() {
		$query = new query("uris");
		$query->condition("uris.groups", array("user", "admin"));
		//expected output
		$expected = "SELECT uris.* FROM `".P("uris")."` AS `uris` WHERE (:default0 IN (SELECT uris_groups.slug FROM ".P("uris_groups")." uris_groups_lookup INNER JOIN ".P("terms")." uris_groups ON uris_groups.id=uris_groups_lookup.groups_id WHERE uris_groups_lookup.uris_id=uris.id) || :default1 IN (SELECT uris_groups.slug FROM ".P("uris_groups")." uris_groups_lookup INNER JOIN ".P("terms")." uris_groups ON uris_groups.id=uris_groups_lookup.groups_id WHERE uris_groups_lookup.uris_id=uris.id))";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("user", $query->parameters[":default0"]);
		$this->assertSame("admin", $query->parameters[":default1"]);
	}

	function test_action() {
		$query = new query("uris");
		sb()->user = array("id" => 2);
		$query->action("read");

		//expected output
		$expected = "SELECT uris.* ".
								"FROM `".P("uris")."` AS `uris` ".
								"INNER JOIN `".P("permits")."` AS `permits` ON 'uris' LIKE permits.related_table && 'read' LIKE permits.action ".
								"WHERE ".
									"('global' LIKE permits.priv_type || (permits.priv_type='object' && permits.related_id=uris.id)) && ".
									"(permits.object_statuses is null || permits.object_statuses=uris.statuses) && ".
									"(permits.user_groups is null || permits.user_groups IN (SELECT groups_id FROM ".P("users_groups")." u WHERE u.users_id=2)) && ".
									"(permits.role='everyone' || permits.role='user' && permits.who='' || permits.role='owner' && uris.owner='' || permits.role='groups' && EXISTS (SELECT groups_id FROM ".P("uris_groups")." o WHERE o.uris_id=uris.id && o.groups_id IN (SELECT groups_id FROM ".P("users_groups")." u WHERE u.users_id=2)))";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
	}

	function test_remove() {
		//the delete method is normally an execution method
		//passing false prevents the query from actually running
		$query = new query("uris");
		$query->condition("path", "phpunit")->delete(false);

		//expected output
		$expected = "DELETE uris.* FROM `".P("uris")."` AS `uris` WHERE path = :default0";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("phpunit", $query->parameters[":default0"]);
	}

	function test_insert() {
		//the insert method is normally an execution method
		//passing false prevents the query from actually running
		$query = new query("uris");
		$query->set("path", "phpunit")->insert(false);

		//expected output (actual output contains extra fields due to validation)
		$expected = "INSERT INTO `".P("uris")."` SET `path` = :set0";

		//compare
		$actual = reset(explode(",", $query->build()));
		$this->assertSame($expected, $actual);
		$this->assertSame("phpunit", $query->parameters[":set0"]);
	}

	function test_update() {
		//the update method is normally an execution method
		//passing false prevents the query from actually running
		$query = new query("uris");
		$query->set("title", "PHPUnit");
		$query->condition("path", "phpunit");
		$query->update(false);

		//expected output
		$expected = "UPDATE `".P("uris")."` AS `uris` SET `title` = :set0, `modified` = :set1 WHERE path = :default0";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("PHPUnit", $query->parameters[":set0"]);
		$this->assertSame("phpunit", $query->parameters[":default0"]);
	}

	function test_update_condition_expansion() {
		//the update method is normally an execution method
		//passing false prevents the query from actually running
		$query = new query("uris");
		$query->set("uris.title", "PHPUnit");
		$query->condition("owner.first_name", "phpunit");
		$query->update(false);

		//expected output
		$expected = "UPDATE `".P("uris")."` AS `uris` LEFT JOIN `".P("users")."` AS `uris_owner` ON uris_owner.id=uris.owner SET `uris`.`title` = :set0, `modified` = :set1 WHERE uris_owner.first_name = :default0";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("PHPUnit", $query->parameters[":set0"]);
		$this->assertSame("phpunit", $query->parameters[":default0"]);
	}

	function test_update_set_expansion() {
		//the update method is normally an execution method
		//passing false prevents the query from actually running
		$query = new query("uris");
		$query->set("owner.first_name", "PHPUnit");
		$query->condition("uris.title", "phpunit");
		$query->update(false);

		//expected output
		$expected = "UPDATE `".P("uris")."` AS `uris` LEFT JOIN `".P("users")."` AS `uris_owner` ON uris_owner.id=uris.owner SET `uris_owner`.`first_name` = :set0, `modified` = :set1 WHERE uris.title = :default0";

		//compare
		$actual = $query->build();
		$this->assertSame($expected, $actual);
		$this->assertSame("PHPUnit", $query->parameters[":set0"]);
		$this->assertSame("phpunit", $query->parameters[":default0"]);
	}

}
?>
