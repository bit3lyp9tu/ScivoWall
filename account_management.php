<?php
    include_once("queries.php");

    function generate_salt($len=32) {
        $pr_bits = '';
        $fp = @fopen('/dev/urandom','rb');
        if ($fp !== FALSE) {
            $pr_bits .= @fread($fp, $len);
            @fclose($fp);
        }
        $pr_bits = unpack("C*", $pr_bits);
        $str = "";

        foreach ($pr_bits as $entry) {
            $str .= chr(33 + ($entry % 94));
        }
        echo substr($str, 0, $len);
    }

    if(isset($_POST['action'])) {

        if ($_POST['action'] == 'register') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $pw = isset($_POST['pw']) ? $_POST['pw'] : '';

            $salt = generate_salt();
            $pepper = "a2d47c981889513c5e2ddbca71f414"; //TODO: use pepper dependency
            $hash = md5($pw . ":" . $salt . ":" . $pepper);

            // echo $pw . ":" . $salt . ":" . $pepper . "----" . $hash;
            try {
                echo insertQuery("INSERT INTO poster_generator.user (`name`, `pass_sha`, `salt`, `pepper`)
                    VALUES (?, ?, ?, ?)",
                    "ssss", $name, $hash, $salt, $pepper);

            } catch (mysqli_sql_exception $th) {
                if ($th->getCode() == 1062) {
                    echo "The user " . $name . " already exists.";
                }
            }
        }

        if ($_POST['action'] == 'login') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $pw = isset($_POST['pw']) ? $_POST['pw'] : '';

            $result = getterQuery(
                "SELECT pass_sha, salt, pepper FROM poster_generator.user WHERE user.name=?",
                ["pass_sha", "salt", "pepper"],
                "s", $name);

            if ($result == "No results found") {
                echo "Wrong Password";
            }else {

                $res_dec = json_decode($result, true);

                $hash = $res_dec["pass_sha"][0];
                $salt = $res_dec["salt"][0];
                $pepper = $res_dec["pepper"][0];

                if (md5($pw . ":" . $salt . ":" . $pepper) == $hash) {
                    echo "Correct Password";
                }else {
                    echo "Wrong Password";
                }
            }
        }

        if ($_POST['action'] == 'fetch_all_projects') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';

            $user_id = getterQuery(
                "SELECT user_id FROM poster_generator.user WHERE user.name=?",
                ["user_id"],
                "s", $name
            );

            if ($user_id == "No results found") {
                echo "No results found";
            }else{
                $result = getterQuery(
                    "SELECT title FROM poster_generator.poster WHERE poster.user_id=?",
                    ["title"],
                    "s", json_decode($user_id, true)["user_id"][0]
                );
                echo $result;
            }
        }

        if ($_POST['action'] == 'delete_project') {
            $local_id = isset($_POST['local_id']) ? $_POST['local_id'] : '';
            $session_id = isset($_POST['session_id']) ? $_POST['session_id'] : '';  //currently unused
            //TODO: include session_id in deletion process for increased security

            $user_id = 2;

            $result = getterQuery(
                "SELECT poster_id
                FROM (
                    SELECT ROW_NUMBER() OVER (ORDER BY poster_id) AS local_id, poster_id
                    FROM poster_generator.poster
                    WHERE poster.user_id = ?
                ) AS ranked_posters
                WHERE local_id = ?",
                ["poster_id"],
                "ii", $user_id, $local_id
            );

            $res = deleteQuery(
                "DELETE FROM poster_generator.poster WHERE poster.poster_id = ?",
                "i", json_decode($result, true)["poster_id"][0]
            );

            //refresh list
            $data = getterQuery(
                "SELECT title FROM poster_generator.poster WHERE poster.user_id=?",
                ["title"],
                "s", $user_id
            );
            echo $data;
        }

        if ($_POST['action'] == 'create_project') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $user_name = isset($_POST['user_name']) ? $_POST['user_name'] : '';
            //TODO: include session_id in creation process for increased security

            $user_id = json_decode(getterQuery(
                "SELECT user_id FROM poster_generator.user WHERE user.name=?",
                ["user_id"],
                "s", $user_name
            ), true);

            if ($user_id != "") {
                $res = insertQuery(
                    "INSERT into poster_generator.poster (title, user_id) VALUE (?, ?)",
                    "si", $name, $user_id["user_id"][0]
                );

                //refresh list
                $data = getterQuery(
                    "SELECT title FROM poster_generator.poster WHERE poster.user_id=?",
                    ["title"],
                    "s", $user_id["user_id"][0]
                );
                echo $data;
            }else {
                echo "ERROR";
            }

        }
    }
?>
