<?
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    
    $bind_param = "sss";
    $array_val = array($uid, $coldate, $coltime);

    $query = "SELECT data_result
    from p_data_result 
    where uid = ?
    and collect_date = ?
    and collect_time = ?
    and collect_time != '00:00:00'
    and data_id = 'cn_plan'
    and data_result != '';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    $html_bind = "";
    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $html_bind .= '$("#next_plan_view #cn_plan_view").val('.json_encode($row["data_result"]).')';
        }
    }
    $stmt->close();
    $mysqli->close();
?>

<div class="fl-wrap-col w-300" id="next_plan_view">
    <div class="fl-wrap-row h-10"></div>
    <div class="fl-wrap-row h-200 font-s-2">
        <textarea id="cn_plan_view" style="min-height: 199px; max-height: 199px; min-width: 299px; max-width: 299px;"></textarea>
    </div>
</div>

<script>
    $(document).ready(function(){
        <? echo $html_bind; ?>
    });
</script>