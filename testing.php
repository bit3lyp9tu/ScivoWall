<?php
	error_reporting(E_ALL);

	set_error_handler(function ($errno, $errstr, $errfile, $errline) {
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	});

	$isCLI = (php_sapi_name() == 'cli');

	if(!$isCLI) {
		echo "Can only be run in CLI";
		exit(0);
	}

	$GLOBALS["tests_failed"] = 0;

	if (sizeof($argv) == 2 && isset($argv[1])) {
		$GLOBALS["dbname"] = $argv[1];
	} else if (getenv("dbname")) {
		$GLOBALS["dbname"] = getenv("getenv");
	} else {
		$GLOBALS["dbname"] = "poster_generator";
	}

	include(__DIR__ . "/" . "queries.php");
	include_once(__DIR__ . "/" . "functions.php");

	function shutdown() {
		$error = error_get_last();

		// runQuery("set FOREIGN_KEY_CHECKS = 0;");
		// runQuery("drop database " . $GLOBALS["dbname"] . ";");
		// runQuery("set FOREIGN_KEY_CHECKS = 1;");

		if ($error !== null) {
			print_red("Script failed with error: " . $error['message']);
			exit(1);
		}

		if ($GLOBALS["tests_failed"] > 0) {
			print_red($GLOBALS["tests_failed"] . " tests failed");
			exit(1);
		} else {
			print_green("All tests successful");
			exit(0);
		}
	}

	register_shutdown_function('shutdown');

	include(__DIR__ . "/" . "install.php");

	include(__DIR__ . "/" . "account_management.php");

	include_once(__DIR__ . "/" . "poster_edit.php");

	function print_green($text) {
		echo "\033[32m$text\033[0m\n";
	}

	function print_red($text) {
		echo "\033[31m$text\033[0m\n";
	}

	function is_equal($x, $y) {
		if($x === $y) {
			return true;
		}
		return false;
	}

	function is_not_equal($x, $y) {
		return !is_equal($x, $y);
	}

	function test_not_equal($name, $x, $y) {
		if(is_equal($x, $y)) {
			print_red("Test [$name] failed");
			$GLOBALS["tests_failed"]++;
		}
	}

	function test_equal($name, $x, $y) {
		if(is_not_equal($x, $y)) {
			$bt = debug_backtrace();
			$caller = array_shift($bt);
			print_red("Test [$name](" . $caller['line'] . ") failed.");
			print("Expected:\n$y\n");
			print("Got:\n$x\n");
			$GLOBALS["tests_failed"]++;
		}
	}

	test_equal("default length for generate_salt is 32", strlen(generate_salt()), 32);
	test_equal("generate_salt(10)", strlen(generate_salt(10)), 10);

	test_equal("isDocker()", gettype(isDocker()), "boolean");

	/*
	$new_id = insertQuery("insert ...");
	test_not_equal("new id", $new_id, null);
	if($new_id !== null) {
		test_equal("zweite fkt die nur ausgeführt wird wenn erste geht", bla, blubb);
		} else {
			test_equal("diese fkt failt immer", 0, 1);
	}
	*/

	// check query to attribute filter
	test_equal("get column name A", implode(",", getColumnNames("SELECT id, name FROM author AS t;")), 'id,name');
	test_equal("get column name B", implode(",", getColumnNames("SELECT id AS a, name FROM author;")), 'a,name');
	// test_equal("get column name C", implode(",", getColumnNames(
	// 	"SELECT e.name,
	//        (SELECT MAX(salary)
	// 	   FROM employees
	// 	   WHERE department_id = e.department_id
	// 	   ) AS highest_salary_in_dept
	// 	FROM employees e;
	// ")), 'e.name,highest_salary_in_dept');
	test_equal("get column name error", implode(",", getColumnNames("SELECT id AS")), '[ERROR]: SELECT id AS does not match');
	$sql2 = "SELECT id, name FROM author, (SELECT author_id FROM author_to_poster WHERE author_to_poster.poster_id=?) AS sub WHERE sub.author_id=author.id";
	test_equal("A", implode(",", getColumnNames($sql2)), "id,name,author_id");
	$str2 = "SELECT e.name, MAX(sa.lary) AS ttt
		FROM employees
		WHERE department_id = e.department_id
		) AS highest_salary_in_dept
		FROM employees e;";
	test_equal("B", implode(",", getColumnNames($str2)), 'e.name,ttt');
	$str3 = "SELECT title, from_unixtime(last_edit_date) AS last_edit, visible FROM poster WHERE fk_view_mode=?";
	test_equal("C", implode(",", getColumnNames($str3)), "title,last_edit,visible");
	test_equal("get difficult column", implode(",", getColumnNames("SELECT title, from_unixtime(last_edit_date) AS last_edit, visible FROM poster WHERE fk_view_mode=?", 1)), "title,last_edit,visible");


	// test_equal("", implode(",", getColumnNames("SELECT * FROM author;")), 'id,name');


	// check get tables of query
	test_equal("get table error", implode(",", getTableNames("SELECT * FR")), '[ERROR]: SELECT * FR does not match');
	test_equal("get simple table", implode(",", getTableNames("SELECT * FROM user WHERE id=1;")), 'user');
	test_equal("get two tables", implode(",", getTableNames("SELECT * FROM user, author WHERE id=1;")), 'user,author');
	test_equal("get with subtable", implode(",", getTableNames("SELECT * FROM user, (SELECT * FROM poster, session) AS abc WHERE id=1;")), 'user');
	test_equal("get tables with alias", implode(",", getTableNames("SELECT * FROM user AS a, session AS b, author;")), 'user,session,author');
	//TODO:   test get complex table with linebreaks
	// test_equal("get complex table with linebreaks",
	// 			implode(",", getTableNames(
	// 				"SELECT *
	// 				FROM user
	// 				AS a, session AS b, author;"
	// 			)), 'user,session,author');

	// check type converter
	test_equal("type dedection int", getTypeStr(1), "i");
	test_equal("type dedection str", getTypeStr("test"), "s");
	test_equal("type dedection list of int and str", getTypeStr(1, 'test', 10, 'test2'), "isis");
	test_equal("type dedection 4", getTypeStr("TestingTitle2"), "s");

	// check get table description
	test_equal("get attributes of session table", implode(",", getDBHeader("session")["Field"]), 'id,user_id,sessionID,expiration_date');


	//SQL Queries
	test_equal("select query no result", sizeof(runQuery("SELECT * FROM session")), 0);
	// print_r(getterQuery2("SELECT id, user_id FROM session;"));
	test_equal("new getter query", implode(",",getterQuery2("SELECT id, user_id FROM session;")["id"]), '');

	test_equal("insert query", insertQuery("INSERT INTO user (name, pass_sha, salt, pepper) VALUE (?, ?, ?, ?)", "ssss", 'Test-Name', '0bf301312acc91474e96e1a07422a791', 'vAfcB"$2NE[C}Rpw)9vhI/-4YPS<}?@F', 'a2d47c981889513c5e2ddbca71f414'), "success");
	// test_equal("select query get single result", json_encode(runQuery("SELECT user_id FROM user"))[0][0], "1");

	test_equal("get inserted id", getLastInsertID(), 88);

	// print_r(getterQuery2("SELECT name FROM user;"));
	test_equal("test getter query kleene", implode(",", getterQuery2("SELECT * FROM user;")["name"]), 'max5,bug,Admin,Max Mustermann,Anne Beispielfrau,Test-Name');
	test_equal("getter query unequal amount of references and given params",
				getterQuery2("SELECT title, user_id FROM poster WHERE poster.title=?")["[ERROR]"],
				"Found param-references '?' (1) in query does not match the amound of params (0) given.");
	test_equal("getter query unequal amount of references and given params2",
				getterQuery2("SELECT title, user_id FROM poster;", 1)["[ERROR]"],
				"Found param-references '?' (0) in query does not match the amound of params (1) given.");

	//TODO:
	// $result = json_encode(getterQuery2("SELECT user_id, name, pass_sha, salt, pepper, access_level FROM user WHERE user.name = ?", "Test-Name"), true);
	// test_equal("select query get json result", $result,
	// 	'{"user_id":[1],"name":["Test-Name"],"pass_sha":["0bf301312acc91474e96e1a07422a791"],"salt":["vAfcB\"$2NE[C}Rpw)9vhI\/-4YPS<}?@F"],"pepper":["a2d47c981889513c5e2ddbca71f414"],"access_level":[1]}'
	// );
	$result = json_encode(getterQuery2("SELECT user_id, name, pass_sha, salt, pepper, access_level FROM user WHERE user.name = ?", "---"), true);
	test_equal("select query getter", $result, '{"user_id":[],"name":[],"pass_sha":[],"salt":[],"pepper":[],"access_level":[]}');

	// Empty Delete???
	test_equal("delete query", deleteQuery("DELETE FROM poster WHERE poster.title = ?", "s", "Testing Title"), "successfully deleted");
	test_equal("delete query check if removed", json_encode(runQuery("SELECT title FROM poster")), '[["test1"],["test4"],["fxhfdf"],["dxfgbfdffdbdfxbfbxbf"],["Climate Change Effects in the Arctic"],["AI in Modern Healthcare"],["The Future of Urban Farming"]]');

	test_equal("update query new entry", insertQuery("INSERT INTO poster (title, user_id) VALUE (?, ?)", "si", 'TestingTitle', 86), "success");
	test_equal("update query edit", editQuery("UPDATE poster SET \n poster.title=? \n WHERE poster.title=? \n AND poster.user_id=?", "sss", 'TestingTitle2', 'TestingTitle', 1), "successfully updated");

	test_equal("update query check status", json_encode(getterQuery2("SELECT title, user_id FROM poster WHERE poster.title=?", "fxhfdf"), true), '{"title":["fxhfdf"],"user_id":[82]}');
	test_equal("update query cleanup", deleteQuery("DELETE FROM poster WHERE poster.title = ?", "s", "fxhfdf"), "successfully deleted");


	$str = "(A1 (B1 (C1 (D1))) A2 ((C1) (C1) B1) (B1) A3)";
	test_equal("", buildBrackets(resolveBrackets($str)), $str);

	$str = "(grxhgrdxA1 (A1 iehroo))";
	test_equal("", buildBrackets(resolveBrackets($str)), $str);

	$str = "(A1)";
	test_equal("", buildBrackets(resolveBrackets($str)), $str);

	$str = "(A1 A1 A1 A1 A1 A1 A1A1)";
	test_equal("", buildBrackets(resolveBrackets($str)), $str);

	$str = "A1";
	test_equal("", buildBrackets(resolveBrackets($str)), $str);

	$str = "(A1 (B1))";
	test_equal("", buildBrackets(resolveBrackets($str)), $str);


	// Account Management
	test_equal("delete user", deleteQuery("DELETE FROM user WHERE user.name = ?", "s", "testing"), "successfully deleted");
	$result = json_encode(getterQuery2("SELECT user_id, name FROM user WHERE user.User_id > ?", 0), true);
	test_equal("delete user", $result, '{"user_id":[19,82,85,86,87,88],"name":["max5","bug","Admin","Max Mustermann","Anne Beispielfrau","Test-Name"]}');

	// TODO: need testing
	// test_equal("test is table users empty", isEmpty(), 1);

	test_equal("register new user", register("testing", "1A_aaaaaaaaaa"), "success");

	test_equal("test is table users not empty", isEmpty(), 0);
	// TODO: test fails (user_id does not exist)
	// test_equal("test", isAdmin(1), true);

	test_equal("register with same username twice", register("testing", "1A_aaaaaaaaaa"), "The user testing already exists.");
	test_equal("register with number as username", register(123, "1A_aaaaaaaaaa"), "success");
	test_equal("register bad password msg", register("testing2", "123"), "Password not complex enough");

	//TODO:  	print_r(getterQuery2("SELECT LAST_INSERT_ID() AS id;"));

	test_equal("login unknown username", login("---", "---"), "Wrong Username or Password");
	test_equal("login with wrong password", login("testing", "---"), "Wrong Username or Password");
	test_equal("login successfully", login("testing", "1A_aaaaaaaaaa"), "Correct Password");
	// //TODO:   check after correct session during login

	test_equal("user does not exist", isAdmin(1), 'user_id does not exist');
	test_equal("is user non-admin", isAdmin(86), false);
	$result = editQuery("UPDATE user SET user.access_level=? WHERE user.user_id=?", "ii", 3, 1);
	test_equal("is user admin", isAdmin(85), true);

	editQuery("UPDATE poster SET fk_view_mode=?", "i", 1);
	updateVisibility(1, true);
	test_equal("update visibility", json_encode(getterQuery2("SELECT visible FROM poster"), true), '{"visible":[1,0,0,0,1,1,0]}');
	updateVisibility(1, false);
	test_equal("update visibility", json_encode(getterQuery2("SELECT visible FROM poster"), true), '{"visible":[0,0,0,0,1,1,0]}');

	test_equal("poster does not exist", isPublic(1), 0);
	test_equal("is public false", isPublic(349), 0);
	editQuery("UPDATE poster SET visible=?", "i", 1);
	test_equal("is public true", isPublic(350), 1);

	// print_r(json_decode(create_project("new Project", 1), true)["title"]);
	test_equal("create new project", implode(",", json_decode(create_project("new Project", 86), true)["title"]), 'TestingTitle,new Project');
	test_equal("fetch all projects db check", json_encode(getterQuery2("SELECT poster_id, title, user_id, visible FROM poster"), true),
	'{"poster_id":[108,112,132,349,350,351,353,354],' .
		'"title":["test1","test4","dxfgbfdffdbdfxbfbxbf","Climate Change Effects in the Arctic","AI in Modern Healthcare","The Future of Urban Farming","TestingTitle","new Project"],' .
		'"user_id":[82,82,82,19,19,19,86,86],"visible":[1,1,1,1,1,1,1,0]}');

	test_equal("fetch all projects", implode(",", json_decode(fetch_projects(19), true)["title"]), 'Climate Change Effects in the Arctic,AI in Modern Healthcare,The Future of Urban Farming');

	// TODO:
	// test_equal("fetch authors user is working with", implode(',', fetch_authors(1)["name"]), '');

	test_equal("delete project", implode(",", json_decode(delete_project(1, 19), true)["title"]), 'AI in Modern Healthcare,The Future of Urban Farming');
	test_equal("delete project db check", json_encode(getterQuery2("SELECT poster_id, title FROM poster"), true), '{"poster_id":[108,112,132,350,351,353,354],"title":["test1","test4","dxfgbfdffdbdfxbfbxbf","AI in Modern Healthcare","The Future of Urban Farming","TestingTitle","new Project"]}');


	test_equal("Password complexity empty", getPwComplexityLevel(""), 0);
	test_equal("Password complexity length", getPwComplexityLevel("aaaaaaaaaaaaa"), 1);
	test_equal("Password complexity contains number", getPwComplexityLevel("1aaaaaaaaaaaa"), 2);
	test_equal("Password complexity contains upper letter", getPwComplexityLevel("1Aaaaaaaaaaaa"), 3);
	test_equal("Password complexity contains special char", getPwComplexityLevel("1A_aaaaaaaaaa"), 4);


	test_equal("check View Modes", json_encode(getterQuery2("SELECT name FROM view_modes"), true), '{"name":["public","private"]}');


	$new_proj = addProject(87, "First Project");
	test_equal("new project creation success", $new_proj, "success success success");

	$check_poster = getterQuery2("SELECT poster_id, title FROM poster");
	test_equal("new project check poster", json_encode($check_poster, true), '{"poster_id":[108,112,132,350,351,353,354,355],"title":["test1","test4","dxfgbfdffdbdfxbfbxbf","AI in Modern Healthcare","The Future of Urban Farming","TestingTitle","new Project","First Project"]}');

	$check_author = getterQuery2("SELECT id, name FROM author");
	test_equal("new project heck author", json_encode($check_author, true), '{"id":[35,38,348,349,351,352,353,354,355,356,357],"name":["Author5","Author8","Du","Max","BlaBla","ChatGPT","Alice Johnson","Dr. Rahul Mehta","Lina Chen","Marcus Lee","Anne Beispielfrau"]}');

	$check_a_to_p = getterQuery2("SELECT id, author_id, poster_id FROM author_to_poster");
	test_equal("new project heck author_to_poster", json_encode($check_a_to_p, true), '{"id":[16,18,370,371,372,376,377,378,379],"author_id":[38,35,352,355,356,352,353,355,357],"poster_id":[108,108,350,350,350,351,351,351,355]}');

	addBox(351, "Text Content");
	addBox(351, "Text Content 2");
	$check_box = getterQuery2("SELECT * FROM box");
	test_equal("add box fill content check", json_encode($check_box, true), '{"box_id":[29,30,31,237,238,239,240,241,242,243,244],"poster_id":[108,108,108,350,350,350,351,351,351,351,351],"content":["Text 1","Other Text","New Text","# Background\n\nHow AI is being used in diagnostics.","# Data\n\nPatient outcome statistics from 2015-2022.","# Conclusion\n\nAI improves diagnostic accuracy by 12%.","# Concept\n\nVertical farms and hydroponic systems.","# Case Studies\n\nTokyo and New York initiatives.","# Impact\n\nIncreased yields with 70% less water usage.","Text Content","Text Content 2"]}');

	editBox(1, 351, "New Text");
	$check_box = getterQuery2("SELECT * FROM box");
	test_equal("edit box", json_encode($check_box, true), '{"box_id":[29,30,31,237,238,239,240,241,242,243,244],"poster_id":[108,108,108,350,350,350,351,351,351,351,351],"content":["Text 1","Other Text","New Text","# Background\n\nHow AI is being used in diagnostics.","# Data\n\nPatient outcome statistics from 2015-2022.","# Conclusion\n\nAI improves diagnostic accuracy by 12%.","New Text","# Case Studies\n\nTokyo and New York initiatives.","# Impact\n\nIncreased yields with 70% less water usage.","Text Content","Text Content 2"]}');

	addAuthor("Other Author");
	$check_author = getterQuery2("SELECT * FROM author");
	test_equal("add author", json_encode($check_author, true), '{"id":[35,38,348,349,351,352,353,354,355,356,357,358],"name":["Author5","Author8","Du","Max","BlaBla","ChatGPT","Alice Johnson","Dr. Rahul Mehta","Lina Chen","Marcus Lee","Anne Beispielfrau","Other Author"]}');

	$lasest_author_id = getLastInsertID();
	connectAuthorToPoster($lasest_author_id, 351);
	$check_a_t_p = getterQuery2("SELECT * FROM author_to_poster");
	test_equal("add author to poster", json_encode($check_a_t_p, true), '{"id":[16,18,370,371,372,376,377,378,379,380],"author_id":[38,35,352,355,356,352,353,355,357,358],"poster_id":[108,108,350,350,350,351,351,351,355,351]}');

	test_equal("title getter", getTitle(108), 'test1');
	test_equal("title setter A", setTitle(108, 'ABC'), "successfully updated");
	test_equal("title setter B", getTitle(108), "ABC");

	test_equal("author getter", implode(",", getAuthors(350)["name"]), 'ChatGPT,Lina Chen,Marcus Lee');
	test_equal("authors null",  json_encode(getAuthors(1), true), '{"id":[],"name":[],"author_id":[]}');

	test_equal("boxes getter", implode(",", getBoxes(349)), '');
	test_equal("boxes getter empty", sizeof(getBoxes(100)), 0);

	deleteBox(2, 3);
	test_equal("delete box", implode(",", getBoxes(3)), '');

	// TODO:   rename_author

	removeAuthor(4, 351);
	test_equal("remove author", implode(",", getAuthors(351)["name"]), 'ChatGPT,Alice Johnson,Lina Chen');

	// test_equal("add list of authors", addAuthors(349, ["author1", "auhtor2", "author3"]), '[success|success],[success|success],[success|success],');
	// test_equal("added list of authors correctly", implode(",", getAuthors(349)["name"]), 'Test-Name');

	// TODO:   fix return msg to: successfully deleted[success|success],[success|success],[success|success],
	// test_equal("overwrite Authors", overwriteAuthors(351, ["author2", "author3", "author4"]), 'successfully deleted[success|success],|success],[success|success],');
	// test_equal("overwrite Authors check content", implode(",", getAuthors(3)["name"]), 'Test-Name');

	// overwriteBoxes(2, array("Content A"));
	// test_equal("overwrite boxes equal size edit", implode(",", getBoxes(3)), 'Content A');

	// overwriteBoxes(2, array("Content A", "Content B", "Content C", "Content D", "Content E", "Content F"));
	// test_equal("overwrite boxes addition", implode(",", getBoxes(3)), 'Content A,Content B,Content C,Content D,Content E,Content F');

	// print_r(getBoxes(3));

	//TODO:   removal of Boxes
	// overwriteBoxes(3, array("Content C"));
	// test_equal("overwrite boxes removal", implode(",", getBoxes(3)), 'Content C');
	// overwriteBoxes(3, array());
	// test_equal("overwrite boxes empty-removal", implode(",", getBoxes(3)), '');

	test_equal("get visibility options", implode(",", getVisibilityOptions()), 'public,private');
	test_equal("get poster visibility", getVisibility(355), 2);

	test_equal("get poster visibility", getVisibility(351), 1);
	test_equal("set visibility mode", setViewMode(19, 2, 2), 'successfully updated');
	test_equal("get poster visibility", getVisibility(351), 2);

	//update last edit date
	$sleep_time = 1;
	//poster
	test_equal("update last edit date poster 1", updateEditDate("poster", 2), 'successfully updated');
	$t1 = getterQuery2("SELECT last_edit_date FROM poster WHERE poster.poster_id=?", 350)["last_edit_date"][0];
	sleep($sleep_time);
	updateEditDate("poster", 350);
	// $t2 = getterQuery2("SELECT last_edit_date FROM poster WHERE poster.poster_id=?", 350)["last_edit_date"][0];
	// print_r($t1 + $sleep_time);
	// test_equal("update last edit date poster 2", ($t1 + $sleep_time == $t2) ? 1 : 0, 1);
	// print_r($t2);
	// //user
	// $user_id = 2;
	// test_equal("update last edit date user 1", updateEditDate("user", $user_id), 'successfully updated');

	// print_r(getterQuery2("SELECT last_login_date FROM user WHERE user.user_id=?", $user_id));

	// $t1 = getterQuery2("SELECT last_login_date FROM user WHERE user.user_id=?", $user_id)["last_login_date"][0];
	// sleep($sleep_time);
	// updateEditDate("user", $user_id);
	// $t2 = getterQuery2("SELECT last_login_date FROM user WHERE user.user_id=?", $user_id)["last_login_date"][0];
	// test_equal("update last edit date user 2", ($t1 + $sleep_time == $t2) ? 1 : 0, 1);


	//image //TODO:
	// print_r(getDBHeader("image")["Field"]);
	// $image_id = 1;
	// test_equal("update last edit date image 1", updateEditDate("image", $image_id), 'successfully updated');
	// $t1 = getterQuery2("SELECT last_edit_date FROM image WHERE image.image_id=?", $image_id)["last_edit_date"][0];
	// print_r(getterQuery2("SELECT last_edit_date FROM image WHERE image.image_id=?", $image_id));
	// sleep($sleep_time);
	// updateEditDate("image", $image_id);
	// $t2 = getterQuery2("SELECT last_edit_date FROM image WHERE image.image_id=?", $image_id)["last_edit_date"][0];
	// test_equal("update last edit date image 2", ($t1 + $sleep_time == $t2) ? 1 : 0, 1);

	// print_r(unpack('H*', 'AB101')[1]);
	// print_r(str2bin('AB101'));

	// print_r($DB_NAME);


	// admin filter
	test_equal("filter min empty", solve_min("name", json_decode(
		'{
			"attributes": {
				"name": {
					"min": "",
					"max": "",
					"list": [
						"max5"
					]
				}
			}
		}', true
	)), "");
	test_equal("filter min", solve_min("name", json_decode(
		'{
			"attributes": {
				"name": {
					"min": "1",
					"max": "",
					"list": [
						"max5"
					]
				}
			}
		}', true
	)), " name >= 1 ");
	test_equal("filter max empty", solve_max("name", json_decode(
		'{
			"attributes": {
				"name": {
					"min": "",
					"max": "",
					"list": [
						"max5"
					]
				}
			}
		}', true
	)), "");
	test_equal("filter max", solve_max("name", json_decode(
		'{
			"attributes": {
				"name": {
					"min": "1",
					"max": "5",
					"list": [
						"max5"
					]
				}
			}
		}', true
	)), " name <= 5 ");

	test_equal("filter list", solve_list("name", json_decode(
		'{
			"attributes": {
				"name": {
					"min": "1",
					"max": "5",
					"list": [
					]
				}
			}
		}', true
	)), "");
	test_equal("filter list", solve_list("name", json_decode(
		'{
			"attributes": {
				"name": {
					"min": "1",
					"max": "5",
					"list": [
						"max5",
						"Admin"
					]
				}
			}
		}', true
	)), " name IN ('max5','Admin') ");

	$json = '{
		"attributes": {
			"user.name": {
				"min": "",
				"max": "",
				"list": [
					"max5"
				]
			},
			"poster.title": {
				"min": "",
				"max": "",
				"list": [
					"The Future of Urban Farming"
				]
			},
			"last_edit_date": {
				"min": "",
				"max": "",
				"list": [
				]
			},
			"visible": {
				"min": "",
				"max": "",
				"list": [
					"1"
				]
			},
			"view_modes.name": {
				"min": "",
				"max": "",
				"list": [
					"private",
					"public"
				]
			}
		}
	}';
	test_equal("filter projects empty", filter_projects('{}'), "");
	test_equal(
		"filter projects",
		filter_projects($json),
		" AND  user.name IN ('max5') AND poster.title IN ('The Future of Urban Farming') AND visible IN (1) AND view_modes.name IN ('private','public') "
	);

	$sanitized = sanitize_filter(" AND  user.name IN ('max5') AND poster.title IN ('The Future of Urban Farming') AND last_edit_date >= 1.5 AND last_edit_date <= 5 AND view_modes.name IN ('private','public') ");
	test_equal(
		"sanitize filtered input - var",
		implode(",", $sanitized["var"]),
		"max5,The Future of Urban Farming,1.5,5,private,public"
	);

	test_equal(
		"sanitize filtered input - sql 0",
		sanitize_filter("")["sql"],
		""
	);
	test_equal(
		"sanitize filtered input - sql 1",
		sanitize_filter(" AND  user.name IN ('max5') ")["sql"],
		" AND  user.name IN (?) "
	);
	test_equal(
		"sanitize filtered input - sql 2",
		sanitize_filter(" AND  user.name IN ('max5','Admin') ")["sql"],
		" AND  user.name IN (?,?) "
	);
	test_equal(
		"sanitize filtered input - sql 3",
		sanitize_filter(" AND last_edit_date >= 1.5 AND last_edit_date <= 5 ")["sql"],
		" AND last_edit_date >= ? AND last_edit_date <= ? "
	);
	test_equal(
		"sanitize filtered input - sql 4",
		sanitize_filter(" AND last_edit_date >= 1.5 AND last_edit_date <= 5 AND user.name IN ('max5','Admin') ")["sql"],
		" AND last_edit_date >= ? AND last_edit_date <= ? AND user.name IN (?,?) "
	);

	login("Admin", "PwScaDS-2025");
	$result = json_decode(fetch_projects_all(85, $json), true);
	test_equal("fetch filtered projects - name", $result["user.name"][0], 'max5');
	test_equal("fetch filtered projects - title", $result["title"][0], 'The Future of Urban Farming');
	test_equal("fetch filtered projects not admin", fetch_projects_all(86, $json), 'Not Admin');

	test_equal(
		"filter interface content",
		getFilterSelectables(85),
		'{"user":{"name":["123","Admin","Anne Beispielfrau","bug","Max Mustermann","max5","Test-Name","testing"]},"title":{"title":["ABC","test4","dxfgbfdffdbdfxbfbxbf","AI in Modern Healthcare","The Future of Urban Farming","TestingTitle","new Project","First Project"]},"last_edit":{"min":0,"max":2147483647},"visible":{"min":0,"max":1},"view_mode":{"name":["public","private"]}}'
	);

	/*
	name:	bug
	pw:
	A+a2d47c981889513c5e2ddbca71f414

	max5	abc
	Admin	PwScaDS-2025
	Max Mustermann 		AbC123-98xy
	Anne Beispielfrau	ghy23_ghjAA
	*/

?>
