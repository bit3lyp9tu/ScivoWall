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
		print_red("Test $name failed");
		$GLOBALS["tests_failed"]++;
	}
}

function test_equal($name, $x, $y) {
	if(is_not_equal($x, $y)) {
		print_red("Test $name failed.");
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

?>
