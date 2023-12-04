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

    $sopt = "<option value=''>-- Please Select --</option>";

    $query = "select distinct m.s_name, s.s_id from p_staff m
    left join i_staff_clinic s on (m.s_id = s.s_id)
    where s.section_id in ('D05', 'D06');";

    $stmt = $mysqli -> prepare($query);
    // echo "sid:".$sSID."/".$data_result_staff."/".$data_id;

    if($stmt -> execute()){
        $stmt -> bind_result($data_name_th, $data_value);
        while($stmt -> fetch()){
            if($sSID != "" &&  $data_result_staff == ""){
                $sopt .= "<option value=".$data_value." data-id=".$data_id." ".(($data_value == $sSID)?"selected":"").">".$data_name_th."</option>";
            }
            else if($sSID != "" &&  $data_result_staff != "" && $sSID == $data_result_staff){
                $sopt .= "<option value=".$data_value." data-id=".$data_id." ".(($data_value == $data_result_staff)?"selected":"").">".$data_name_th."</option>";
            }
            else{
                $sopt .= "<option value=".$data_value." data-id=".$data_id." ".(($data_value == $data_result_staff)?"selected":"").">".$data_name_th."</option>";
            }
        }
    }

    $stmt -> close();
    $mysqli -> close();

    echo $sopt;
?>