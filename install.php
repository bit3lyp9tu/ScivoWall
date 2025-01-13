<?php
	include("mysql.php");

	$GLOBALS["queries"] = [];

	function print_table_query() {
		if(count($GLOBALS["queries"])) {
			print "<table>";
			foreach ($GLOBALS["queries"] as $query) {
				print "<tr><td>".htmlentities($query)."</td></tr>\n";
			}
			print "</table>";
		} else {
			print("no queries found");
		}
	}

	function run_query ($sql) {
		$result = $GLOBALS["conn"]->query($sql);

		if($result === false) {
			die("ERROR: ".$GLOBALS["conn"]->error);
		}

		if(file_exists("/etc/debug_queries")) {
			print_query_table();//TODO:
		}

		return $result;
	}


	$create_queries = [
		"create database if not exists poster_generator",
		"use poster_generator",

		"create table if not exists user (
			user_id int primary key auto_increment,
			name varchar(256) not null unique,
			pass_sha varchar(256) not null,
			salt varchar(256) not null,
			pepper varchar(256) not null
		)",
		"create table if not exists author (
			id int primary key auto_increment,
			name varchar(256) not null
		)",
		"create table if not exists poster (
		    poster_id int primary key auto_increment,
		    title varchar(256) not null,
		    user_id int references user(user_id) on delete cascade
		)",
		"create table if not exists author_to_poster (
			id int primary key auto_increment,
			author_id int references author(id) on delete cascade,
			poster_id int references poster(poster_id) on delete cascade
		)",
		"create table if not exists image (
			image_id int primary key auto_increment,
			file_name varchar(256) not null,
			content blob not null
		)",
		"create table if not exists box (
			box_id int primary key auto_increment,
			poster_id int references poster(poster_id) on delete cascade,
			content blob not null
		)",
		"create table if not exists session (
			id int primary key auto_increment,
			user_id int references user(user_id) on delete cascade,
			sessionID varchar(256) not null,
			expiration_date DATETIME not null
		)",

		//"insert into user (name, pass_sha, salt, pepper) value ('Max K', 'trhtgxjzjk', 'ututgfzt', 'test');",
		"insert into author (name) value ('max');",
		"insert into poster (title, user_id) value ('Poster Titel', 2);",

		"insert into image (file_name, content) value ('bild.png', '0001')",
		"insert into box (poster_id, content) value (
			1,
			'das ist ein text'
		)",
		"insert into session (user_id, sessionID, expiration_date) value (1, '1234', '2025-12-31')"
	];

	/*
	if(select count(*) from user == 0)
	{
		insert("insert into user (name, pass_sha, salt, pepper) value ('Max K', 'trhtgxjzjk', 'ututgfzt', 'test');",)
	}
	*/

	foreach ($create_queries as $query) {
		$result = run_query("$query\n");
	}
?>
