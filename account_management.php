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
            $substr = explode(" ", $result);

            $hash = $substr[0];
            $salt = $substr[1];
            $pepper = $substr[2];

            if (md5($pw . ":" . $salt . ":" . $pepper) == $hash) {
                echo "Correct Password";
            }else {
                echo "Wrong Password";
            }
        }
    }
?>
