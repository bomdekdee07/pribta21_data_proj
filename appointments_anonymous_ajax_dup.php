<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $tel_phone = getQS("tel_no", "99999999999999");

    $data_dup_pateint = array();
    $query = "select uid, fname, sname, en_fname, en_sname, date_of_birth 
    from patient_info 
    where REPLACE(REPLACE(REPLACE(REPLACE(tel_no, ' ', ''), ',', ''), '-', ''), '/', '') = ?
    order by uid ASC;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $tel_phone);

    if($stmt->execute()){
        $stmt->bind_result($uid, $fname, $sname, $en_fname, $en_sname, $date_of_birth);
        while($stmt->fetch()){
            $data_dup_pateint[$uid]["uid"] = $uid;
            $data_dup_pateint[$uid]["name"] = isset($fname)? $fname." ".$sname : $en_fname." ".$en_fname;
            $data_dup_pateint[$uid]["date_of_birth"] = $date_of_birth;
        }
    }
    $stmt->close();
    $mysqli->close();

    $check_row_data = count($data_dup_pateint);

    $stJS_dupAnonymous = "";
    $stJS_dupAnonymous .=   '<div class="fl-wrap-row h-25 font-s-3">
                                <div class="fl-fix wper-5"></div>
                                <div class="fl-fix wper-90 fw-b fl-mid-left" style="color: red;">
                                    พบข้อมูลซ้ำในระบบ!
                                </div>
                            </div>
                            <div class="fl-wrap-row h-30 font-s-2 fw-b">
                                <div class="fl-fix wper-5"></div>
                                <div class="fl-fix wper-25 border-t border-bt border-l fl-mid">
                                    UID
                                </div>
                                <div class="fl-fix wper-35 border-t border-bt fl-mid-left">
                                    ชื่อ-นามสกุล
                                </div>
                                <div class="fl-fix wper-20 border-t border-bt fl-mid-left">
                                    วันเกิด
                                </div>
                                <div class="fl-fix wper-10 border-t border-bt fl-mid-left border-r"></div>
                                <div class="fl-fix wper-5"></div>
                            </div>
                            <div class="fl-wrap-col fl-auto h-150">';

    if(count($data_dup_pateint) > 0){
        foreach($data_dup_pateint as $key => $val){
            $stJS_dupAnonymous .=   '<div class="fl-wrap-row h-30">
                                        <div class="fl-wrap-col wper-5"></div>
                                        <div class="fl-wrap-col wper-90">
                                            <div class="fl-wrap-row h-30 font-s-1 row-color row-hover">
                                                <div class="fl-fix fl-mid" style="min-width: 27.5%; max-width: 27.5%;">
                                                    '.$val["uid"].'
                                                </div>
                                                <div class="fl-fix fl-mid-left" style="min-width: 39%; max-width: 39%;">
                                                    '.$val["name"].'
                                                </div>
                                                <div class="fl-fix wper-20 fl-mid-left">
                                                    '.$val["date_of_birth"].'
                                                </div>
                                                <div class="fl-fill fl-mid">
                                                    <button class="btn btn-primary" id="btSelectDup" data-uid="'.$val["uid"].'" data-name="'.$val["name"].'" style="padding: 0px 15px;"><span class="fw-b"><i class="fa fa-check" aria-hidden="true" style="color: #95F556;"></i> Select</span></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="fl-wrap-col wper-5"></div>
                                    </div>';
        }
    }
    $stJS_dupAnonymous .=   '</div>';

    if(count($data_dup_pateint) > 1)
        echo "yesDupMany"."///".$stJS_dupAnonymous;
    else if($check_row_data == 1){
        foreach($data_dup_pateint as $key => $val){
            echo "yesDupOne"."///".$val["uid"]."///"."";
        }
    }
    else
        echo "noHave"."///"."";
?>

<script>
    $(document).ready(function(){
        //Select dup
        $("#anonymous_main #btSelectDup").off("click");
        $("#anonymous_main #btSelectDup").on("click", function(){
            var uidS = $(this).data("uid");
            $("#anonymous_main #btn_cancel_hide_dup").trigger( "click", [uidS] );
        });
    });
</script>