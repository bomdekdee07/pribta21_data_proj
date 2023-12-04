<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $type_code = getQS("type_code");

    $data_group_type = array();
    $query = "select supply_group_code, supply_group_name 
    from i_stock_group 
    where supply_group_type = ?";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $type_code);
    if($stmt->execute()){
        $stmt->bind_result($supply_group_code, $supply_group_name);
        while ($stmt->fetch()) {
            $data_group_type[$supply_group_code]["code"] = $supply_group_code;
            $data_group_type[$supply_group_code]["name"] = $supply_group_name;
        }
        // print_r($data_group_type);
    }
    $stmt->close();
    $mysqli->close();

    $stJsFilter = "";
    $stJsFilter .=  '<div class="fl-wrap-row h-30 fs-large">
                        <div class="fl-fix w-5"></div>
                        <div class="fl-fix w-55 fl-mid-left fw-b">Group:</div>
                        <div class="fl-fill fl-mid">
                            <select id="ddGroupSupFilter" style="min-width: 230px; max-width: 230px;">
                                <option value="">---All---</option>';
    foreach($data_group_type as $key => $val){
        $stJsFilter .= '<option value="'.$val["code"].'">'.$val["name"].'</option>';
    }
    $stJsFilter .=          '</select>
                        </div>
                    </div>';

    echo $stJsFilter;
?>

