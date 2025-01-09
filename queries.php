<?php
    function getTitle ( $id ) {
        $db_path = "/etc/dbpw";

        $password = null;

        if (file_exists($db_path)) {
            $password = file_get_contents($db_path);
            $password = chop($password);
        } else {
            die("$db_path not found!");
        }

        $servername = "localhost";
        $username = "poster_generator";

        // Create connection
        $conn = new mysqli($servername, $username, $password);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }


        $sql = "SELECT title FROM poster_generator.poster where poster.poster_id=" . $id;
        $result = $conn->query($sql);

        if (!$result) {
            return "ERROR";
        }

        if ($result->num_rows > 0) {
            // Initialize an empty string to store the result
            $output = "<ul>";

            // Fetch all rows and generate HTML output
            while ($row = $result->fetch_assoc()) {
                $output .= "<li>" . htmlspecialchars($row['title']) . "</li>";
            }

            $output .= "</ul>";
            return $output;
        } else {
            return "No results found";
        }
    }
?>
