<?php
	include_once(__DIR__ . "/" . "mysql.php");

	$GLOBALS["queries"] = [];

	//TODO:   try {
					// ... connecte zur db...
			// } except {
					//     dier("Cannot connect to database");
			// }

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
		try {
			$result = $GLOBALS["conn"]->query($sql);
		} catch (mysqli_sql_exception $th) {
			die($th->getMessage());
		}

		if($result === false) {
			die("ERROR: ".$GLOBALS["conn"]->error);
		}

		if(file_exists("/etc/debug_queries")) {
			print_query_table();//TODO:
		}

		return $result;
	}

	if (getenv('DB_NAME')) {
		$GLOBALS['dbname'] = getenv('DB_NAME');
	}// else{
	// 	$GLOBALS["dbname"] = "poster_generator";
	// }

	if(!array_key_exists("dbname", $GLOBALS)) {
		$GLOBALS["dbname"] = "poster_generator";
	}

	// print_r("name: " . $GLOBALS['dbname'] . "\n");

	//TODO:   add columns in user: last logged-in date/ date of registration
	//TODO:   add columns in poster: date of creation/ last edit date/ last edited by user_id

	$create_queries = [
		"create database if not exists ".$GLOBALS["dbname"],
		"use ".$GLOBALS["dbname"],

		"create table if not exists user (
			user_id int primary key auto_increment,
			name varchar(256) not null unique,
			pass_sha varchar(256) not null,
			salt varchar(256) not null,
			pepper varchar(256) not null,
			registration_date INT NOT NULL DEFAULT UNIX_TIMESTAMP(),
			last_login_date INT NOT NULL DEFAULT UNIX_TIMESTAMP(),
			access_level INT NOT NULL DEFAULT 1
		)",
		"create table if not exists author (
			id int primary key auto_increment,
			name varchar(256) not null
		)",
		"CREATE TABLE IF NOT EXISTS view_modes (
			ID INT PRIMARY KEY AUTO_INCREMENT,
			name VARCHAR(64) NOT NULL
		)",
		"INSERT INTO view_modes (ID, name)
		SELECT 1, 'public'
		WHERE NOT EXISTS (
			SELECT 1
			FROM view_modes
			LIMIT 1
		)",
		"INSERT INTO view_modes (ID, name)
		SELECT 2, 'private'
		WHERE NOT EXISTS (
			SELECT 1
			FROM view_modes
			WHERE ID = 2
			LIMIT 1
		)",
		"create table if not exists poster (
		    poster_id int primary key auto_increment,
		    title varchar(256) not null,
		    user_id int not null references user(user_id) on delete cascade,
			creation_date INT NOT NULL DEFAULT UNIX_TIMESTAMP(),
			last_edit_date INT NOT NULL DEFAULT UNIX_TIMESTAMP(),
			fk_view_mode INT NOT NULL DEFAULT 2,
			CONSTRAINT fk_view_mode FOREIGN KEY (fk_view_mode) REFERENCES view_modes(ID),
			visible TINYINT(1) NOT NULL DEFAULT 0
		)",
		"create table if not exists author_to_poster (
			id int primary key auto_increment,
			author_id int references author(id) on delete cascade,
			poster_id int not null references poster(poster_id) on delete cascade
		)",
		"create table if not exists image (
			image_id int primary key auto_increment,
			file_name varchar(256) not null,
			upload_date INT NOT NULL DEFAULT UNIX_TIMESTAMP(),
			last_edit_date INT NOT NULL DEFAULT UNIX_TIMESTAMP(),
			type varchar(256) NOT NULL,
			size INT NOT NULL,
			last_modified INT NOT NULL,
			webkit_relative_path varchar(256) NOT NULL,
			data longblob not null,
			fk_poster INT NOT NULL,
			CONSTRAINT fk_poster FOREIGN KEY (fk_poster) REFERENCES poster(poster_id)
		)",
		"create table if not exists box (
			box_id int primary key auto_increment,
			poster_id int not null references poster(poster_id) on delete cascade,
			content blob not null
		)",
		"create table if not exists session (
			id int primary key auto_increment,
			user_id int not null references user(user_id) on delete cascade,
			sessionID varchar(256) not null,
			expiration_date int not null
		)",

		//"insert into user (name, pass_sha, salt, pepper) value ('Max K', 'trhtgxjzjk', 'ututgfzt', 'test');",
		/*
		"insert into author (name) value ('max');",
		"insert into poster (title, user_id) value ('Poster Titel', 2);",

		"insert into image (file_name, content) value ('bild.png', '0001')",
		"insert into box (poster_id, content) value (
			1,
			'das ist ein text'
		)",
		"insert into session (user_id, sessionID, expiration_date) value (1, '1234', '2025-12-31')",

		"create view if not exists ranked_posters AS SELECT ROW_NUMBER() OVER (ORDER BY poster_id) AS local_id, poster_id, user_id FROM poster"
		 */
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
