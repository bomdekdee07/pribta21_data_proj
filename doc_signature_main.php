<?
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $gSid = getSS("s_id")!=""? getSS("s_id") : getQS("s_id");
    $gDoc_code = getQS("doctype");
    $gClinic_id = getSS("clinic_id")!=""? getSS("clinic_id") : getQS("clinic_id");
    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $type_leg = getQS("type_leg") == ""? "TH":getQS("type_leg");
    $check_type_leg_th = $type_leg == "TH"? "checked":""; 
    $check_type_leg_en = $type_leg == "EN"? "checked":"";
    // echo "val:".$gSid."/".$gDoc_code."/".$gClinic_id."/".$uid."/".$coldate."/".$coltime;

    $time_now = date("h:i:sa");

    $bind_param = "ssssss";
    $array_val = array($gClinic_id, $gSid, $gDoc_code, $uid, $coldate, $coltime);
    $data_check_sign = array();

    $query = "SELECT count(*) check_sign,
        sig_status,
        sig_leg_type
    from i_doc_signatures
    where clinic_id = ?
    and s_id = ?
    and doc_code = ?
    and uid = ?
    and collect_date = ?
    and collect_time = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($check_sign, $sig_status, $sig_leg_type);
        while($stmt->fetch()){
            $data_check_sign["check_status"] = $check_sign;
            $data_check_sign["status"] = $sig_status;
            $data_check_sign["type_leg"] = $sig_leg_type;
        }
        // print_r($data_check_sign);
    }
    $stmt->close();
    $mysqli->close();

    $style_bt_sign = "";
    $text_bt_sign = "";
    $val_bt_sign = "";

    if($data_check_sign["check_status"] == "0"){
        $style_bt_sign = "btn-success";
        $text_bt_sign = "Sign";
        $val_bt_sign = "1";
    }
    else{
        if($data_check_sign["status"] == "1" && getQS("type_leg") == ""){ // 1=sign, 0=unsign
            $style_bt_sign = "btn-danger";
            $text_bt_sign = "Unsign";
            $val_bt_sign = "0";
        }
        else if(getQS("type_leg") != "" && $data_check_sign["type_leg"] != $type_leg){
            $style_bt_sign = "btn-success";
            $text_bt_sign = "Sign";
            $val_bt_sign = "1";
        }
        else if($data_check_sign["status"] == "0" && $data_check_sign["type_leg"] == $type_leg){
            $style_bt_sign = "btn-success";
            $text_bt_sign = "Sign";
            $val_bt_sign = "1";
        }
        else{
            $style_bt_sign = "btn-danger";
            $text_bt_sign = "Unsign";
            $val_bt_sign = "0";
        }
    }

    if(isset($data_check_sign["type_leg"]) && getQS("type_leg") == ""){
        $type_leg = $data_check_sign["type_leg"];

        if($data_check_sign["type_leg"] == "TH"){
            $check_type_leg_th = "checked";
            $check_type_leg_en = "";
        }
        else{
            $check_type_leg_th = "";
            $check_type_leg_en = "checked";
        }
    }
    
    $bind_html_sign = "";
    $bind_html_sign .= '<div class="fl-fix w-60"></div>
                        <div id="doc_signature_main" class="fl-wrap-col" data-sid="'.$gSid.'" data-doccode="'.$gDoc_code.'" data-clinicid="'.$gClinic_id.'" data-uid="'.$uid.'" data-coldate="'.$coldate.'" data-coltime="'.$coltime.'" style="background-color:#EAEDEA; min-height:120px; max-height:120px; max-width: 35%;">
                            <div class="fl-wrap-row" style="min-height:120px;max-height:120px;">
                                <div class="fl-wrap-col" style="min-width:70%;">
                                    <div class="fl-wrap-row h-15"></div>
                                    <div class="fl-wrap-row h-30">
                                        <div class="fl-fix w-25 fl-mid-left font-s-1"></div>
                                        <div class="fl-fix w-40 fl-mid-left font-s-1">
                                            <label><input type="radio" id="type_leg_main" name="type_leg_main" value="TH" '.$check_type_leg_th.' /> TH</label>
                                        </div>
                                        <div class="fl-fix w-100 fl-mid-left font-s-1">
                                            <label><input type="radio" id="type_leg_main" name="type_leg_main" value="EN" '.$check_type_leg_en.' /> EN</label>
                                        </div>
                                    </div>
                                    <div class="fl-wrap-row" style="min-height:70px;max-height:70px;">
                                        <div class="fl-fill fl-mid">
                                            <img id="imgDefSig" src="staff_signature/'.$gSid.'_'.$type_leg.'.png?'.$time_now.'" class="sign-img-responsive" onerror="this.src=\'staff_signature/default.png\'"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="fl-wrap-col">
                                    <div class="fl-wrap-row" style="min-height:10px;max-height:30px;"></div>
                                    <div class="fl-wrap-row" style="min-height:40px;max-height:40px;">
                                        <div class="fl-fill fl-mid-left holiday-ml-1 fw-b font-s-1">
                                            <button id="bt_confix_signature" class="btn btn-info" style="padding-left:15px; padding-right:15px;"><i class="fa fa-cog" aria-hidden="true"> ตั้งค่า</i></button>
                                        </div>
                                    </div>
                                    <div class="fl-wrap-row" style="min-height:40px;max-height:40px;">
                                        <div class="fl-fill fl-mid-left holiday-ml-1 fw-b font-s-1 reload-bt-click">
                                            <button id="bt_signature" class="btn '.$style_bt_sign.'" value="'.$val_bt_sign.'" style="padding-left:20px; padding-right:20px;"><i class="fa fa-user" aria-hidden="true"> '.$text_bt_sign.'</i></button>
                                            <i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';

    echo $bind_html_sign;
?>

<script>
    $(document).ready(function(){
        $("#doc_signature_main #type_leg_main").off("change");
        $("#doc_signature_main #type_leg_main").on("change", function(){
            var s_id_s = $("#doc_signature_main").data("sid");
            var doc_code_s = $("#doc_signature_main").data("doccode");
            var clinic_id_s = $("#doc_signature_main").data("clinicid");
            var uid_s = $("#doc_signature_main").data("uid");
            var coldate_s = $("#doc_signature_main").data("coldate");
            var coltime_s = $("#doc_signature_main").data("coltime");
            var bt_val_s = $("#doc_signature_main #bt_signature").val();
            var type_leg_s = $("#doc_signature_main #type_leg_main:checked").val();
            $("#doc_signature_main").parent().load("doc_signature_main.php?s_id="+s_id_s+"&doctype="+doc_code_s+"&clinic_id="+clinic_id_s+"&uid="+uid_s+"&coldate="+coldate_s+"&coltime="+coltime_s+"&type_leg="+type_leg_s);
        });

        $("#doc_signature_main #bt_signature").off("click");
        $("#doc_signature_main #bt_signature").on("click", function(ev){
            ev.preventDefault();
            var type_leg_s = $("#doc_signature_main #type_leg_main:checked").val();
            var s_id_s = $("#doc_signature_main").data("sid");
            var doc_code_s = $("#doc_signature_main").data("doccode");
            var clinic_id_s = $("#doc_signature_main").data("clinicid");
            var uid_s = $("#doc_signature_main").data("uid");
            var coldate_s = $("#doc_signature_main").data("coldate");
            var coltime_s = $("#doc_signature_main").data("coltime");
            var bt_val_s = $("#doc_signature_main #bt_signature").val();
            var src_file = "staff_signature/"+s_id_s+"_"+type_leg_s+".png";

            $.ajax({
                url: src_file,
                type: "HEAD",
                error: function(){
                    alert("ยังไม่มีรูปภาพลายเซ็น");
                },
                success: function(){
                    var aData = {
                        app_mode: "ins_docsign",
                        clinic_id: clinic_id_s,
                        doc_code: doc_code_s,
                        s_id: s_id_s,
                        uid: uid_s,
                        collect_date: coldate_s,
                        collect_time: coltime_s,
                        sig_status: bt_val_s,
                        sig_leg_type: type_leg_s
                    };

                    callAjax("doc_sign_db_ins_upd.php", aData, saveFormDataComplete);
                    $("#bt_signature").next(".spinner").show();
                    $("#bt_signature").hide();
                }
            });
        });

        $("#doc_signature_main #bt_confix_signature").off("click");
        $("#doc_signature_main #bt_confix_signature").on("click", function(ev){
            ev.preventDefault();
            sUrl="user_dlg_signature_edit.php";
            var s_id_s = $("#doc_signature_main").data("sid");
            var doc_code_s = $("#doc_signature_main").data("doccode");
            var clinic_id_s = $("#doc_signature_main").data("clinicid");
            var uid_s = $("#doc_signature_main").data("uid");
            var coldate_s = $("#doc_signature_main").data("coldate");
            var coltime_s = $("#doc_signature_main").data("coltime");
            var bt_val_s = $("#doc_signature_main #bt_signature").val();
            var type_leg_s = $("#doc_signature_main #type_leg_main:checked").val();

			showDialog(sUrl,"Signature Editor","320","450","",
			function(sResult){
				//CLose function
				if(sResult=="REFRESH"){
					$("#doc_signature_main").parent().load("doc_signature_main.php?s_id="+s_id_s+"&doctype="+doc_code_s+"&clinic_id="+clinic_id_s+"&uid="+uid_s+"&coldate="+coldate_s+"&coltime="+coltime_s+"&type_leg="+type_leg_s);
				}
			},false,function(){});
        });
    });

    function saveFormDataComplete(flagSave, aData, rtnDataAjax){
        // console.log(flagSave+"/"+rtnDataAjax);
        if(flagSave){
            $.notify("Save Data", "success");

            $("#bt_signature").next(".spinner").hide();
            $("#bt_signature").show();

            var s_id_s = $("#doc_signature_main").data("sid");
            var doc_code_s = $("#doc_signature_main").data("doccode");
            var clinic_id_s = $("#doc_signature_main").data("clinicid");
            var uid_s = $("#doc_signature_main").data("uid");
            var coldate_s = $("#doc_signature_main").data("coldate");
            var coltime_s = $("#doc_signature_main").data("coltime");
            var bt_val_s = $("#doc_signature_main #bt_signature").val();
            var type_leg_s = $("#doc_signature_main #type_leg_main:checked").val();
            $("#doc_signature_main").parent().load("doc_signature_main.php?s_id="+s_id_s+"&doctype="+doc_code_s+"&clinic_id="+clinic_id_s+"&uid="+uid_s+"&coldate="+coldate_s+"&coltime="+coltime_s+"&type_leg="+type_leg_s);
        }
    }
</script>