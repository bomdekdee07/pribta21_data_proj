<?
    include("in_session.php");
    include('in_db_conn.php');
    include_once("in_php_function.php");

    $uid_input = getQS("uid");

    $data_patient_relate = array();
    $query = "select info_relate.uid, 
        info_relate.rel_uid, 
        info_relate.rel_type,
        pa_info.fname,
        pa_info.sname
    from patient_info_relate info_relate
    left join patient_info pa_info on((pa_info.uid = info_relate.uid or pa_info.uid = info_relate.rel_uid) and pa_info.uid != ?)
    where (info_relate.uid = ? or info_relate.rel_uid = ?)
    and (info_relate.uid is not null or info_relate.rel_uid is not null);";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $uid_input, $uid_input, $uid_input);
    if($stmt->execute()){
        $stmt->bind_result($uid, $rel_uid, $rel_type, $fname, $sname);

        while ($stmt->fetch()){
            if($rel_uid != $uid_input){
                $data_patient_relate[$uid]["ref"] = $rel_uid;
            }
            else{
                $data_patient_relate[$uid]["ref"] = $uid;
            }
            $data_patient_relate[$uid]["type"] = $rel_type;
            $data_patient_relate[$uid]["name"] = $fname." ".$sname;
        }
        // print_r($data_patient_relate);
    }
    $stmt->close();
    $mysqli->close();

    $sJS_relate = "";
    $sJS_relate .=  '<div class="fl-wrap-col" id="patient_relate">';
    $sJS_relate .=      '<div class="fl-wrap-row fw-b fs-smaller h-25" style="background-color: #D6B5E2;">
                            <div class="fl-fix wper-15 fl-mid border">
                                <span>UID</span>
                            </div>
                            <div class="fl-fix wper-25 fl-mid-left border">
                                <span class="holiday-ml-4">Name</span>
                            </div>
                            <div class="fl-fill fl-mid-left border">
                                <span class="holiday-ml-4">Relate</span>
                            </div>
                        </div>';
    foreach($data_patient_relate as $key => $val){
        $sJS_relate .=  '<div class="fl-wrap-row fs-small h-25 border-bottom">
                            <div class="fl-fix wper-15 fl-mid">
                                <span>'.$val["ref"].'</span>
                            </div>
                            <div class="fl-fix wper-25 fl-mid-left">
                                <span class="holiday-ml-4">'.$val["name"].'</span>
                            </div>
                            <div class="fl-fill fl-mid-left">
                                <span class="holiday-ml-4">'.$val["type"].'</span>
                            </div>
                        </div>';
    }
    $sJS_relate .=  '</div>';

    echo $sJS_relate;
?>