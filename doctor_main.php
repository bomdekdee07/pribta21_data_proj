<?
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");

    if($sSID == "" || $sClinicID == ""){
        exit("Please login again.");
    }
    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = urlDecode(getQS("coltime"));

    $data_result_staff_md = "";
    $data_result_staff_cl = "";
    $data_result_staff_rn = "";
?>
        
<?
    // function for concat string single quote sensitive.
    function convert_singel_c($value_S){
        $values_con = "'".$value_S."'";

        return $values_con;
    }

    // Function Special character
    function convert_special_char($value){
        $d_result = "";
        $d_result = preg_replace("/[^A-Za-z0-9ก-๙เแ\-.]/", ' ', $value);

        return $d_result;
    }

    // Query collect_date first save
    $bind_param = "s";
    $array_val = array($sUid);
    $data_collect_date_first = "";

    $query = "SELECT collect_date 
    from p_data_result 
    where uid = ?
    and collect_date != '0000-00-00' 
    order by collect_date limit 1;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($collect_date_first);
        while($stmt->fetch()){
            $data_collect_date_first = $collect_date_first;
        }
    }
    $stmt->close();

    // General
    $d_data_result = array(); // data result of uid, collect_date, collect_time
    $query = "SELECT d.data_id, d.data_result, t.data_type, '1' as data_check
    FROM p_data_result d
        left join p_form_list_data t on (d.data_id = t.data_id)
    WHERE d.uid=? 
    AND d.collect_date=? 
    AND d.collect_time=? 
    and t.form_id = 'PHYSICAIN_CHART'";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss',$sUid, $sColDate, $sColTime); // echo "query : $query";

    if($stmt->execute()){
        $stmt->bind_result($data_id, $data_result, $data_type, $data_check);
        while ($stmt->fetch()) {
            if(!isset($d_data_result[$data_id]))
                $d_data_result[$data_id]["data_id"] = $data_id;
                $d_data_result[$data_id]["data_result"] = $data_result;
                $d_data_result[$data_id]["data_type"] = $data_type;
                $d_data_result[$data_id]["data_check"] = $data_check;
                // print($d_data_result[$data_id]["data_id"].": ".$d_data_result[$data_id]["data_result"]."<br>");
        }
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    
    // Latest
    $query = "SELECT  r.data_id, r.data_result, f.data_type, '2' as data_check
    FROM p_data_result as r, p_data_list as dl ,
    p_form_list_data as f
    WHERE r.uid=? AND f.form_id='PHYSICAIN_CHART'
    AND r.data_id = f.data_id AND r.data_id=dl.data_id
    AND dl.data_category = '2'
    AND r.collect_date <= ?
    ORDER BY r.collect_date DESC";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("ss", $sUid, $sColDate);

    if($stmt -> execute()){
        $stmt -> bind_result($data_id, $data_result, $data_type, $data_check);

        while($stmt -> fetch()){
            if(!isset($d_data_result[$data_id])){
                $d_data_result[$data_id]["data_id"] = $data_id;
                $d_data_result[$data_id]["data_result"] = $data_result;
                $d_data_result[$data_id]["data_type"] = $data_type;
                $d_data_result[$data_id]["data_check"] = $data_check;
            }
        }
        // print_r($d_data_result);
    }
    else{
        $msg_error .= $stmt -> error;
    }
    $stmt->close();
    
    $mysqli->close();

    // bind JS Value
    $sJS = "";
    foreach($d_data_result as $d_id => $data_val){
        $data_id = $data_val["data_id"];
        $data_result = $data_val["data_result"];
        $data_type = $data_val["data_type"];
        $data_check = $data_val["data_check"];

        $d_result = "";
        if(isset($d_data_result[$d_id])){
            $d_result = $data_result;
        }

        if($data_type == "text"){
            // $d_result = convert_special_char($d_result);
            $sJS .= '$("input[name='.$data_id.']").val('.(json_encode($d_result)).');';
            if($data_check == "1")
            $sJS .= '$("input[name='.$data_id.']").attr("data-odata",'.(json_encode($d_result)).');';
        }
        else if($data_type == "date"){
            // $d_result = convert_special_char($d_result);
            $sJS .= '$("input[name='.$data_id.']").val('.(json_encode($d_result)).');';
            if($data_check == "1")
            $sJS .= '$("input[name='.$data_id.']").attr("data-odata",'.(json_encode($d_result)).');';
        }
        else if($data_type == "number"){
            $sJS .= '$("input[name='.$data_id.']").val("'.$d_result.'");';
            if($data_check == "1")
            $sJS .= '$("input[name='.$data_id.']").attr("data-odata","'.$d_result.'");';
        }
        else if($data_type == "textarea"){
            // $d_result = convert_special_char($d_result);
            $sJS .= '$("textarea[name='.$data_id.']").val('.(json_encode($d_result)).');';
            if($data_check == "1")
            $sJS .= '$("textarea[name='.$data_id.']").attr("data-odata",'.(json_encode($d_result)).');';
        }
        else if($data_type == "checkbox"){
            $sJS .= '$("input[name='.$data_id.']").filter("[value='.convert_singel_c($d_result).']").attr("checked", true);'; // echo $sJS."<br>";
            if($data_check == "1")
            $sJS .= '$("input[name='.$data_id.']").attr("data-odata", '.convert_singel_c($d_result).');'; 
        }
        else if($data_type == "radio"){
            $sJS .= '$("input[name='.convert_singel_c($data_id).']").filter("[value='.convert_singel_c($d_result).']").attr("checked", true);';
            if($data_check == "1")
            $sJS .= '$("input[name='.convert_singel_c($data_id).']").attr("data-odata", '.convert_singel_c($d_result).');';
        }
        else{
            if($data_check == "1")
            $sJS .= '$("select[name='.convert_singel_c($data_id).']").attr("data-odata", '.convert_singel_c($d_result).');';
        }

        if($data_id == "staff_md"){
            $data_result_staff_md = $d_result;
        }
        if($data_id == "staff_rn"){
            $data_result_staff_rn = $d_result;
        }
        if($data_id == "staff_cl"){
            $data_result_staff_cl = $d_result;
        }
    }

    // PDA > 7 not edit
    if($sColDate != ""){
        $now = time(); // or your date as well
        $your_date = strtotime($sColDate);
        $datediff = $now - $your_date;
        $days = round($datediff / (60 * 60 * 24));

        if($days > 7){
            $sJS .= '$("[name=note_demo]").attr("disabled", "disabled");';
            $sJS .= '$(".pda-check").attr("disabled", "disabled");';
        }
    }
?>

<div id="doctor_main" class='fl-wrap-col' style='min-width:1024px;'>
    <div id="data_defult" data-sid="<? echo $sSID; ?>"></div>
    <div class='fl-fill fl-auto'>
        <div class="fl-wrap-row">                                                         
            <div class="fl-fill">
                <? $_GET["doc_group"] = "P_HEALTHY"; include("document_sys_bt.php"); //RECEIPT P_HEALTHY B_INVOICE DRUG_PRESC?>
            </div>
            <div class="fl-fill">
                <? $_GET["doc_group"] = "MEDICAL_C"; include("document_sys_bt.php"); //RECEIPT P_HEALTHY B_INVOICE DRUG_PRESC?>
            </div>
            <div class="fl-wrap-col document-bt-qa fabtn fl-mid" id="document_bt_add">
                <div class="fl-fill fw-b">
                    <span>QA</span>
                </div>
            </div>
            <!-- </div> -->
        </div>
        <div class='fl-wrap-row'>
            <div class='fl-wrap-col left-div wper-65'>
                <div class='fl-fix border' style="min-height: 32px; max-height: 32px;">
                    <? include("doctor_inc_resistance.php"); ?>
                </div>
                <div class='fl-fix border' style="min-height: 32px; max-height: 32px;">
                    <? include("doctor_inc_psycho.php"); ?>
                </div>
                <div class='fl-fix border'>
                    <? include("doctor_inc_vital_sign.php"); ?>
                </div>
                <div class='fl-fix border'>
                    <? include("doctor_inc_patient_comp.php"); ?>
                    <? include("doctor_inc_physical_exam.php"); ?>
                    <? include("doctor_inc_diagnosis.php"); ?>
                </div>
                <div class="fl-fix border">
                    <? include("doctor_inc_online.php"); ?>
                </div>
            </div>
            <div class='fl-wrap-col right-div wper-35'>
                <div class='fl-fix border' style="min-height: 32px; max-height: 32px;">
                    <? include("doctor_inc_visit_ref.php"); ?>
                </div>
                <div class='fl-fix border'>
                    <? include("doctor_inc_past_his.php"); ?>
                </div>
                <div class='fl-wrap-row h-30 fw-b bt-add-lab-drog'>
                    <div class="fl-fix w-5 fl-mid-left"></div>
                    <div class="fl-fix w-100 fl-mid-left">
                        <button id='btnAddDrug' class='btn font-s-3' style="background-color: #58D68D; font-weight: bold; padding: 0px 20px 0px 20px;">Medication</button>
                    </div>
                    <div class='fl-fill'></div>
                    <div class="fl-fix w-100 fl-mid-right">
                        <button id='btnAddLab'  class='btn font-s-3' style="background-color: #58D68D; font-weight: bold; padding: 0px 20px 0px 20px;">Lab</button>
                    </div>
                    <div class="fl-fix w-5 fl-mid-left"></div>
                </div>
                <div id="divTotalMedicine" class='fl-wrap-col border' style="min-height: 276px; max-height: 276px;">
                    <? include("medicine_inc_total_value.php"); ?>
                </div>
                <div id="divTotalBill" class='fl-wrap-col border' style="min-height: 302px; max-height: 302px;">
                    <? include("lab_inc_total_pric.php"); ?>
                </div>
            </div>
        </div>

        <div class="fl-wrap-row h-85">
            <div class="fl-wrap-row ml-2 fl-auto holiday-mt-s0 note-demo-hide">
                <div class='fl-fix fw-b fs-small' style='min-width:110px'>
                    <span class='language_en'><label>Additional Notes:</label></span>
                    <span class='language_th'><label>Additional Notes:</label></span>
                </div>
                <div class='fl-fill mr-1 fs-large'>
                    <textarea name='additional_note' data-id='additional_note' data-require='' data-odata='' class='save-data v_text input-group fs-smaller' value='' rows='10' style="min-height: 80px; max-height:80px;"></textarea>
                </div>
            </div>
        </div>
    </div> 
    <div class='fl-wrap-row fl-mid doctor-footer-bar'>
        <div class="fl-wrap-col" style="min-width: 350px; max-width: 350px">
            <div class='fl-wrap-row' style="min-width: 300px; max-width: 300px; min-height: 19px; max-height: 19px;">
                <div class='fl-fix smallfont2 appointments-mt-0' style='min-width:40px'>
                    <b><span class='language_en'><label>MD:</label></span><span class='language_th'><label>MD:</label></span></b>
                </div>    
                <div class='fl-fill'>
                    <select name='staff_md' data-id='staff_md' data-odata='' class='save-data v_text smallfont2 input-group'>
                        <? $data_id = "staff_md"; $data_result_staff = $data_result_staff_md; $sSID; include("doctor_opt_staff_select.php"); ?>
                    </select>
                </div>
            </div>
            <div class='fl-wrap-row' style="min-width: 300px; max-width: 300px; min-height: 19px; max-height: 19px;">
                <div class='fl-fix smallfont2 appointments-mt-0' style='min-width:40px'>
                    <b><span class='language_en'><label>RN:</label></span><span class='language_th'><label>RN:</label></span></b>
                </div>    
                <div class='fl-fill'>
                    <select name='staff_rn' data-id='staff_rn' data-odata='' class='save-data v_text smallfont2 input-group'>
                        <? $data_id = "staff_rn"; $data_result_staff = $data_result_staff_rn; $sSID; include("doctor_opt_staff_select.php"); ?>
                    </select>
                </div>
            </div>
            <div class='fl-wrap-row' style="min-width: 300px; max-width: 300px; min-height: 19px; max-height: 19px;">
                <div class='fl-fix smallfont2 appointments-mt-0' style='min-width:40px'>
                    <b><span class='language_en'><label>CL:</label></span><span class='language_th'><label>CL:</label></span></b>
                </div>    
                <div class='fl-fill'>
                    <select name='staff_cl' data-id='staff_cl' data-odata='' class='save-data v_text smallfont2 input-group'>
                        <? $data_id = "staff_cl"; $data_result_staff = $data_result_staff_cl; $sSID; include("doctor_opt_staff_select.php"); ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="fl-fix" style="min-width: 10px;"></div>
        <div class="fl-fix doctor-appointments" id="appointments_schedule">
            <? include("doctor_inc_appointments.php"); ?>
        </div>
        <div class="fl-fix" style="min-width: 330px; max-width: 330px;" id="bill_cash_approval">
            <? include("cashier_inc_approval_main.php"); ?>
        </div>
        <div class="fl-fill"></div>
        <div class="fl-fix fl-mid" style="min-width: 65px">
            <button class="btn" name="btn_doctor_add_img" style="padding: 8px 6px 8px 6px; background-color:#F9E79F;"><i class="fa fa-file-image fa-2x" aria-hidden="true"></i>+</button>
        </div>
        <div class="fl-fix smallfont2" style="min-width: 60px">
            <b><span class='language_th'>หมายเหตุ:</span><span class='language_en'>Note</span></b>
        </div>
        <div class="fl-fix" style="min-width: 260px; max-height: 56px">
            <textarea name="cn_patient_note" data-id="cn_patient_note" data-require='' data-odata='' class='save-data v_text input-group smallfont2 input-group' value='' rows='3' style="max-height: 56px"></textarea>
        </div>
        <div class='fl-fix w-m' name="div_form_view_data" data-uid='<? echo($sUid); ?>' data-coldate='<? echo($sColDate); ?>' data-coltime='<? echo($sColTime); ?>' data-ss='<? echo($sSID); ?>' data-clinicid = <? echo $sClinicID; ?>>
            <button id='btn_save_form_view' class='btn btn-success smallfont2 border' type='button' onclick='saveFormData_doctor();'><i class="fa fa-pencil-square-o" aria-hidden='true'></i> <b>บันทึกข้อมูล</b> </button>
            <i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $(".language_en").hide();
        $(".detail-show").hide();

        var check_date_visit =  $("[name= div_form_view_data]").data("coldate");
        var d = new Date();
        var month = d.getMonth()+1;
        var day = d.getDate();
        var output = d.getFullYear() + '-' +(month<10 ? '0' : '') + month + '-' +(day<10 ? '0' : '') + day;

        if(check_date_visit != output){
            $(".bt-add-lab-drog").hide();
        }

        // echo value or result
        <? echo $sJS; ?>

        // Add IMG
        $("#doctor_main [name=btn_doctor_add_img]").off("click");
        $("#doctor_main [name=btn_doctor_add_img]").on("click", function(){
            uid_s = $("[name=div_form_view_data]").data("uid");
            coldate_s = $("[name=div_form_view_data]").data("coldate");
            coltime_s = $("[name=div_form_view_data]").data("coltime");
            sUrl="doctor_img_dlg.php?uid="+uid_s+"&coldate="+coldate_s+"&coltime="+coltime_s;
            // console.log(sUrl);

            showDialog(sUrl, "Image Management", "64%", "50%","",
            function(sResult){
                //CLose function
            },false,function(){
                //Load Done Function
            });
        });

        // Check if view queue "L" not save auto
        $("#doctor_main .save-data").off("change")
        $("#doctor_main .save-data").on("change", function(){
            $.each($(this), function(){
                // Type radio
                if($(this).prop("type") === "radio"){
                    if($(this).is(":checked") == true){
                        if($(this).val() != $(this).attr("data-odata")){
                            uid_s = $("[name=div_form_view_data]").data("uid");
                            coldate_s = $("[name=div_form_view_data]").data("coldate");
                            coltime_s = $("[name=div_form_view_data]").data("coltime");

                            var aData = {
                                uid: uid_s,
                                coldate: coldate_s,
                                coltime: coltime_s
                            };

                            $.ajax({
                                url: "doctor_main_getQueue_ajax.php",
                                cash: false,
                                data: aData,
                                method: "POST",
                                success: function(sResult){
                                    var auto_save_patient_info = setInterval(function(){
                                        if(sResult == true){
                                            // console.log("inQueue: doing auto save");
                                            saveFormData_doctor_auto(auto_save_patient_info);
                                        }
                                    }, 500);
                                }
                            });
                        }
                    }
                }

                // Type checkbox
                if($(this).prop("type") === "checkbox"){
                    if($(this).is(":checked") == true){
                        if($(this).val() != $(this).attr("data-odata")){
                            uid_s = $("[name=div_form_view_data]").data("uid");
                            coldate_s = $("[name=div_form_view_data]").data("coldate");
                            coltime_s = $("[name=div_form_view_data]").data("coltime");

                            var aData = {
                                uid: uid_s,
                                coldate: coldate_s,
                                coltime: coltime_s
                            };

                            $.ajax({
                                url: "doctor_main_getQueue_ajax.php",
                                cash: false,
                                data: aData,
                                method: "POST",
                                success: function(sResult){
                                    var auto_save_patient_info = setInterval(function(){
                                        if(sResult == true){
                                            // console.log("inQueue: doing auto save");
                                            saveFormData_doctor_auto(auto_save_patient_info);
                                        }
                                    }, 500);
                                }
                            });
                        }
                    }
                }
            });
        });

        $("#doctor_main .save-data").off("keypress")
        $("#doctor_main .save-data").on("keypress", function(){
            $.each($(this), function(){
                // Type text
                if($(this).prop("type") === "text"){
                    if($(this).val() != $(this).attr("data-odata")){
                        uid_s = $("[name=div_form_view_data]").data("uid");
                        coldate_s = $("[name=div_form_view_data]").data("coldate");
                        coltime_s = $("[name=div_form_view_data]").data("coltime");

                        var aData = {
                            uid: uid_s,
                            coldate: coldate_s,
                            coltime: coltime_s
                        };

                        $.ajax({
                            url: "doctor_main_getQueue_ajax.php",
                            cash: false,
                            data: aData,
                            method: "POST",
                            success: function(sResult){
                                var auto_save_patient_info = setInterval(function(){
                                    if(sResult == true){
                                        // console.log("inQueue: doing auto save");
                                        saveFormData_doctor_auto(auto_save_patient_info);
                                    }
                                }, 500);
                            }
                        });
                    }
                }

                // Type textarea
                if($(this).prop("type") === "textarea"){
                    if($(this).val() != $(this).attr("data-odata")){
                        uid_s = $("[name=div_form_view_data]").data("uid");
                        coldate_s = $("[name=div_form_view_data]").data("coldate");
                        coltime_s = $("[name=div_form_view_data]").data("coltime");

                        var aData = {
                            uid: uid_s,
                            coldate: coldate_s,
                            coltime: coltime_s
                        };

                        $.ajax({
                            url: "doctor_main_getQueue_ajax.php",
                            cash: false,
                            data: aData,
                            method: "POST",
                            success: function(sResult){
                                var auto_save_patient_info = setInterval(function(){
                                    if(sResult == true){
                                        // console.log("inQueue: doing auto save");
                                        saveFormData_doctor_auto(auto_save_patient_info);
                                    }
                                }, 500);
                            }
                        });
                    }
                }
            });
        });

        // enter auto next tap
        var $quan = $('#doctor_main input[type=text]');
        $quan.off("keyup");
        $("#doctor_main").on("keyup", "input[type=text]", function(e) {
            // console.log("IN");
            if (e.which === 13) {
                var ind = $quan.index(this);
                var check_ind = $quan.eq(ind+1).val();

                if(typeof(check_ind) !== "undefined")
                    $quan.eq(ind + 1).focus();
                else
                    $quan.eq(0).focus();
                
            }
        });

        $("#doctor_main .rp-lab").off("click");
        $("#doctor_main").on("click", ".rp-lab", function(){
            uid_s = $("[name=div_form_view_data]").data("uid");
            coldate_s = $("[name=div_form_view_data]").data("coldate");
            coltime_s = $("[name=div_form_view_data]").data("coltime");

            let sUrl = "lab_inc_result.php?uid="+uid_s+"&coldate="+coldate_s+"&coltime="+coltime_s;
            showDialog(sUrl,"Lab Report","90%","90%","",function(sResult){   },false,function(){});
            });

        var check_online = $("[name=coun_labre]").filter(":checked").val();
        if(check_online == "Y"){
            $(".hide-data-online").show();
        }
        else{
            // $(".hide-data-online").hide();
        }

        $("[name=coun_labre]").off("click");
        $("[name=coun_labre]").on("click", function(){
            if($(this).filter(":checked").val() == "Y"){
                $(".hide-data-online").show();
            }
            else{
                // $(".hide-data-online").hide();
                // $("[name=coun_labre_y]").prop("checked", false);
            }
        });

        // tick all normal
        $(".t_all_n").unbind("click");
        $(".t_all_n").on("click", function() {
            $(".g_normal_n input[type='radio']").prop("checked", $(this).is(":checked"));

            $(".t_all_nd")[0].checked = false;
            $(".t_all_ab")[0].checked = false;

            $(".t_all_nd").prop("checked", false);
            $(".t_all_ab").prop("checked", false);

            $(".detail-show").hide();
            $(".t_show").val("");

            $("#hide_row").addClass("h-40");
        });

        // tick all ND
        $(".t_all_nd").unbind("click");
        $(".t_all_nd").on("click", function() {
            $(".g_normal_nd input[type='radio']").prop("checked", $(this).is(":checked"));

            $(".t_all_n")[0].checked = false;
            $(".t_all_ab")[0].checked = false;

            $(".t_all_n").prop("checked", false);
            $(".t_all_ab").prop("checked", false);

            $(".detail-show").hide();
            $(".t_show").val("");

            $("#hide_row").addClass("h-40");
        });

        // tick all Abnormal
        $(".t_all_ab").unbind("click");
        $(".t_all_ab").on("click", function() {
            $(".g_normal_nd input[type='radio']").prop("checked", $(this).is(":checked"));

            $(".t_all_n")[0].checked = false;
            $(".t_all_nd")[0].checked = false;

            $(".t_all_n").prop("checked", false);
            $(".t_all_nd").prop("checked", false);

            if($(this).is(":checked") == true){
                $(".detail-show").show();
            }
            else{
                $(".detail-show").hide();
                $(".g_normal_n input[type='radio']").prop("checked", false);
            }

            $("#hide_row").removeClass("h-40");
        });
        
        // check box show Other
        $(".v_checkbox").unbind("click");
        $(".v_checkbox").on("change", function(){
            var nd_checkbox = this.name;
            var nd_text = nd_checkbox.substring(0,nd_checkbox.indexOf("-"));
            var nd_text_action = nd_text+"_text";
            // debugger;

            if($("input[name="+nd_checkbox+"]").is(":checked")){
                $("input[name="+nd_text_action+"]").hide();
            }else{
                $("input[name="+nd_text_action+"]").show();
                $("input[name="+nd_text_action+"]").removeAttr("hidden");
            }

            //Other//
            if(nd_checkbox == "cn_other_checkbox-1"){
                var nd_text = nd_checkbox.substring(0,nd_checkbox.indexOf("_checkbox"));
                var nd_text_action = nd_text+"_n_nd";

                if($("input[name="+nd_checkbox+"]").is(":checked")){
                    $("textarea[name="+nd_text_action+"]").show();
                    $("textarea[name="+nd_text_action+"]").removeAttr("hidden");
                }else{
                    $("textarea[name="+nd_text_action+"]").hide();
                }
            }
        });

        // nono detail other
        $("#doctor_main [name=cn_other_n_nd]").unbind("change");
        $("#doctor_main [name=cn_other_n_nd]").on("change", function(){
            if($(this).val() == ""){
                $("#doctor_main [name=cn_other_checkbox-1]")[0].checked = false;
            }
        });

        // function color bmi
        $("input[name=cn_weight]").on("blur", function(){
            var amount = $(this).val().replace(/^\s+|\s+$/g, '');
            if( ($(this).val() != '') && (!amount.match(/^$/) )){
                $(this).val( parseFloat(amount).toFixed(2));
            }
        });

        $("input[name=heigh]").on("blur", function(){
            var amount = $(this).val().replace(/^\s+|\s+$/g, '');
            if( ($(this).val() != '') && (!amount.match(/^$/) )){
                $(this).val( parseFloat(amount).toFixed(2));
                if($(this).val() > 300){
                    alert("Can not enter value more than 300cm.")
                    $(this).val("");
                    $(this).focus();
                }
            }
        });

        $("input[name=cn_weight]").unbind("click");
        $("input[name=heigh]").unbind("click");
        
        $("input[name=cn_weight]").change(function(){
            $(".cal-bmi").val(calculate_bmi($("input[name=cn_weight]").val(), $("input[name=heigh]").val()));
        });

        $("input[name=heigh]").change(function(){
            $(".cal-bmi").val(calculate_bmi($("input[name=cn_weight]").val(), $("input[name=heigh]").val()));
        });

        // first load hind other text.
        show_new();

        // In case have data first scrren.
        $(".cal-bmi").val(calculate_bmi($("[name=cn_weight]").val(), $("[name=heigh]").val()));

        var check_md = $("#doctor_main [name=staff_md]").val();
        var check_rn = $("#doctor_main [name=staff_rn]").val();
        var check_cl = $("#doctor_main [name=staff_cl]").val();
        var sid = $("#doctor_main #data_defult").data("sid");

        if(check_md == ""){
            // $("#doctor_main [name=staff_md]").val(sid);
            $("#doctor_main [name=staff_md] option[value='"+sid+"']").prop('selected', true);
        }
        if(check_rn == ""){
            // $("#doctor_main [name=staff_rn]").val(sid);
            $("#doctor_main [name=staff_rn] option[value='"+sid+"']").prop('selected', true);
        }
        if(check_cl == ""){
            // $("#doctor_main [name=staff_cl]").val(sid);
            $("#doctor_main [name=staff_cl] option[value='"+sid+"']").prop('selected', true);
        }

        $("#doctor_main .document-bt-qa").off("click");
        $("#doctor_main .document-bt-qa").click(function(){
            var obj = $(this);
            var uid_send = $("#doctor_main [name=div_form_view_data]").data("uid");
            var col_date = $("#doctor_main [name=div_form_view_data]").data("coldate");
            var col_time = $("#doctor_main [name=div_form_view_data]").data("coltime");
            var sUrl_appoint = "qa_inc_pribta_main.php?q=&uid="+uid_send+"&coldate="+col_date+"&coltime="+col_time;

            showDialog(sUrl_appoint, "Q&A", "95%", "90%", "", function(sResult){}, false, function(sResult){});
        });

        $("#doctor_main .have-txt-box").off("change paste keyup");
        $("#doctor_main .have-txt-box").on("change paste keyup", function(){
            var name_radio = $(this).data("radio");
            var val_radio = $(this).data("radioval");

            if($("[name="+name_radio+"][value=Y]").length > 0){
                if($($(this)).val() != ""){
                    $("[name="+$(this).data("radio")+"][value=Y]").prop("checked", true);
                }
                else{
                    $("[name="+$(this).data("radio")+"][value=N]").prop("checked", true);
                }
            }            

            if($("[name="+name_radio+"][value=3]").length > 0){
                if($($(this)).val() != ""){
                    $("[name="+$(this).data("radio")+"][value=3]").prop("checked", true);
                }
                else{
                    $("[name="+$(this).data("radio")+"][value=3]").prop("checked", false);
                }
            }

            if($("[name="+name_radio+"][value=AB]").length > 0){
                if($($(this)).val() != ""){
                    $("[name="+$(this).data("radio")+"][value=AB]").prop("checked", true);
                }
                else{
                    $("[name="+$(this).data("radio")+"][value=Y]").prop("checked", true);
                }
            }
        });

        // btn show group anogen
        $("#doctor_main .btn-anogen").off("click");
        $("#doctor_main").on("click", ".btn-anogen", function(){
            var chk_classClick = $("#doctor_main .btn-anogen").closest(".thisclick").length;
            if(chk_classClick == 0){
                $("#doctor_main .btn-anogen").addClass("thisclick");
                $("#doctor_main .group-anogenital-hide").show();
            }
            else{
                $("#doctor_main .group-anogenital-hide").hide();
                $("#doctor_main .btn-anogen").removeClass("thisclick");
            }
        });

        $("#doctor_main .btn-hra").off("click");
        $("#doctor_main").on("click", ".btn-hra", function(){
            var chk_classClick = $("#doctor_main .btn-hra").closest(".thisclick").length;
            if(chk_classClick == 0){
                $("#doctor_main .btn-hra").addClass("thisclick");
                $("#doctor_main .group-hra").show();
            }
            else{
                $("#doctor_main .group-hra").hide();
                $("#doctor_main .btn-hra").removeClass("thisclick");
            }
        });

        // Auto Complete
        var auto_val = ["SEATRANS"];
        $("#doctor_main [name=cn_patient_note]").autocomplete({source: auto_val});
    });

    function show_new(){
        // Other
        var other_check = $(".g_other");

        if($("textarea[name=cn_other_n_nd]").val() != "" || other_check["0"].children[0].checked == true){
            $(".t_show_2")["0"].show = true;
            $("input[name=cn_other_checkbox-1]")[0].checked = true;
        }else{
            $(".t_show_2")["0"].hidden = true;
            $("input[name=cn_other_checkbox-1]")[0].checked = false;
        };

        // Tick all Normal
        var length_check_N = $(".g_normal_n").length;
        var count_loop_N = 0;
        var cont_check_N = 0;
        var g_normal_n = $(".g_normal_n");
        var check_g_normal_n = 0;
        //hace other = abnormal
        var chk_other_have = $("[name=cn_other_n_nd]").val();

        g_normal_n.children().each(function(){
            if(g_normal_n.children()[count_loop_N].checked == true){
                cont_check_N = cont_check_N+1;
            }
            count_loop_N = count_loop_N+1;
        })

        if(length_check_N == cont_check_N && chk_other_have == ""){
            $(".t_all_n").prop("checked", true);
            $("#hide_row").addClass("h-40");
        }
        else{
            $(".t_all_n").prop("checked", false);
        }

        // Tick all ND
        var length_check = $(".g_normal_nd").length;
        var count_loop = 0;
        var cont_check = 0;
        var g_normal_nd = $(".g_normal_nd");

        g_normal_nd.children().each(function(){
            if(g_normal_nd.children()[count_loop].checked == true){
                cont_check = cont_check+1;
            }
            count_loop = count_loop+1;
        })

        if(length_check == cont_check){
            $(".t_all_nd").prop("checked", true);
            $("#hide_row").addClass("h-40");
        }
        else{
            $(".t_all_nd").prop("checked", false);
        }

        // Tick all Abnormal
        var length_check_AB = $(".g_normal_ab").length;
        var count_loop_AB = 0;
        var cont_check_AB = 0;
        var g_normal_ab = $(".g_normal_ab");

        g_normal_ab.children().each(function(){
            if(g_normal_ab.children()[count_loop_AB].checked == true){
                cont_check_AB = cont_check_AB+1;
            }
            count_loop_AB = count_loop_AB+1;
        })

        if(cont_check_AB > 0 || chk_other_have != ""){
            $(".detail-show").show();
            $(".t_all_ab").prop("checked", true);
            $("#hide_row").removeClass("h-40");
        }
        else{
            $(".detail-show").hide();
            $(".t_all_ab").prop("checked", false);
        }

        var chk_ab_group_hra = $(".hra-row");
        chk_ab_group_hra.each(function(){
            var loop_check = $(this).is(":checked");
            if(loop_check){
                $(".group-hra").show();
            }
        })

        var chk_ab_group_anogen = $(".anogen-row");
        chk_ab_group_anogen.each(function(){
            var loop_check = $(this).is(":checked");
            if(loop_check){
                $(".group-anogenital-hide").show();
            }
        })
    };

    function calculate_bmi(weight, height){
        if(weight === ""){
            var weight = 0;
        }
        else{
            var weight = parseFloat(weight).toFixed(2);
        }

        if(height === ""){
            var height_m = 0;
        }
        else{
            var height_m = (parseFloat(height).toFixed(2)/100)*(parseFloat(height).toFixed(2)/100);
        }
        var bmi_total = (weight/height_m).toFixed(2);

        if(weight != 0 && height_m != 0){
            if(bmi_total < 18.50){
                $(".cal-bmi").attr('style','border: 2px solid #F7DC6F;');
            }
            else if(bmi_total >= 18.50 && bmi_total <= 22.90){
                $(".cal-bmi").attr('style','border: 2px solid #2ECC71;');
            }
            else if(bmi_total >= 22.91 && bmi_total <= 24.90){
                $(".cal-bmi").attr('style','border: 2px solid #F39C12;');
            }
            else if(bmi_total > 24.90){
                $(".cal-bmi").attr('style','border: 2px solid #E74C3C;');
            }

            return bmi_total;
        }
        else{
            $(".cal-bmi").val("");
            $(".cal-bmi").removeAttr("style");
        }
    }

    function getWObjValue(obj){
        var sValue = "";
        if($(obj)){
            var sTagName = $(obj).prop("tagName").toUpperCase();

            if(sTagName=="INPUT"){
                if($(obj).prop("type")){
                    if($(obj).prop("type").toLowerCase()=="checkbox"){
                        sValue = ($(obj).prop("checked"))?1:"";
                    }
                    else if($(obj).prop("type").toLowerCase()=="radio"){
                        var sName = $(obj).attr("name");
                        sValue = $("input[name='"+sName+"']").filter(":checked").val();
                    }
                    else{
                        sValue = $(obj).val();
                    }
                }
                else{
                    sValue = $(obj).val();
                }
            }
            else{
                sValue = $(obj).val();
            }

            if($(obj).hasClass("v_date")){
                var arrDate = sValue.split("/");

                if(arrDate.length == 3){
                    sValue = (parseInt(arrDate[2]) - 543)+"-"+arrDate[1]+"-"+ arrDate[0] ;
                }
            }
            
            return sValue;
        }
    }

    function saveFormData_doctor_auto(obj){
        var divSaveData = "div_form_view_data";
        var lst_data_obj = [];

        // In case change value if not have value not change.
        $("#doctor_main .save-data-radio:checked").each(function(ix,objx){
            $("input[name="+$(objx).data("id")+"]").data("val",  $(objx).val());
            // console.log("data_id_radio: "+$(objx).data("id")+"/"+$(objx).val());
        });
        $("#doctor_main .save-data-radio:checked").each(function(ix,objx){
            $("input[name="+$(objx).data("id")+"]").data("val",  $(objx).val());
            // console.log("data_id_radio: "+$(objx).data("id")+"/"+$(objx).val());
        });

        var old_value = "";
        $("#doctor_main .save-data").each(function(ix,objx){
            var objVal = "";
            var odata_val = "";
            
            objVal = getWObjValue($(objx));
            odata_val = $(objx).data("odata");
            if(typeof odata_val === "undefined"){
                odata_val = "";
            }
            if(typeof objVal === "undefined"){
                objVal  = "";
            }
            odata_val = (odata_val?odata_val.toString().replace(/"|'/g,''):odata_val); //ไม่ใช้แล้วเพราะใช้ json_encode()
            // console.log(odata_val+"new"+objVal);
            // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val.toString().replace(/'/g,"")); //cn_family_history_text
            // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val);

            if(objVal != odata_val){
                var data_item = {};

                data_item[$(objx).data("id")] = (objVal?objVal.toString().replace(/"|'/g,'') : objVal);
                lst_data_obj.push(data_item);
                console.log("data_id: "+$(objx).data("id")+":"+objVal+"-"+odata_val+";");
            }

            old_value = $(objx).data("id");
        });

        if(lst_data_obj.length > 0){
            var aData = {
                uid:$("[name="+divSaveData +"]").data("uid"),
                coldate:$("[name="+divSaveData +"]").data("coldate"),
                coltime:$("[name="+divSaveData +"]").data("coltime"),
                sid:$("#data_defult").data("sid"),
                dataid:lst_data_obj,
            };
            // console.log(aData);

            $("#btn_save_form_view").next(".spinner").show();
            $("#btn_save_form_view").hide();
            callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_doctor_auto);
            clearInterval(obj);
        }
        else{
            // $.notify("No data change", "warn");
            clearInterval(obj);
        }
    };

    function saveFormDataComplete_doctor_auto(flagSave, aData, rtnDataAjax){
        // console.log(flagSave+"/"+rtnDataAjax);
        if(flagSave){
            // $.notify("Save Data Auto", "success");
            var divSaveData = "div_form_view_data";

            //update all odata of  value changed data_id
            var conValue = "";
            Object.keys(aData.dataid).forEach(function(i){
                Object.keys(aData.dataid[i]).forEach(function(data_id){
                    conValue = aData.dataid[i][data_id];
                    conValue = conValue;
                    // console.log(i+data_id + " - " +conValue);
                    $("[name="+data_id+"]").data("odata", conValue);
                });
            });

            // Open Dlg appointment calendar
            var sUid = flagSave["uid_rtn"];
            var sQ = flagSave["queue_rtn"];
            var coldate = $("[name=div_form_view_data]").data("coldate");
            
            var d = new Date();
            var day = d.getDate();
            var month = d.getMonth()+1;
            var year = d.getFullYear();
            var present_date = year+"-"+(month<10 ? '0' : '') + month +"-"+(day<10 ? '0' : '') + day;
        }

        $("#btn_save_form_view").next(".spinner").hide();
        $("#btn_save_form_view").show();
    }

    function saveFormData_doctor(){
        var divSaveData = "div_form_view_data";
        var lst_data_obj = [];

        // In case change value if not have value not change.
        $("#doctor_main .save-data-radio:checked").each(function(ix,objx){
            $("input[name="+$(objx).data("id")+"]").data("val",  $(objx).val());
            // console.log("data_id_radio: "+$(objx).data("id")+"/"+$(objx).val());
        });
        $("#doctor_main .save-data-radio:checked").each(function(ix,objx){
            $("input[name="+$(objx).data("id")+"]").data("val",  $(objx).val());
            // console.log("data_id_radio: "+$(objx).data("id")+"/"+$(objx).val());
        });

        var old_value = "";
        $("#doctor_main .save-data").each(function(ix,objx){
            var objVal = "";
            var odata_val = "";
            
            if(objVal != old_value){
                objVal = getWObjValue($(objx));
                odata_val = $(objx).data("odata");
                if(typeof odata_val === "undefined"){
                    odata_val = "";
                }
                if(typeof objVal === "undefined"){
                    objVal  = "";
                }
                odata_val = (odata_val?odata_val.toString().replace(/"|'/g,''):odata_val); //ไม่ใช้แล้วเพราะใช้ json_encode()
                // console.log(odata_val+"new"+objVal);
                // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val.toString().replace(/'/g,"")); //cn_family_history_text
                // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val);
            }

            if(objVal != odata_val){
                var data_item = {};

                data_item[$(objx).data("id")] = (objVal?objVal.toString().replace(/"|'/g,'') : objVal);
                lst_data_obj.push(data_item);
                // console.log("data_id: "+$(objx).data("id")+":"+objVal+"-"+odata_val+";");
            }

            old_value = $(objx).data("id");
        });

        if(lst_data_obj.length > 0){
            var aData = {
                uid:$("[name="+divSaveData +"]").data("uid"),
                coldate:$("[name="+divSaveData +"]").data("coldate"),
                coltime:$("[name="+divSaveData +"]").data("coltime"),
                sid:$("#data_defult").data("sid"),
                dataid:lst_data_obj,
            };
            // console.log(aData);

            callAjax("doctor_db_form_update.php", aData, saveFormDataComplete_doctor);
            $("#btn_save_form_view").next(".spinner").show();
            $("#btn_save_form_view").hide();
        }
        else{
            $.notify("No data change", "warn");
        }
    }

    function saveFormDataComplete_doctor(flagSave, aData, rtnDataAjax){
        // console.log(flagSave+"/"+rtnDataAjax);
        if(flagSave){
            $.notify("Save Data", "success");
            var divSaveData = "div_form_view_data";

            //update all odata of  value changed data_id
            var conValue = "";
            Object.keys(aData.dataid).forEach(function(i){
                Object.keys(aData.dataid[i]).forEach(function(data_id){
                    conValue = aData.dataid[i][data_id];
                    conValue = conValue;
                    // console.log(i+data_id + " - " +conValue);
                    $("[name="+data_id+"]").data("odata", conValue);
                });
            });

            // Open Dlg appointment calendar
            var sUid = flagSave["uid_rtn"];
            var sQ = flagSave["queue_rtn"];
            var coldate = $("[name=div_form_view_data]").data("coldate");
            
            var d = new Date();
            var day = d.getDate();
            var month = d.getMonth()+1;
            var year = d.getFullYear();
            var present_date = year+"-"+(month<10 ? '0' : '') + month +"-"+(day<10 ? '0' : '') + day;
            var sUrl = "queue_inc_fwd.php?uid="+sUid+"&q="+sQ;
            // console.log(sUrl);
            // console.log(coldate+"/"+present_date);

            if(sQ != "" && coldate == present_date){
                showDialog(sUrl,"FWD ส่งคิวต่อไปห้องอื่น","600","1024","",function(result){
                    
                },false,function(){
                    $("#divQueueFwd input[name='room_no'][value='24']").prop("checked",true);
                    $("#divQueueFwd input[name='room_no'][value='24']").focus();
                });
            }
        }

        $("#btn_save_form_view").next(".spinner").hide();
        $("#btn_save_form_view").show();
    }
</script>   