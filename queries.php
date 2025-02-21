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
        // $stmt->set_charset('utf8mb4');
        $stmt->bind_param($types, ...$param);
        $stmt->execute();
        $stmt->close();

		return "success";
    }

    function editQuery($sql, $types, ...$param) {   //TODO: ??????????????????
        $stmt = $GLOBALS["conn"]->prepare($sql);
        $stmt->bind_param($types, ...$param);
        $stmt->execute();
        $stmt->close();

		return "successfully updated";
    }

    # TODO: true/false zurückgeben
    function deleteQuery($sql, $types, ...$param) {
        $stmt = $GLOBALS["conn"]->prepare($sql);
        $stmt->bind_param($types, ...$param);
        $stmt->execute();
        $stmt->close();

		return "successfully deleted";
    }

    function getterQuery($sql, $target_values, $types, ...$param) {
        $stmt = $GLOBALS["conn"]->prepare($sql);
        if ($types != "") {
            $stmt->bind_param($types, ...$param);
        }
        $stmt->execute();

        $result = $stmt->get_result();

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

    function simpleQuery($sql) {
        // $stmt = $GLOBALS["conn"]->prepare($sql);
        // $stmt->execute();
        // $stmt->close();

        return "";
    }

    // Old
    function runSingleQuery ($sql, $allows_num_rows=true) {
	    $result = $GLOBALS["conn"]->query($sql);
	    $substr = explode(" ",$sql);

	    if (!$result) {
		    return "ERROR";
	    }

	    if($allows_num_rows) {
		    if ($result->num_rows > 0) {
			    // Initialize an empty string to store the result
			    $output = "";

			    // Fetch all rows and generate HTML output
			    while ($row = $result->fetch_assoc()) {
				    $output .= "<div>" . htmlspecialchars($row[$substr[1]]) . "</div>";
			    }

			    $output .= "";
			    return $output;
		    } else {
			    return "No results found";
		    }
	    }
    }


    // function getTitle($id) {
	//     # prepared statement oder mysqli_real_escape_string
    //     return runSingleQuery("SELECT title FROM poster WHERE poster.poster_id=" . $id);
    // }
?>
