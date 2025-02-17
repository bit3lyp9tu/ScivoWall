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
    <script src="https://code.jquery.com/typeahead.bundle.min.js"></script>

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"> -->
    <!-- <link rel='stylesheet' type='text/css' href=style.css> -->



</head>

<body>
    Page

    <input type="text" class="form-control tag-input" name="example" id="example" placeholder="Enter tags" value="">
</body>

<script>

    import { ITag, TagInput } from './src/lib';
    import './src/css/standard.css';

    // pre-defined tags
    const stringData = [
        'dog',
        'cat',
        'fish',
        'catfish',
        'dogfish',
        'bat'
    ];

    const instance = new TagInput({
        input: document.getElementById('example'),
        data: stringData
    });

</script>

</html>
