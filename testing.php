<?php

$isCLI = (php_sapi_name() == 'cli');

if(!$isCLI) {
	echo "Can only be run in CLI";
	exit(0);
}

$GLOBALS["tests_failed"] = 0;
$GLOBALS["dbname"] = "poster_generator_test";

include("queries.php");
include_once("functions.php");

function shutdown() {
	runSingleQuery("set FOREIGN_KEY_CHECKS = 0;", false);
	runSingleQuery("drop database ".$GLOBALS["dbname"], false);
	runSingleQuery("set FOREIGN_KEY_CHECKS = 1;", false);

	if ($GLOBALS["tests_failed"] > 0) {
		print_red($GLOBALS["tests_failed"] . " tests failed");
		exit(1);
	} else {
		print_green("All tests successful");
		exit(0);
	}
}

register_shutdown_function('shutdown');

include("install.php");

include("account_management.php");

include_once("poster_edit.php");

function print_green($text) {
	echo "\033[32m$text\033[0m\n";
}

function print_red($text) {
	echo "\033[31m$text\033[0m\n";
}

function is_equal ($x, $y) {
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
		print_red("Test [$name] failed.");
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
test_equal("get column name A", implode(",", getCoulmnNames("SELECT id, name FROM author AS t;")), 'id,name');
test_equal("get column name B", implode(",", getCoulmnNames("SELECT id AS a, name FROM author;")), 'a,name');
// test_equal("get column name C", implode(",", getCoulmnNames(
// 	"SELECT e.name,
//        (SELECT MAX(salary)
// 	   FROM employees
// 	   WHERE department_id = e.department_id
// 	   ) AS highest_salary_in_dept
// 	FROM employees e;
// ")), 'e.name,highest_salary_in_dept');
test_equal("get column name error", implode(",", getCoulmnNames("SELECT id AS")), '[ERROR]: SELECT id AS does not match');
$sql2 = "SELECT id, name FROM author, (SELECT author_id FROM author_to_poster WHERE author_to_poster.poster_id=?) AS sub WHERE sub.author_id=author.id";
test_equal("A", implode(",", getCoulmnNames($sql2)), "id,name,author_id");
$str2 = "SELECT e.name, MAX(sa.lary) AS ttt
	   FROM employees
	   WHERE department_id = e.department_id
	   ) AS highest_salary_in_dept
	FROM employees e;";
test_equal("B", implode(",", getCoulmnNames($str2)), 'e.name,ttt');
$str3 = "SELECT title, from_unixtime(last_edit_date) AS last_edit, visible FROM poster WHERE fk_view_mode=?";
test_equal("C", implode(",", getCoulmnNames($str3)), "title,last_edit,visible");
test_equal("get difficult column", implode(",", getCoulmnNames("SELECT title, from_unixtime(last_edit_date) AS last_edit, visible FROM poster WHERE fk_view_mode=?", 1)), "title,last_edit,visible");


// test_equal("", implode(",", getCoulmnNames("SELECT * FROM author;")), 'id,name');


// check get tables of query
test_equal("get table error", implode(",", getTableNames("SELECT * FR")), '[ERROR]: SELECT * FR does not match');
test_equal("get simple table", implode(",", getTableNames("SELECT * FROM user WHERE id=1;")), 'user');
test_equal("get two tables", implode(",", getTableNames("SELECT * FROM user, author WHERE id=1;")), 'user,author');
test_equal("get with subtable", implode(",", getTableNames("SELECT * FROM user, (SELECT * FROM poster, session) AS abc WHERE id=1;")), 'user');
test_equal("get tables with alias", implode(",", getTableNames("SELECT * FROM user AS a, session AS b, author;")), 'user,session,author');
//TODO: test get complex table with linebreaks
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
test_equal("select query no result", runSingleQuery("SELECT * FROM session"), "No results found");
// print_r(getterQuery2("SELECT id, user_id FROM session;"));
test_equal("new getter query", implode(",",getterQuery2("SELECT id, user_id FROM session;")["id"]), '');

test_equal("insert query", insertQuery("INSERT INTO user (name, pass_sha, salt, pepper) VALUE (?, ?, ?, ?)", "ssss", 'Test-Name', '0bf301312acc91474e96e1a07422a791', 'vAfcB"$2NE[C}Rpw)9vhI/-4YPS<}?@F', 'a2d47c981889513c5e2ddbca71f414'), "success");
test_equal("select query get single result", runSingleQuery("SELECT user_id FROM user"), "<div>1</div>");

test_equal("get inserted id", getLastInsertID(), 1);

// print_r(getterQuery2("SELECT name FROM user;"));
test_equal("test getter query kleene", implode(",", getterQuery2("SELECT * FROM user;")["name"]), 'Test-Name');
test_equal("getter query unequal amount of references and given params",
			getterQuery2("SELECT title, user_id FROM poster WHERE poster.title=?")["[ERROR]"],
			"Found param-references '?' (1) in query does not match the amound of params (0) given.");
			test_equal("getter query unequal amount of references and given params2",
			getterQuery2("SELECT title, user_id FROM poster;", 1)["[ERROR]"],
			"Found param-references '?' (0) in query does not match the amound of params (1) given.");

			$result = json_encode(getterQuery2("SELECT user_id, name, pass_sha, salt, pepper, access_level FROM user WHERE user.name = ?", "Test-Name"), true);
//TODO
// test_equal("select query get json result", $result,
// 	'{"user_id":[1],"name":["Test-Name"],"pass_sha":["0bf301312acc91474e96e1a07422a791"],"salt":["vAfcB\"$2NE[C}Rpw)9vhI\/-4YPS<}?@F"],"pepper":["a2d47c981889513c5e2ddbca71f414"],"access_level":[1]}'
// );
$result = json_encode(getterQuery2("SELECT user_id, name, pass_sha, salt, pepper, access_level FROM user WHERE user.name = ?", "---"), true);
test_equal("select query getter", $result, '{"user_id":[],"name":[],"pass_sha":[],"salt":[],"pepper":[],"access_level":[]}');

// // Empty Delete???
// test_equal("delete query", deleteQuery("DELETE FROM poster WHERE poster.title = ?", "s", "Testing Title"), "successfully deleted");
// test_equal("delete query check if removed", runSingleQuery("SELECT title FROM poster"), "No results found");

test_equal("update query new entry", insertQuery("INSERT INTO poster (title, user_id) VALUE (?, ?)", "si", 'TestingTitle', 1), "success");
test_equal("update query edit", editQuery("UPDATE poster SET \n poster.title=? \n WHERE poster.title=? \n AND poster.user_id=?", "sss", 'TestingTitle2', 'TestingTitle', 1), "successfully updated");

test_equal("update query check status", json_encode(getterQuery2("SELECT title, user_id FROM poster WHERE poster.title=?", "TestingTitle2"), true), '{"title":["TestingTitle2"],"user_id":[1]}');
test_equal("update query cleanup", deleteQuery("DELETE FROM poster WHERE poster.title = ?", "s", "TestingTitle"), "successfully deleted");


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


// //Account Management
test_equal("delete user", deleteQuery("DELETE FROM user WHERE user.name = ?", "s", "testing"), "successfully deleted");
$result = json_encode(getterQuery2("SELECT user_id, name FROM user WHERE user.User_id > ?", 0), true);
test_equal("delete user", $result, '{"user_id":[1],"name":["Test-Name"]}');


test_equal("register new user", register("testing", "1A_aaaaaaaaaa"), "success");
test_equal("register with same username twice", register("testing", "1A_aaaaaaaaaa"), "The user testing already exists.");
test_equal("register with number as username", register(123, "1A_aaaaaaaaaa"), "success");
test_equal("register bad password msg", register("testing2", "123"), "Password not complex enough");

//TODO:	print_r(getterQuery2("SELECT LAST_INSERT_ID() AS id;"));

test_equal("login unknown username", login("---", "---"), "Wrong Username or Password");
test_equal("login with wrong password", login("testing", "---"), "Wrong Username or Password");
test_equal("login successfully", login("testing", "1A_aaaaaaaaaa"), "Correct Password");
// //TODO: check after correct session during login

test_equal("is user non-admin", isAdmin(1), false);
$result = editQuery("UPDATE user SET user.access_level=? WHERE user.user_id=?", "ii", 3, 1);
test_equal("is user admin", isAdmin(1), true);

editQuery("UPDATE poster SET fk_view_mode=?", "i", 1);
updateVisibility(1, true);
test_equal("update visibility", json_encode(getterQuery2("SELECT visible FROM poster"), true), '{"visible":[1]}');
updateVisibility(1, false);
test_equal("update visibility", json_encode(getterQuery2("SELECT visible FROM poster"), true), '{"visible":[0]}');

test_equal("is public false", isPublic(1), false);
editQuery("UPDATE poster SET visible=?", "i", 1);
test_equal("is public true", isPublic(1), true);

test_equal("create new project", create_project("new Project", 1), '{"title":["TestingTitle2","new Project"]}');
test_equal("fetch all projects db check", json_encode(getterQuery2("SELECT poster_id, title, user_id, visible FROM poster"), true), '{"poster_id":[1,2],"title":["TestingTitle2","new Project"],"user_id":[1,1],"visible":[1,0]}');

test_equal("fetch all projects", implode(",", fetch_projects(1)["title"]), 'TestingTitle2,new Project');

// TODO
// test_equal("fetch authors user is working with", implode(',', fetch_authors(1)["name"]), '');

test_equal("delete project", delete_project(1, 1), '{"title":["new Project"]}');
test_equal("delete project db check", json_encode(getterQuery2("SELECT poster_id, title FROM poster"), true), '{"poster_id":[2],"title":["new Project"]}');


test_equal("Password complexity empty", getPwComplexityLevel(""), 0);
test_equal("Password complexity length", getPwComplexityLevel("aaaaaaaaaaaaa"), 1);
test_equal("Password complexity contains number", getPwComplexityLevel("1aaaaaaaaaaaa"), 2);
test_equal("Password complexity contains upper letter", getPwComplexityLevel("1Aaaaaaaaaaaa"), 3);
test_equal("Password complexity contains special char", getPwComplexityLevel("1A_aaaaaaaaaa"), 4);


test_equal("check View Modes", json_encode(getterQuery2("SELECT name FROM view_modes"), true), '{"name":["public","private"]}');


$new_proj = addProject(1, "First Project");
test_equal("new project creation success", $new_proj, "success success success");

$check_poster = getterQuery2("SELECT poster_id, title FROM poster");
test_equal("new project check poster", json_encode($check_poster, true), '{"poster_id":[2,3],"title":["new Project","First Project"]}');

$check_author = getterQuery2("SELECT id, name FROM author");
test_equal("new project heck author", json_encode($check_author, true), '{"id":[1],"name":["Test-Name"]}');

$check_a_to_p = getterQuery2("SELECT id, author_id, poster_id FROM author_to_poster");
test_equal("new project heck author_to_poster", json_encode($check_a_to_p, true), '{"id":[1],"author_id":[1],"poster_id":[3]}');

addBox(3, "Text Content");
addBox(3, "Text Content 2");
$check_box = getterQuery2("SELECT * FROM box");
test_equal("add box fill content check", json_encode($check_box, true), '{"box_id":[1,2],"poster_id":[3,3],"content":["Text Content","Text Content 2"]}');

editBox(1, 3, "New Text");
$check_box = getterQuery2("SELECT * FROM box");
test_equal("edit box", json_encode($check_box, true), '{"box_id":[1,2],"poster_id":[3,3],"content":["New Text","Text Content 2"]}');

addAuthor("Other Author");
$check_author = getterQuery2("SELECT * FROM author");
test_equal("add author", json_encode($check_author, true), '{"id":[1,2],"name":["Test-Name","Other Author"]}');

connectAuthorToPoster(2,3);
$check_a_t_p = getterQuery2("SELECT * FROM author_to_poster");
test_equal("add author to poster", json_encode($check_a_t_p, true), '{"id":[1,2],"author_id":[1,2],"poster_id":[3,3]}');

test_equal("title getter", getTitle(3), 'First Project');
test_equal("title setter A", setTitle(3, 'Changed Title'), "successfully updated");
test_equal("title setter B", getTitle(3), "Changed Title");

test_equal("author getter", implode(",", getAuthors(3)["name"]), 'Test-Name,Other Author');
test_equal("authors null",  json_encode(getAuthors(1), true), '{"id":[],"name":[],"author_id":[]}');

test_equal("boxes getter", implode(",", getBoxes(3)), 'New Text,Text Content 2');
test_equal("boxes getter empty", sizeof(getBoxes(100)), 0);

deleteBox(2, 3);
test_equal("delete box", implode(",", getBoxes(3)), 'New Text');

removeAuthor(2, 3);
test_equal("remove author", implode(",", getAuthors(3)["name"]), 'Test-Name');

test_equal("add list of authors", addAuthors(3, ["author1", "auhtor2", "author3"]), '[success|success],[success|success],[success|success],');
test_equal("added list of authors correctly", implode(",", getAuthors(3)["name"]), 'Test-Name,author1,auhtor2,author3');

test_equal("overwrite Authors", overwriteAuthors(3, ["author2", "author3", "author4"]), 'successfully deleted[success|success],[success|success],[success|success],');
test_equal("overwrite Authors check content", implode(",", getAuthors(3)["name"]), 'author2,author3,author4');

overwriteBoxes(3, array("Content A"));
test_equal("overwrite boxes equal size edit", implode(",", getBoxes(3)), 'Content A');

overwriteBoxes(3, array("Content A", "Content B", "Content C", "Content D", "Content E", "Content F"));
test_equal("overwrite boxes addition", implode(",", getBoxes(3)), 'Content A,Content B,Content C,Content D,Content E,Content F');

// print_r(getBoxes(3));

//TODO removal of Boxes
// overwriteBoxes(3, array("Content C"));
// test_equal("overwrite boxes removal", implode(",", getBoxes(3)), 'Content C');
// overwriteBoxes(3, array());
// test_equal("overwrite boxes empty-removal", implode(",", getBoxes(3)), '');

test_equal("get visibility options", implode(",", getVisibilityOptions()), 'public,private');
test_equal("get poster visibility", getVisibility(2), 2);

//update last edit date
$sleep_time = 1;
//poster
test_equal("update last edit date poster 1", updateEditDate("poster", 2), 'successfully updated');
$t1 = getterQuery2("SELECT last_edit_date FROM poster WHERE poster.poster_id=?", 2)["last_edit_date"][0];
sleep($sleep_time);
updateEditDate("poster", 2);
$t2 = getterQuery2("SELECT last_edit_date FROM poster WHERE poster.poster_id=?", 2)["last_edit_date"][0];
test_equal("update last edit date poster 2", ($t1 + $sleep_time == $t2) ? 1 : 0, 1);
//user
$user_id = 2;
test_equal("update last edit date user 1", updateEditDate("user", $user_id), 'successfully updated');
$t1 = getterQuery2("SELECT last_login_date FROM user WHERE user.user_id=?", $user_id)["last_login_date"][0];
sleep($sleep_time);
updateEditDate("user", $user_id);
$t2 = getterQuery2("SELECT last_login_date FROM user WHERE user.user_id=?", $user_id)["last_login_date"][0];
test_equal("update last edit date user 2", ($t1 + $sleep_time == $t2) ? 1 : 0, 1);
//image //TODO
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





/*
name:	bug
pw:
A+a2d47c981889513c5e2ddbca71f414

Admin
PwScaDS-2025
*/
?>
