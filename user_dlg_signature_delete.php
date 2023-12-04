<?
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $path_file = getQS("path");

    if($path_file != ""){
        if(file_exists($path_file)){
            unlink($path_file);
            return 1;
        }
        else{
            return 2;
        }
    }
?>