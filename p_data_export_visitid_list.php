<?
    include("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $html_bind = array();
    $bind_param = "s";
    $array_val = array($sProjid);

    $query ="select DISTINCT visit_id from p_project_uid_visit where proj_id = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($visit_id);
        while($stmt->fetch()){
            $html_bind[$visit_id] = "<label for='".$visit_id."' class='h-15 holiday-ml-1'><input type='checkbox' id='".$visit_id."' class='visit-list-all' value='".$visit_id."' />".$visit_id."</label>";
        }
    }
    $stmt->close();
    $mysqli->close();

    foreach($html_bind as $key_visit => $val){
        echo $val;
    }
?>