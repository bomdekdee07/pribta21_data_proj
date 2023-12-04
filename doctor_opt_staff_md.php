<?
    include("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");
    // echo "Test:".$$data_result_staff; //GET from main include.

    if($data_result_staff == ""){
        $data_result_staff = "";
    }
    if($data_id == null){
        $data_id = "";
    }

    if((isset($sClinicID)? $sClinicID : "") == ""){
        $sClinicID = (isset($clinic_id)? $clinic_id : "");
    }

    $sopt = "<option value=''>-- Please Select --</option>";

    // echo "TEST2:".$not_QS_sid.":".$sSID;
    if($not_QS_sid != false){
        $query = "select distinct m.s_name, s.s_id from p_staff m
        left join i_staff_clinic s on (m.s_id = s.s_id)
        where s.section_id in ('D05', 'D06')
        and s.clinic_id = ?
        and s.s_id = ?;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("ss", $sClinicID, $sSID);
        
    }else{
        $query = "select distinct m.s_name, s.s_id from p_staff m
        left join i_staff_clinic s on (m.s_id = s.s_id)
        where s.section_id in ('D05', 'D06')
        and s.clinic_id = ?;";

        $stmt = $mysqli -> prepare($query);
        $stmt -> bind_param("s", $sClinicID);
    }
    // echo "sid:".$sSID."/".$data_result_staff."/".$data_id;

    if($stmt -> execute()){
        $stmt -> bind_result($data_name_th, $data_value);
        while($stmt -> fetch()){
            if($sSID != "" &&  $data_result_staff == ""){
                $sopt .= "<option value=".$data_value." data-id=".$data_id." ".(($data_value == $sSID)?"selected":"").">".$data_name_th."</option>";
                // $sopt .= "1in:".$sSID."/".$$data_result_staff;
            }
            else if($sSID != "" &&  $data_result_staff != "" && $sSID == $data_result_staff){
                $sopt .= "<option value=".$data_value." data-id=".$data_id." ".(($data_value == $data_result_staff)?"selected":"").">".$data_name_th."</option>";
                // $sopt .= "2in:".$sSID."/".$$data_result_staff;
            }
            else{
                $sopt .= "<option value=".$data_value." data-id=".$data_id." ".(($data_value == $data_result_staff)?"selected":"").">".$data_name_th."</option>";
                // $sopt .= "3in:".$sSID."/".$$data_result_staff;
            }
        }
    }
    $stmt -> close();
    $mysqli -> close();

    echo $sopt;
?>