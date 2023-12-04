<?
    include("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");
    // echo "Test:".$$data_result_staff; //GET from main include.

    if($sClinicID == null){
        $sClinicID = "";
    }
    if($sUid == null){
        $sSID = "";
    }
    $type_special = getQS("type_special");
    // echo $sUid."/".$sClinicID;

    $bind_param = "s";
    $array_val = array($sClinicID);

    $sopt = "<option value=''>-- Not found! --</option>";
    $query = "SELECT distinct main.doc_code, main.doc_name, main.doc_template_file 
    from i_doc_master_list as main
    where main.doc_status = 1
    and main.clinic_id = ?";

    if($type_special == "y"){
        $query .= " and main.doc_template_file = 'medical_certificate_main'";
    }

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param($bind_param, ...$array_val);

    if($stmt -> execute()){
        $stmt -> bind_result($doc_code, $doc_name, $doc_template_file);
        while($stmt -> fetch()){
            $sopt .= "<option value=".$doc_code.",".$doc_template_file.">".$doc_name."</option>";
        }
    }
    $stmt -> close();
    $mysqli -> close();

    
    echo $sopt;
?>