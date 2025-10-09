<?php
    include_once(__DIR__ . "/" . "queries.php");
    include_once(__DIR__ . "/" . "install.php");
    include_once(__DIR__ . "/" . "functions.php");
    include_once(__DIR__ . "/" . "account_management.php");


    // TODO:   check for all functions using poster_id if id exists
    // TODO:   check for all functions using user_id if id exists

    function getTitle($poster_id) {
        //TODO:   add test if id doesnt exists

        $title = getterQuery(
            "SELECT title
            FROM poster
            WHERE poster.poster_id=?",
            $poster_id
        );

        return $title["title"][0];
    }
    function setTitle($poster_id, $title) {
        $result = editQuery(
            "UPDATE poster SET poster.title=?
            WHERE poster.poster_id=?",
            "si", $title, $poster_id
        );
        return $result;
    }

    function getAuthors($poster_id) {
        $author_names = getterQuery(
            "SELECT id, name
            FROM
                author, (
                    SELECT author_id
                    FROM author_to_poster
                    WHERE author_to_poster.poster_id=?
                ) AS sub
            WHERE sub.author_id=author.id",
            $poster_id
        );
        return $author_names;
    }

    function getBoxes($poster_id) {
        $content = getterQuery(
            "SELECT content
            FROM box
            WHERE box.poster_id=?",
            $poster_id
        )["content"];

        return $content;
    }

    function addBox($poster_id, $content="") {
        $result = insertQuery(
            "INSERT INTO box (poster_id, content) VALUE (?, ?)",
            "is", $poster_id, $content
        );
        return $result;
    }
    function editBox($local_id, $poster_id, $content="") {
        $result = editQuery(
            "UPDATE box SET box.content=?
            WHERE box.box_id=(
                SELECT box_id
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY box_id) AS local_id, box_id
                    FROM box
                    WHERE box.poster_id=?
                ) AS ranked_boxes
                WHERE local_id=?
            )",
            "sii", $content, $poster_id, $local_id
        );
        return $result;
    }

    function overwriteBoxes($poster_id, $new_content) {
	    runQuery("START TRANSACTION");
	    $boxes = getBoxes($poster_id);

	    $old_size = count($boxes);
	    $new_size = count($new_content);

	    if ($new_size != 0) {
		    for ($i = 0; $i < $new_size; $i++) {
			    if(!editBox($i + 1, $poster_id, $new_content[$i])) {
				    runQuery("ROLLBACK");
				    return false;
			    }
		    }

		    if ($old_size < $new_size) {
			    for ($i = $old_size; $i < $new_size; $i++) {
				    if(!addBox($poster_id, $new_content[$i])) {
					    runQuery("ROLLBACK");
					    return false;
				    }
			    }
		    } else if ($old_size > $new_size) {

			    for ($i = $old_size - 1; $i > $new_size - 1; $i--) {
				    if(!deleteBox($i+1, $poster_id)) {
					    runQuery("ROLLBACK");
					    return false;
				    }
			    }
		    }
	    } else {
		    for ($i = $old_size; $i > 0; $i--) {
			    if(!deleteBox($i, $poster_id)) {
				    runQuery("ROLLBACK");
				    return false;
			    }
		    }
	    }

	    runQuery("COMMIT");

	    return true;
    }

    function getFullImage($name, $poster_id) {
        return json_encode(getterQuery(
            "SELECT file_name, type, size, last_modified, data FROM image WHERE fk_poster=? AND file_name=? LIMIT 1",
            $poster_id, $name
        ), true);
    }

    // function str2bin($str) {
    //     $binary = '';
    //     for ($i = 0; $i < strlen($str); $i++) {
    //         $binary .= sprintf("%08b", ord($str[$i]));  // Converts each character to 8-bit binary
    //     }
    //     return  b'' . $binary;
    // }

    function addImage($json_data, $poster_id) {
	    $data_hash = hash("sha512", $json_data["data"]);

	    $file_name = $json_data["name"];

	    // Hole die Info ob das Bild bereits existiert
	    $image_already_exists_filename = getterQuery(
		    "SELECT file_name, data_hash FROM image where (file_name = ? or data_hash = ?) and fk_poster = ?",
		    $file_name, $data_hash, $poster_id
	    );

	    if(!preg_match("/^\d+$/", $poster_id)) {
		    die("Poster-ID is not an integer");
	    }

	    // Wenn es noch nicht existiert, füge es ein und returne einfach den Dateinamen
	    // Wenn ein Bild mit diesem Dateinamen bereits existiert aber der Inhalt anders ist, ändere den Dateinamen mit einem Suffix

	    if(count($image_already_exists_filename["file_name"]) == 0) {
		    $result = insertQuery(
			    "INSERT INTO image (file_name, type, size, last_modified, webkit_relative_path, data, fk_poster, data_hash)
			    VALUE (?, ?, ?, ?, ?, ?, ?, ?)", "ssiissis",
			    $json_data["name"], $json_data["type"], $json_data["size"],
			    $json_data["last_modified"], $json_data["webkit_relative_path"], $json_data["data"], $poster_id, $data_hash
		    );
	    } else {
		    // Wenn es unter dem Filename bereits existiert und der Inhalt anders ist, füge die Datei mit anderem Filename ein

		    if (in_array($data_hash, $existing_hashes)) {
			    $index = 0;

			    while (true) {
				    $new_name = str_replace(".", $index++ . ".", $json_data["name"]);

				    $image_already_exists_filename = getterQuery(
					    "SELECT file_name FROM image where file_name = ? and fk_poster = ?",
					    $new_name, $poster_id
				    );

				    if(count($image_already_exists_filename["file_name"]) == 0) {
					    break;
				    }
			    }

			    $result = insertQuery(
				    "INSERT INTO image (file_name, type, size, last_modified, webkit_relative_path, data, fk_poster, data_hash)
				    VALUE (?, ?, ?, ?, ?, ?, ?, ?)", "ssiissis",
				    $new_name, $json_data["type"], $json_data["size"],
				    $json_data["last_modified"], $json_data["webkit_relative_path"], $json_data["data"], $poster_id, $data_hash
			    );
		    }
	    }

	    $image_already_exists_filename = getterQuery(
		    "SELECT file_name FROM image where data_hash = ? and fk_poster = ?",
		    $data_hash, $poster_id
	    );

	    if(!count($image_already_exists_filename["file_name"])) {
		    die("FEHLER!!!! War leer!!!!! Inserting war irgendwie kaputt");
	    }

	    return $image_already_exists_filename["file_name"][0];
    }

    function deleteBox($local_id, $poster_id) {
        $result = deleteQuery(
            "DELETE FROM box
            WHERE box.box_id=(
                SELECT box_id
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY box_id) AS row_nr, box_id
                    FROM box
                    WHERE box.poster_id=?
                ) AS ranked_boxes
                WHERE row_nr=?
            )",
            "ii", $poster_id, $local_id
        );
        return $result;
    }

    function addAuthor($name) {
        $result = insertQuery(
            "INSERT INTO author (name) VALUE (?)",
            "s", $name
        );
        return $result;
    }
    function connectAuthorToPoster($author_id, $poster_id) {
        $result = insertQuery(
            "INSERT INTO author_to_poster (author_id, poster_id) VALUE (?, ?)",
            "ii", $author_id, $poster_id
        );
        return $result;
    }
    function removeAuthor($local_id, $poster_id) {
        $result = deleteQuery(
            "DELETE FROM author_to_poster
            WHERE author_to_poster.id=(
                SELECT id
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY id) AS local_id, id
                    FROM author_to_poster
                    WHERE author_to_poster.poster_id=?
                ) AS ranked_authors
                WHERE local_id=?
            )",
            "ii", $poster_id, $local_id
        );
        return $result;
    }
    function addAuthors($poster_id, $authors) {
	    $results = "";

	    for ($i = 0; $i < count($authors); $i++) {
		    $id = null;

		    $res = getterQuery(
			    "SELECT id FROM author WHERE name=?", $authors[$i]
		    )["id"];

		    if (count($res) == 0) {
			    $results .= "[" . addAuthor($authors[$i]);
			    $id = getLastInsertID();
		    } else {
			    $id = $res[0];
		    }

		    if(!connectAuthorToPoster($id, $poster_id)) {
			    return false;
		    }
	    }

	    return true;
    }

    function overwriteAuthors($poster_id, $authors) {
	    runQuery("START TRANSACTION");

	    if(!deleteQuery(
		    "DELETE FROM author_to_poster WHERE poster_id=?",
		    "i", $poster_id
	    )) {
		    runQuery("ROLLBACK");

		    return false;
	    }

	    if(addAuthors($poster_id, $authors)) {
		    runQuery("COMMIT");
	    } else {
		    runQuery("ROLLBACK");

		    return false;
	    }

	    return true;
    }

    function addProject($user_id, $title) {
        //new poster
        $resultA = insertQuery(
            "INSERT INTO poster (title, user_id) VALUE (?, ?)",
            "si", $title, $user_id
        );
        $poster_id = getLastInsertID();

        //new author (user)
        $resultB = insertQuery(
            "INSERT INTO author (name)
            SELECT (
                SELECT name FROM user WHERE user.user_id=?
            )
            WHERE NOT EXISTS (
                SELECT name
                FROM author
                WHERE author.name=(
                    SELECT name FROM user WHERE user.user_id=?
                )
            )",
            "ii", $user_id, $user_id
        );
        $author_id = getLastInsertID();

        //new author_to_poster
        $resultC = insertQuery(
            "INSERT INTO author_to_poster (author_id, poster_id) VALUE (?, ?)",
            "ii", $author_id, $poster_id
        );

        //new box
        // addBox($poster_id, "$$ Content $$");

        return $resultA . " " . $resultB . " " . $resultC;
    }

    function getVisibilityOptions() {
        $result = getterQuery(
            "SELECT name FROM view_modes"
        );
        return $result["name"];
    }
    function getVisibility($poster_id) {

        $result = getterQuery(
            "SELECT fk_view_mode
            FROM poster
            WHERE poster_id=?", $poster_id
        );
        return $result["fk_view_mode"][0];
    }
    function setVisibility($poster_id, $value) {
        $result = editQuery(
            "UPDATE poster SET poster.fk_view_mode = ?
            WHERE poster.poster_id = ?", "ii", $value+1, $poster_id
        );
        return $result;
    }

    function setViewMode2($poster_id, $view_id) {
        $result = editQuery(
            "UPDATE poster SET fk_view_mode = ?
            WHERE poster_id = ?",
            "ii", $view_id, $poster_id
        );
        return $result;
    }

    function fetchPublicPosters() {
        $result = getterQuery(
            "SELECT poster_id, title
            FROM poster
            WHERE fk_view_mode=? AND visible=?", 1, 1
        );
        return json_encode($result, true);
    }

    function isPublic($poster_id) {
        $result = getterQuery(
            "SELECT 1 AS is_public FROM poster WHERE poster_id=? AND fk_view_mode=? AND visible=?",
            $poster_id, 1, 1
        );
        return count($result["is_public"]) != 0 ? 1 : 0;
    }

    function load_content($poster_id) {
        $content = new stdClass();

        $content->title = null;
        $content->authors = array();
        $content->boxes = array();
        $content->visibility = null;
        $content->vis_options = getVisibilityOptions();

        $count = getterQuery("SELECT COUNT(poster_id) as cnt_poster_id FROM poster WHERE poster_id = ?", $poster_id);
        if($count["cnt_poster_id"][0] > 0) {
            $content->title = getTitle($poster_id);
            $content->authors = getAuthors($poster_id)["name"]; //direct from project
            // $content->all_authors = ...      //other authors the user once wrote in a project// TODO:   request list of all authors the user has a connection with
            $content->boxes = getBoxes($poster_id);
            $content->visibility = getVisibility($poster_id);
            $content->vis_options = getVisibilityOptions();
        }

        return json_encode($content);
    }

    // TODO: use for every poster change event
    // TODO: needs testing
    function hasPermissionToChange($user_id, $poster_id) {
        if (isAdmin($user_id) === true) {
            return true;
        }

        if (getterQuery("SELECT user_id FROM poster WHERE poster_id = ?", $poster_id)["user_id"][0] === $user_id) {
            return true;
        }

        return false;
    }
?>
