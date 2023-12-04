<?
include_once("in_php_function.php");

if (isset($_FILES['photoFile'])) {
    // Example:
    move_uploaded_file($_FILES['photoFile']['tmp_name'], "projimg/" . $_FILES['photoFile']['name']);
    echo 'successful';
}

?>