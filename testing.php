<?php
$GLOBALS["tests_failed"] = 0;

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
		print("Expected:\n$x\n");
		print("Got:\n$y\n");
		$GLOBALS["tests_failed"]++;
	}
}


if ($GLOBALS["tests_failed"] > 0) {
	print_red($GLOBALS["tests_failed"] . " tests failed");
	exit(1);
} else {
	print_green("All tests successful");
	exit(0);
}
?>
