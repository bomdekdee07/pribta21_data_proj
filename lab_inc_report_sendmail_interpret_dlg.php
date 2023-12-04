<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $upd_date = getQS("upd_date");
    // echo $uid."/".$coldate."/".$coltime."/".$upd_date;

    $bind_param = "ssss";
    $array_val = array($uid, $coldate, $coltime, $upd_date);
    $interpret_txt = "";

    $query = "SELECT interpret_txt
    from log_send_email_lab_result 
    where uid = ?
    and collect_date = ?
    and collect_time = ?
    and upd_date = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $interpret_txt .= $row["interpret_txt"];
        }
    }
    $stmt->close();
    $mysqli->close();

    $js_response = "";
    $js_response .= '$("#interpret_txt_dlg [name=interpret_txt]").val('.json_encode($interpret_txt).');';
?>

<div class="fl-wrap-col" id="interpret_txt_dlg">
    <div class="fl-wrap-row">
        <div class="fl-fill font-s-2">
            <textarea name="interpret_txt" style="width: 100%; height: 100%;"></textarea>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        <? echo $js_response; ?>
    });
</script>