<?php
    include_once(__DIR__ . "/" . "install.php");
    include_once(__DIR__ . "/" . "queries.php");

    $msgs = array(
        "success" => [],
        "warning" => [],
        "error" => []
    );

    function generate_salt($len=32) {
        return substr(bin2hex(random_bytes($len)), 0, $len);
    }

    function getPwComplexityLevel($pw) {
        $complexity_points = 0;

        $numbers = str_split("0123456789");
        $letters = "abcdefghijklmnopqrstuvwxyz";
        $lower_letters = str_split($letters);
        $upper_letters = str_split(strtoupper($letters));
        $special_char = str_split("_=!#$%&()*+-.:/\?@[]*$");

        if (strlen($pw) >= 8) {
            $complexity_points++;
        }
        if (!empty(array_intersect($numbers, str_split($pw)))) {
            $complexity_points++;
        }
        if (!empty(array_intersect($upper_letters, str_split($pw)))) {
            $complexity_points++;
        }
        if (!empty(array_intersect($special_char, str_split($pw)))) {
            $complexity_points++;
        }

        return $complexity_points;
    }

    //TODO:   move queries to own functions + tests
    //      make queries more compact
    /*
        *    Validates current session
        *    if session not valid, returns null
        *    if session valid, returns the corresponding user_id
    */
    function getValidUserFromSession() {

        if (!array_key_exists("sessionID", $_COOKIE)) {
            return null;
        }
        $sessionID = $_COOKIE["sessionID"];
        if ($sessionID == null) {
            return null;
        }

        $ret = getterQuery2(
            "SELECT user_id
            FROM session
            WHERE session.sessionID = ?",
            $sessionID
        );

        if (isset($ret["user_id"][0])) {
            return $ret["user_id"][0];
        }

        # TODO: making sure that this error never occurs
        error_log("WARNING: user_id not defined!");

	    return null;
    }

    //TODO:   make id not required
    function isAdmin($user_id) {
        // if ($user_id == null) {
        //     $user_id = getValidUserFromSession();
        // }
        $result = getterQuery2(
            "SELECT access_level FROM user WHERE user_id=?", $user_id
        )["access_level"];

        if (sizeof($result) > 0) {
            return $result[0] >= 2;
        }else{
            return "user_id does not exist";
        }
    }

    function register($name, $pw) {

        if (getPwComplexityLevel($pw) != 4) {
            return "Password not complex enough";
        }

        $salt = generate_salt();
        $pepper = "a2d47c981889513c5e2ddbca71f414"; //TODO:   use pepper dependency
        $hash = sha1($pw . ":" . $salt . ":" . $pepper);

        try {
            $is_first = 0;
            if(isEmpty() === 1) {
                $is_first = 1;
            }

            $result = insertQuery("INSERT INTO user (`name`, `pass_sha`, `salt`, `pepper`) VALUES (?, ?, ?, ?)", "ssss", $name, $hash, $salt, $pepper);

            $last_id = getLastInsertID();

            if($is_first == 1) {
                $r = editQuery("UPDATE user SET user.access_level=? WHERE user.user_id=?", "ii", 2, $last_id);
            }

            return $result;

        } catch (mysqli_sql_exception $th) {
            if ($th->getCode() == 1062) {
                return "The user " . $name . " already exists.";
            }else{
                return $th->getMessage();
            }
        }
    }

    function isEmpty() {
        if(getterQuery2("SELECT COUNT(name) AS n FROM user")["n"][0] === 0) {
            return 1;
        }else{
            return 0;
        }
    }

    function login($name, $pw) {

        # TODO:   select user_id from user where username = ? and passwort = sha(?);

        $result = getterQuery2(
            "SELECT user_id, pass_sha, salt, pepper FROM user WHERE user.name=?",
            $name
        );

        if (sizeof($result["user_id"]) == 0 &&
            sizeof($result["pass_sha"]) == 0 &&
            sizeof($result["salt"]) == 0 &&
            sizeof($result["pepper"]) == 0) {

            return "Wrong Username or Password";
        } else {
            $hash = $result["pass_sha"][0];
            $salt = $result["salt"][0];
            $pepper = $result["pepper"][0];

            $session_time_h = 4;

            if (md5($pw . ":" . $salt . ":" . $pepper) == $hash || sha1($pw . ":" . $salt . ":" . $pepper) == $hash) {  //TODO:   remove md5 when test-phase finished

                //Create new Session
                $user_id = $result["user_id"][0];
                $sid = session_create_id();

                $insertion = insertQuery(
                        "INSERT INTO session (user_id, sessionID, expiration_date)
                        VALUE (?, ?, UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL ? HOUR)))",
                        "isi", $user_id, $sid, $session_time_h);

                // TODO:   is expiration_date for sql and cookie async??? Bug?

                if ($insertion == "success") {
			$isCLI = (php_sapi_name() == 'cli');
			if(!$isCLI) {
				setcookie("sessionID", $sid, time() + $session_time_h * 60 * 60, "/", "", false, true);   //TODO:   placing additional time in variable
			}
                    //TODO:   do the cookie settings need a rework? Update to PHP 7.3 and later might be required
                    //PHP 7.3 and later has additional parameter SameSide=Strict and SameSide=Lax

                    return "Correct Password";
                }else {
                    return $insertion;
                }
            }else {
                return "Wrong Username or Password";
            }
        }

    }

    //TODO:   editing after logout bug
    function logout($user_id) {

        $sid = getterQuery2(
            "SELECT sessionID FROM session WHERE session.user_id=?",
            $user_id
        )["sessionID"][0];

        //set browser-session to expired
        setcookie("sessionID", $sid, time() - 1, "/", "", false, true);   //TODO:   placing additional time in variable

        //set database-session to expired
        //get list of all currently valid sessions (from user)
        //update list and replace expiration_date with an expired one
        $edit = editQuery(
            "UPDATE session
            SET session.expiration_date=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MINUTE))
            WHERE session.expiration_date>UNIX_TIMESTAMP() AND session.user_id=?",
            "i", $user_id
        );

        //return if logout successfully
        return $edit;
    }

    function solve_list($attribute, $rules) {

        // $field = array();
        // for ($i=0; $i < sizeof($rules["attributes"][$attribute]["list"]); $i++) {
        //     $field[] = '?';
        // }
        // return " " . $attribute . " IN (" . implode(', ', $field) . ") ";

        if (sizeof($rules["attributes"][$attribute]["list"]) > 0) {

            $list = array();
            for ($i=0; $i < sizeof($rules["attributes"][$attribute]["list"]); $i++) {
                $value =  $rules["attributes"][$attribute]["list"][$i];
                if (is_numeric($value)) {
                    $list[] = "" . $value . "";
                }else{
                    $list[] = "'" . $value . "'";
                }
            }
            return " " . $attribute . " IN (" . implode(",", $list) . ") ";
        } else {
            return "";
        }

    }

    function solve_min($attribute, $rules) {
        if ($rules["attributes"][$attribute]["min"] !== "") {
            return " " . $attribute . " >= " . $rules["attributes"][$attribute]["min"] . " ";
        }else{
            return "";
        }
    }

    function solve_max($attribute, $rules) {
        if ($rules["attributes"][$attribute]["max"] !== "") {
            return " " . $attribute . " <= " . $rules["attributes"][$attribute]["max"] . " ";
        }else{
            return "";
        }
    }

    function filter_projects($json) {

        $rules = json_decode($json, true);

        if (isset($rules["attributes"])) {

            if (sizeof(array_keys($rules["attributes"])) > 0) {

                $keys = array_keys($rules["attributes"]);
                $list = array();
                for ($i=0; $i < sizeof($keys); $i++) {
                    if (solve_min($keys[$i], $rules) !== "") {
                        $list[] = solve_min($keys[$i], $rules);
                    }
                    if (solve_max($keys[$i], $rules) !== "") {
                        $list[] = solve_max($keys[$i], $rules);
                    }
                    if (solve_list($keys[$i], $rules) !== "") {
                        $list[] = solve_list($keys[$i], $rules);
                    }
                }
                return " AND " . implode("AND", $list);
            }else{
                return "";
            }
        }else{
            return "";
        }
    }

    function sanitize_filter($sql) {
        $result = array();

        $inputs = array();
        $parts = explode("AND", $sql);
        for ($i=0; $i < sizeof($parts); $i++) {
            preg_match_all("/(?<=\').*?(?=\')/m", $parts[$i], $matches);

            for ($j=0; $j < sizeof($matches); $j++) {
                if (is_array($matches[$j])) {

                    for ($k=0; $k < sizeof($matches[$j]); $k++) {
                        if ($matches[$j][$k] !== "" && $matches[$j][$k] !== ",") {
                            $inputs[] = $matches[$j][$k];
                        }
                    }
                }else {
                    if ($matches[$j] !== "" && $matches[$j] !== ",") {
                        $inputs[] = $matches[$j];
                    }
                }
            }

            preg_match("/(?<=\=\s)\d+(.\d+)?(?=\s)/", $parts[$i], $matchesB);

            for ($j=0; $j < sizeof($matchesB); $j++) {
                if (substr($matchesB[$j], 0, 1) !== ".") {

                    $inputs[] = $matchesB[$j];
                }
            }
        }
        $result["var"] = $inputs;

        while (sizeof($inputs) > 0) {

            $start = strpos($sql, $inputs[0]);
            $end = strlen($inputs[0]);

            if(is_numeric($inputs[0])) {
                $sql = substr_replace($sql, '?', $start, $end);
            }else{
                $sql = substr_replace($sql, '?', $start - 1, $end + 2);
            }
            array_shift($inputs);
        }

        $result["sql"] = $sql;

        return $result;
    }

    function fetch_projects_all($user_id, $rules) {
        if ($user_id != null) {
            if (isAdmin($user_id)) {

                $filtered = filter_projects($rules);
                $sanitized = sanitize_filter($filtered);

                $sql =  (
                    "SELECT user.name, title, from_unixtime(last_edit_date) AS last_edit, visible, view_modes.name AS view_mode
                    FROM poster, view_modes, user
                    WHERE poster.fk_view_mode = view_modes.ID AND poster.user_id = user.user_id"
                ) . $sanitized["sql"];

                return json_encode(getterQuery2($sql, ...$sanitized["var"]), true);

            } else {
                return "Not Admin";
            }
        } else {
            return "No or invalid session";
        }
    }

    function getFilterSelectables($user_id) {
        if ($user_id != null) {
            if (isAdmin($user_id)) {

                $result = array();

                $result["user"] = getterQuery2("SELECT name FROM user");
                $result["title"] = getterQuery2("SELECT title FROM poster");
                $result["last_edit"]["min"] = 0;
                $result["last_edit"]["max"] = 2147483647;
                $result["visible"]["min"] = 0;
                $result["visible"]["max"] = 1;
                $result["view_mode"] = getterQuery2("SELECT name FROM view_modes");

                return json_encode($result, true);

            } else {
                return "Not Admin";
            }
        } else {
            return "No or invalid session";
        }
    }

    function fetch_projects($user_id, $priv_acc=true) {

        if ($user_id != null) {

            if ($user_id == "No results found") {
                return "No results found";
            }else{
                if (!$priv_acc && isAdmin($user_id)) {
                    return json_encode(getterQuery2(
                        "SELECT title, from_unixtime(last_edit_date) AS last_edit, visible, view_modes.name AS view_mode FROM poster, view_modes WHERE fk_view_mode=? AND poster.fk_view_mode = view_modes.ID",
                        1
                    ), true);
                }else{
                    return json_encode(getterQuery2(
                        // TODO: feature needs js support as well
                        "SELECT title, from_unixtime(last_edit_date) AS last_edit, visible, view_modes.name AS view_mode FROM poster, view_modes WHERE poster.user_id=? AND poster.fk_view_mode = view_modes.ID",
                        $user_id
                    ), true);
                }
            }

        }else{
            return "No or invalid session";
        }
    }

    function fetch_images($user_id) {
        if ($user_id) {
            return json_encode(getterQuery2(
                "SELECT 0 AS image_data, image.file_name AS name, from_unixtime(image.last_edit_date) AS last_edit, poster.title AS title
                FROM image
                INNER JOIN poster ON image.fk_poster=poster.poster_id
                WHERE poster.user_id=?",
                $user_id
            ), true);

        }else{
            return "No or invalid session";
        }
    }

    function fetch_img_data($user_id) {
        if ($user_id) {
            return json_encode(getterQuery2(
                "SELECT image.data AS image_data
                FROM image
                INNER JOIN poster ON image.fk_poster=poster.poster_id
                WHERE poster.user_id=?",
                $user_id
            ), true);

        }else{
            return "No or invalid session";
        }
    }

    function fetch_authors($user_id) {
        if ($user_id) {

            return json_encode(getterQuery2(
                "SELECT author.name AS name, poster.title AS title
                FROM author
                INNER JOIN author_to_poster ON author.id=author_to_poster.author_id
                INNER JOIN poster ON author_to_poster.poster_id=poster.poster_id
                WHERE poster.user_id=?",
                $user_id
            ), true);

        }else{
            return "No or invalid session";
        }
    }

    function create_project($name, $user_id) {

        if ($user_id != null) {

            if ($user_id != "") {
                $res = insertQuery(
                    "INSERT into poster (title, user_id) VALUE (?, ?)",
                    "si", $name, $user_id
                );

                //refresh list
                $data = getterQuery2(
                    "SELECT title, from_unixtime(last_edit_date) AS last_edit, visible, view_modes.name AS view_mode FROM poster, view_modes WHERE poster.user_id=? AND fk_view_mode=view_modes.ID;",
                    $user_id
                );
                return json_encode($data, false);
            }else {
                return "ERRORhvjjghgc";
            }

        }else{
            return "No or invalid session";
            #$msgs["errors"][] = "No or invalid session";
        }
    }

    function rename_poster($name, $local_id, $user_id) {
        if ($user_id != null) {

            $poster_id = getterQuery2(
                "SELECT poster_id
                FROM (
                    SELECT ROW_NUMBER() OVER (ORDER BY poster_id) AS local_id, poster_id
                    FROM poster
                    WHERE user_id=?
                ) AS ranked_posters
                WHERE local_id=?",
                $user_id, $local_id
            )["poster_id"][0];

            return editQuery(
                "UPDATE poster SET title=? WHERE poster_id=?",
                "si", $name, $poster_id
            );

        }else{
            return "No or invalid session";
        }
    }

    // TODO:   UNTESTED
    function getGlobalIDAuthor($local_id, $user_id) {
        if ($user_id != null) {

            return getterQuery2(
                "SELECT author_id
                FROM (
                    SELECT ROW_NUMBER() OVER (ORDER BY author_to_poster.id) AS local_id, author_to_poster.author_id
                    FROM author_to_poster
                    INNER JOIN poster ON author_to_poster.poster_id=poster.poster_id
                    WHERE user_id=?
                ) AS ranked_authors
                WHERE local_id=?",
                $user_id, $local_id
            )["author_id"][0];

        }else{
            return "No or invalid session";
        }
    }

    // TODO:   UNTESTED
    function getGlobalIDImage($local_id, $user_id) {
        if ($user_id != null) {

            return getterQuery2(
                "SELECT image_id
                FROM (
                    SELECT ROW_NUMBER() OVER (ORDER BY image_id) AS local_id, image_id
                    FROM image
                    INNER JOIN poster ON fk_poster=poster_id
                    WHERE user_id=?
                ) AS ranked_images
                WHERE local_id=?",
                $user_id, $local_id
            )["image_id"][0];

        }else{
            return "No or invalid session";
        }
    }

    // TODO:   UNTESTED
    function rename_author($name, $local_id, $user_id) {

        if ($user_id != null) {

            $id = getGlobalIDAuthor($local_id, $user_id);
            if (is_numeric($id) && $id > 0) {

                return editQuery(
                    "UPDATE author SET name=? WHERE id=?",
                    "si", $name, $id
                );

            }else{
                return "No or invalid id:" . $id;
            }
        }else{
            return "No or invalid session";
        }
    }

    // TODO:   UNTESTED
    function delete_author($local_id, $user_id) {

        if ($user_id != null) {

            $id = getGlobalIDAuthor($local_id, $user_id);
            if (is_numeric($id) && $id > 0) {
                $res = deleteQuery(
                    "DELETE FROM author WHERE id=?",
                    "i", $id
                );

                $res2 = deleteQuery(
                    "DELETE FROM author_to_poster WHERE author_id=?",
                    "i", $id
                );

                return $res . " " . $res2;

            }else{
                return "No or invalid id:" . $id;
            }
        }else{
            return "No or invalid session";
        }
    }

    function rename_image($name, $local_id, $user_id) {
        if ($user_id != null) {

            $id = getGlobalIDImage($local_id, $user_id);
            if (is_numeric($id) && $id > 0) {
                $res = editQuery(
                    "UPDATE image SET file_name=? WHERE image_id=?",
                    $name, $id
                );

                return $res;
            }else{
                return "No or invalid id:" . $id;
            }
        }else{
            return "No or invalid session";
        }
    }

    // TODO:   UNTESTED
    function delete_image($local_id, $user_id) {

        if ($user_id != null) {

            $id = getGlobalIDImage($local_id, $user_id);
            if (is_numeric($id) && $id > 0) {
                return deleteQuery(
                    "DELETE FROM image WHERE image_id=?",
                    "i", $id
                );
            }else{
                return "No or invalid id:" . $id;
            }
        }else{
            return "No or invalid session";
        }
    }

    function delete_project($local_id, $user_id) {

        if ($user_id != null) {

            # evtl aus der ranked_posters view herausholen statt aus der subquery
            $poster_id = getterQuery2(
                "SELECT poster_id
                FROM (
                    SELECT ROW_NUMBER() OVER (ORDER BY poster_id) AS local_id, poster_id
                    FROM poster
                    WHERE poster.user_id = ?
                ) AS ranked_posters
                WHERE local_id = ?",
                $user_id, $local_id
            )["poster_id"][0];

            //delete all images of poster
            $res = deleteQuery(
                "DELETE FROM image WHERE fk_poster=?",
                "i", $poster_id
            );

            $res .= deleteQuery(
                "DELETE FROM poster WHERE poster_id=?",
                "i", $poster_id
            );

            /*
            if(!$res) {
                $msgs["error"][] = "deleteQuery failed...";
            }
            */
            // res  checken: wenn deleteQuery true/false (boolean) zurückgibt chekcen, also fehlerbehandlung
            //refresh list
            $data = getterQuery2(
                "SELECT title, from_unixtime(last_edit_date) AS last_edit, visible, view_modes.name AS view_mode FROM poster, view_modes WHERE poster.user_id=? AND fk_view_mode=view_modes.ID",
                $user_id
            );
            return json_encode($data, true);
            # TODO:   nach jedem ausgeben von json exit 0, damit nicht ausversehen 2 oder mehr jsons konkatiniert werden
            # exit(0);
        }else{
            return "No or invalid session";
        }
    }

    function updateVisibility($id, $value) {
        $result = editQuery(
            "UPDATE poster SET visible=?
            WHERE poster_id=(
                SELECT poster_id FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY poster_id) AS local_id
                FROM poster
                WHERE fk_view_mode = ?
            ) AS inner_query
            WHERE local_id = ?)", "iii", $value, 1, $id
        );
        return $result;
    }

    if(isset($_POST['action'])) {
        # evtl noch checken dass der name mindestens 3 zeichen hat (oder so)
        if ($_POST['action'] == 'register') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $pw = isset($_POST['pw']) ? $_POST['pw'] : '';

            echo register($name, $pw);
        }

        if ($_POST['action'] == 'login') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $pw = isset($_POST['pw']) ? $_POST['pw'] : '';

            echo login($name, $pw);
        }

        if ($_POST['action'] == 'fetch_all_projects') {

            $user_id = getValidUserFromSession();
            echo fetch_projects($user_id, false);
        }

        if ($_POST['action'] == 'selectable_filters') {

            $user_id = getValidUserFromSession();
            echo getFilterSelectables($user_id);
        }

        if ($_POST['action'] == 'fetch_filtered_projects') {

            $filter = isset($_POST['filter']) ? $_POST['filter'] : '';
            $user_id = getValidUserFromSession();

            echo fetch_projects_all($user_id, $filter);
        }

        if ($_POST['action'] == 'fetch_authors') {

            $user_id = getValidUserFromSession();
            echo fetch_authors($user_id);
        }

        if ($_POST['action'] == 'rename-author') {

            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $local_id = isset($_POST['id']) ? $_POST['id'] : 0;
            $user_id = getValidUserFromSession();

            echo rename_author($name, $local_id, $user_id);
        }

        if ($_POST['action'] == 'delete-author') {

            $local_id = isset($_POST['id']) ? $_POST['id'] : 0;
            $user_id = getValidUserFromSession();

            echo delete_author($local_id, $user_id);
        }

        if ($_POST['action'] == 'fetch_images') {

            $user_id = getValidUserFromSession();
            echo fetch_images($user_id);
        }

        if ($_POST['action'] == 'delete-image') {

            $local_id = isset($_POST['id']) ? $_POST['id'] : 0;
            $user_id = getValidUserFromSession();

            echo delete_image($local_id, $user_id);
        }

        if ($_POST['action'] == 'fetch_img_data') {

            $user_id = getValidUserFromSession();
            echo fetch_img_data($user_id);
        }

        if ($_POST['action'] == 'delete_project') {
            $local_id = isset($_POST['local_id']) ? $_POST['local_id'] : '';

            $user_id = getValidUserFromSession();
            echo delete_project($local_id, $user_id);
        }

        if ($_POST['action'] == 'create_project') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';

            $user_id = getValidUserFromSession();
            echo create_project($name, $user_id);
        }

        if ($_POST['action'] == 'rename_poster') {

            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $id = isset($_POST['id']) ? $_POST['id'] : 0;
            $user_id = getValidUserFromSession();

            echo rename_poster($name, $id, $user_id);
        }

        if ($_POST['action'] == 'logout') {

            $user_id = getValidUserFromSession();

            if($user_id != null) {

                echo logout($user_id);
            }else{
                echo "logout error";
            }
        }

        if ($_POST['action'] == 'edit-translation') {
            $local_id = isset($_POST['local_id']) ? $_POST['local_id'] : '';
            $user_id = getValidUserFromSession();

            if ($user_id != null) {
                $poster_id = getterQuery2(
                        "SELECT poster_id
                    FROM (
                        SELECT ROW_NUMBER() OVER (ORDER BY poster_id) AS local_id, poster_id
                        FROM poster
                        WHERE poster.user_id = ?
                    ) AS ranked_posters
                    WHERE local_id = ?",
                    $user_id, $local_id
                )["poster_id"][0];

                echo $poster_id;    //"success: local_id: " . $local_id . " poster_id: " . $poster_id . " user_id: " . $user_id;
            }else {
                echo "ERROR";
            }

        }

        if ($_POST['action'] == 'is-admin') {
            $user_id = getValidUserFromSession();

            if ($user_id != null) {

                echo isAdmin($user_id);
            }else{
                echo false;
            }
        }

        if ($_POST['action'] == 'update-visibility') {
            $local_id = isset($_POST['id']) ? $_POST['id'] : '';
            $value = isset($_POST['value']) ? $_POST['value'] : '';

            $user_id = getValidUserFromSession();

            // echo "test";//$local_id . " " . $value . " " . $user_id;

            if ($user_id != null && isAdmin($user_id)) {

                echo $value . " " . updateVisibility($local_id, $value);
            }else{
                echo "User not an Admin";
            }
        }

        if($_POST['action'] == 'has-valid-user-session') {

            echo getValidUserFromSession() != null;
        }

	    #print(json_encode($msgs));
    }
?>
