<?
    include("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $gUid = getQS("uid");
    $gColdate = getQS("coldate");

    $bind_param2 = "ss";
    $array_val2 = array($gUid, $gColdate);
    $data_coltime = "0";

    $query_2 = "SELECT collect_time from i_queue_list where uid = ? and collect_date = ?;";
    $stmt_2 = $mysqli->prepare($query_2);
    $stmt_2->bind_param($bind_param2, ...$array_val2);

    if($stmt_2->execute()){
      $result = $stmt_2->get_result();
      while($row = $result->fetch_assoc()){
        $data_coltime = $row["collect_time"];
      }
    //   print_r($array_val2);
    }
    $stmt_2->close();
    $mysqli->close();

    echo $data_coltime;
?>
