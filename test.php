<?php
    include("queries.php");
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <title>Poster Generator</title>
    <meta charset='utf-8'>

    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

    <!-- <script src="https://code.jquery.com/jquery.min.js"></script> -->
    <!-- <script src="https://code.jquery.com/typeahead.bundle.min.js"></script> -->

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"> -->
    <!-- <link rel='stylesheet' type='text/css' href=style.css> -->
    <script src="poster.js"></script>
</head>

<body>
    Page

    <div id="drop-zone" style="width: 300px; height: 300px; border: 2px dashed #ccc; text-align: center; line-height: 300px;">
        Drop your image here
    </div>

    <div id="preview">
        <img id="preview-img" src="" alt="Preview" style="max-width: 100%; max-height: 100%; display: none;">
    </div>

</body>

<script>
    // function imageUpload(data) {
    //     $.ajax({
    //         type: "POST",
    //         url: "poster_edit.php",
    //         data: {
    //             action: "image-upload",
    //             data: data
    //         },
    //         success: function (response) {
    //             console.log(response);
    //         },
    //         error: function (error) {
    //             console.error(error);
    //         }
    //     });
    // }

    const dropZone = document.getElementById('drop-zone');
    const previewImg = document.getElementById('preview-img');

    // Handle dragover and drop events
    dropZone.addEventListener('dragover', function(event) {
        event.preventDefault();
    });

    dropZone.addEventListener('dragleave', function() {
        dropZone.style.backgroundColor = '';
    });

    dropZone.addEventListener('drop', async function(event) {
        event.preventDefault();
        dropZone.style.backgroundColor = '';

        const files = event.dataTransfer.files;
        console.log(files[0]);

        if (files.length > 0) {
            const file = files[0];
            const reader = new FileReader();

            reader.onload = async function(e) {
                // This is the base64 content of the image
                const imageContent = e.target.result;
                console.log(imageContent);  // The image content in base64 format

                console.log("Save Image...");
                const data = {
                    "name": file['name']
                    // "type": file.type,
                    // "size": file.size,
                    // "last_modified": file.lastModified,
                    // "webkit_relative_path": file.webkitRelativePath,
                    // "data": "..."
                };
                console.log(data);
                // await imageUpload(data);

                // Display the image in the preview area
                previewImg.src = imageContent;
                previewImg.style.display = 'block';
            };

            reader.readAsDataURL(file); // Read the file as base64
        }
    });

</script>

</html>
