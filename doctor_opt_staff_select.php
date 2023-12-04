<?
    include("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    if($data_result_staff == ""){
        $data_result_staff = "";
    }
    if($data_id == null){
        $data_id = "";
    }

    // echo "data_result: ".$data_result_staff;

    if($data_result_staff == "")
        $sopt = "<option value='' selected>-- Please Select --</option>";
    else
        $sopt = "<option value=''>-- Please Select --</option>";

    $query = "SELECT data_name_th, 
        data_value, 
        s_name
    from p_data_sub_list data_list
    left join p_staff staff on(staff.s_id = data_list.data_value) 
    where data_id = ?
    and staff.s_status = '1';";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $data_id);

    // echo "sid:".$sSID."/".$data_result_staff."/".$data_id;

    $condition_val = "";
    if($stmt -> execute()){
        $stmt -> bind_result($data_name_th, $data_value, $s_name);
        while($stmt -> fetch()){
            if($data_value == "OTH"){
                $s_name = "OTH";
            }
            else if($data_value == "-"){
                $s_name = "-";
            }

            if($sSID != "" &&  $data_result_staff == ""){
                $sopt .= "<option value=".$data_value.">".$s_name."</option>";
                $condition_val = "1";
            }
            else if($sSID != "" &&  $data_result_staff != "" && $sSID == $data_result_staff){
                $condition_val = "2";
                $sopt .= "<option value=".$data_value." data-id=".$data_id." ".(($data_value == $data_result_staff)?"selected":"").">".$s_name."</option>";
            }
            else{
                $condition_val = "3";
                $sopt .= "<option value=".$data_value." data-id=".$data_id." ".(($data_value == $data_result_staff)?"selected":"").">".$s_name."</option>";
            }
        }
    }
    $stmt -> close();
    $mysqli -> close();

    echo $sopt;
?>