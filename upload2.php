<?php
    if (isset($_POST['submit'])) {
        // Define target directory
        $target_dir = "uploads/";  // Make sure this folder is writable (chmod 755 or 777 depending on your server)
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an actual image or fake image
        if (isset($_POST["submit"])) {
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                echo "File is an image - " . $check["mime"] . ".<br>";
                $uploadOk = 1;
            } else {
                echo "File is not an image.<br>";
                $uploadOk = 0;
            }
        }

        print_r($_FILES);

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.<br>";
            $uploadOk = 0;
        }

        // Check file size (5MB in this example)
        if ($_FILES["image"]["size"] > 5000000) {  // Adjust the size as necessary
            echo "Sorry, your file is too large.<br>";
            $uploadOk = 0;
        }

        // Allow certain file formats (JPG, PNG, GIF)
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.<br>";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 due to an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.<br>";
        } else {
            // Try to upload the file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                echo "The file " . htmlspecialchars(basename($_FILES["image"]["name"])) . " has been uploaded.<br>";
            } else {
                echo "Sorry, there was an error uploading your file.<br>";
            }
        }
    }
?>
