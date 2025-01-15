<?php
    // getterQuery: $stmt->close in exception damit nie offene statements bleiben, auch wenns fehlschlägt
    // getterQuery: nicht per default als json sondern als php datenstruktur zurückgeben; json when needed machen, meist ist es einfacher im code wenn man es als php datenstruktur hat

    $db_path = "/etc/dbpw";

    $password = null;

    if (file_exists($db_path)) {
        $password = file_get_contents($db_path);
        $password = chop($password);
    } else {
        die("$db_path not found!");
    }

    // Create connection
    $GLOBALS["conn"] = new mysqli("localhost", "poster_generator", $password);

    // Check connection
    if ($GLOBALS["conn"]->connect_error) {
        die("Connection failed: " . $GLOBALS["conn"]->connect_error);
    }

    function insertQuery($sql, $types, ...$param) {
        $stmt = $GLOBALS["conn"]->prepare($sql);
        $stmt->bind_param($types, ...$param);
        $stmt->execute();
        $stmt->close();

		return "success";
    }

    # todo: true/false zurückgeben
    function deleteQuery($sql, $types, ...$param) {
        $stmt = $GLOBALS["conn"]->prepare($sql);
        $stmt->bind_param($types, ...$param);
        $stmt->execute();
        $stmt->close();

		return "successfully deleted";
    }

    function getterQuery($sql, $target_values, $types, ...$param) {
        $stmt = $GLOBALS["conn"]->prepare($sql);
        $stmt->bind_param($types, ...$param);
        $stmt->execute();

        $result = $stmt->get_result();

        $output = "";

        if ($result->num_rows > 0) {
            $out = array();

            for ($i = 0; $i < count($target_values); $i++) {
                $out[$target_values[$i]] = array();
            }

            while ($row = $result->fetch_assoc()) {
                try {
                    for ($i = 0; $i < count($target_values); $i++) {

                        $out[$target_values[$i]][] = $row[$target_values[$i]];
                    }
                } catch (mysqli_sql_exception $th) {
		    #$stmt->close();
                    return "Error: " . $th->getMessage();
                }
            }
	    #$stmt->close();
            return json_encode($out);

        } else {
	    #$stmt->close();
            return "No results found";
        }
        $stmt->close();
    }

    // Old
    function runSingleQuery ($sql) {
        $result = $GLOBALS["conn"]->query($sql);
        $substr = explode(" ",$sql);

        if (!$result) {
            return "ERROR";
        }

        if ($result->num_rows > 0) {
            // Initialize an empty string to store the result
            $output = "";//"<ul>";

            // Fetch all rows and generate HTML output
            while ($row = $result->fetch_assoc()) {
                $output .= "<div>" . htmlspecialchars($row[$substr[1]]) . "</div>";
            }

            $output .= "";//"</ul>";
            return $output;
        } else {
            return "No results found";
        }
    }


    function getTitle($id) {
	    # prepared statement oder mysqli_real_escape_string
        return runSingleQuery("SELECT title FROM poster_generator.poster WHERE poster.poster_id=" . $id);
    }
?>
