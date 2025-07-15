<?php
    include_once(__DIR__ . "/" . "account_management.php");
    include_once(__DIR__ . "/" . "poster_edit.php");

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

        if ($_POST['action'] == 'selectable_filters') {

            $user_id = getValidUserFromSession();
            echo getFilterSelectables($user_id);
        }

        if ($_POST['action'] == 'fetch_filtered_projects') {

            $filter = isset($_POST['filter']) ? $_POST['filter'] : '';
            $user_id = getValidUserFromSession();

            if ($user_id != null) {
                if (!isAdmin($user_id)) {
                    $filter = user_to_filter($user_id);
                }

                echo fetch_projects_all($user_id, $filter);
            } else {
                echo "No or invalid session";
            }
        }

        if ($_POST['action'] == 'fetch_filtered_authors') {

            $filter = isset($_POST['filter']) ? $_POST['filter'] : '';
            $user_id = getValidUserFromSession();

            if ($user_id != null) {
                if (!isAdmin($user_id)) {
                    $filter = user_to_filter($user_id);
                }

                echo fetch_authors_all($user_id, $filter);
            } else {
                echo "No or invalid session";
            }
        }

        if ($_POST['action'] == 'fetch_author_collection') {

            $user_id = getValidUserFromSession();

            if ($user_id != null) {
                if (isAdmin($user_id)) {
                    $filter = '{"attributes":{"user.name":{"min":"","max":"","list":[]},"poster.title":{"min":"","max":"","list":[]},"last_edit_date":{"min":"","max":"","list":[]},"visible":{"min":"","max":"","list":[]},"view_modes.name":{"min":"","max":"","list":[]}}}';
                } else {
                    $filter = user_to_filter($user_id);
                }

                echo fetch_authors_all($user_id, $filter);
            } else {
                echo "No or invalid session";
            }
        }

        if ($_POST['action'] == 'fetch_filtered_images') {

            $filter = isset($_POST['filter']) ? $_POST['filter'] : '';
            $user_id = getValidUserFromSession();

            if ($user_id != null) {
                if (!isAdmin($user_id)) {
                    $filter = user_to_filter($user_id);
                }

                echo fetch_images_all($filter);
            } else {
                echo "No or invalid session";
            }
        }

        if ($_POST['action'] == 'fetch_img_data') {

            $filter = isset($_POST['filter']) ? $_POST['filter'] : '';
            $user_id = getValidUserFromSession();

            if ($user_id != null) {
                if (!isAdmin($user_id)) {
                    $filter = user_to_filter($user_id);
                }

                echo fetch_img_data($user_id);
            } else {
                echo "No or invalid session";
            }
        }

        if ($_POST['action'] == 'rename-author') {

            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $id = isset($_POST['id']) ? $_POST['id'] : 0;
            $user_id = getValidUserFromSession();

            echo rename_author($name, $id, $user_id);
        }

        if ($_POST['action'] == 'rename-image') {

            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $id = isset($_POST['id']) ? $_POST['id'] : 0;
            $user_id = getValidUserFromSession();

            updateEditDate2("image", $id);
            echo rename_image($name, $id, $user_id);
        }

        if ($_POST['action'] == 'delete-author') {

            $id = isset($_POST['id']) ? $_POST['id'] : 0;
            $user_id = getValidUserFromSession();

            echo delete_author($id, $user_id);
        }

        if ($_POST['action'] == 'delete-image') {

            $id = isset($_POST['id']) ? $_POST['id'] : 0;
            $user_id = getValidUserFromSession();

            echo delete_image($id, $user_id);
        }

        if ($_POST['action'] == 'delete_project') {
            $id = isset($_POST['id']) ? $_POST['id'] : '';
            $user_id = getValidUserFromSession();

            echo delete_project_advanced($id);
        }

        if ($_POST['action'] == 'create_project') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';

            $user_id = getValidUserFromSession();
            echo create_project($name, $user_id);
        }

        if ($_POST['action'] == 'rename_poster') {

            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $id = isset($_POST['id']) ? $_POST['id'] : '';

            $user_id = getValidUserFromSession();
            updateEditDate2("poster", $id);
            echo rename_poster2($name, $id, $user_id);
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
                $poster_id = getterQuery(
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
            $id = isset($_POST['id']) ? $_POST['id'] : '';
            $value = isset($_POST['value']) ? $_POST['value'] : '';

            $user_id = getValidUserFromSession();

            // echo "test";//$local_id . " " . $value . " " . $user_id;

            if ($user_id != null && isAdmin($user_id)) {
                updateEditDate2("poster", $id);
                echo $value . " " . updateVisibility2($id, $value);
            }else{
                echo "User not an Admin";
            }
        }

        if($_POST['action'] == 'has-valid-user-session') {

            echo getValidUserFromSession() != null;
        }



        if ($_POST['action'] == 'get-content') {

            $poster_id = (isset($_POST['key']) && $_POST['key']=='id' && isset($_POST['value'])) ? $_POST['value'] : '';
            $user_id = getValidUserFromSession();

            if ($user_id != null) {

                echo load_content($poster_id);
            }else{

                echo json_encode(array('status' => 'error', 'message' => 'Invalid user'));
            }
        }
        if ($_POST['action'] == 'content-upload') {
            //TODO:   check if mode=private + session-id correct

            $data = json_decode((isset($_POST['data']) ? $_POST['data'] : ''), true);

            $poster_id = isset($_POST['id']) ? $_POST['id'] : '';
            $user_id = getValidUserFromSession();

            $mode = isset($_POST['mode']) ? $_POST['mode'] : '';

            if ($user_id != null/* && $mode == 'edit'*/) {

                if (hasPermissionToChange($user_id, $poster_id) === true) {

                    $title = $data["title"];
                    $authors = $data["authors"];
                    $content = $data["content"];
                    $visibility = $data["visibility"];

                    setTitle($poster_id, $title);
                    updateEditDate2("poster", $poster_id);
                    // addAuthors($poster_id, $authors);
                    overwriteAuthors($poster_id, $authors);
                    overwriteBoxes($poster_id, $content);
                    setVisibility($poster_id, $visibility);

                    echo "success?";
                } else {
                    echo "Insufficient permission";
                }

            }else{
                echo "ERROR";
            }
        }
        if($_POST['action'] == 'fetch-available-posters') {

            echo fetchPublicPosters();
        }
        if ($_POST['action'] == 'image-upload') {
            $data = isset($_POST['data']) ? $_POST['data'] : '';

            //TODO:   check if user has edit permissions for poster
            $poster_id = isset($_POST['id']) ? $_POST['id'] : '';

            echo addImage($data, $poster_id);
        }
        if($_POST['action'] == 'get-image') {
            // $image_id = isset($_POST['id']) ? $_POST['id'] : '';
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $poster_id = isset($_POST['poster_id']) ? $_POST['poster_id'] : '';

            // echo getImage(169);
            echo getFullImage($name, $poster_id);
        }

        if($_POST['action'] == 'list-view-options') {

            $user_id = getValidUserFromSession();
            if ($user_id != null) {
                echo json_encode(getVisibilityOptions());
            }else{
                echo "No or invalid session";
            }
        }

        if($_POST['action'] == 'set-view-option') {

            $poster_id = isset($_POST['poster_id']) ? $_POST['poster_id'] : '';
            $view_option = isset($_POST['view_id']) ? $_POST['view_id'] : '';

            $user_id = getValidUserFromSession();
            if ($user_id != null) {

                if (hasPermissionToChange($user_id, $poster_id) === true) {
                    updateEditDate2("poster", $poster_id);
                    echo setViewMode2($poster_id, $view_option);
                }else{
                    echo "Insufficient permission";
                }

            }else{
                echo "No or invalid session";
            }
        }
    }
?>
