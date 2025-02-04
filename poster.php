<?php
    $site_script="poster.js";

    include("account_management.php");
    include("header.html");

    include("poster_edit.php")
?>
<!DOCTYPE html>
<html lang='en'>
<body>
    <div>
        <p><?php //echo validation($_GET['id']); ?></p>

        <h1>Poster: <?php echo getTitle($_GET['id']); ?></h1>
        <h2>Authors: <?php foreach(getAuthors($_GET['id']) as $value) {echo $value . " ";}?></h2>
        <br>
        <?php
            $content = getBoxes($_GET['id']);
            foreach ($content as $value) {
                echo $value . "<br>";
            }
        ?>
    </div>
</body>
</html>
