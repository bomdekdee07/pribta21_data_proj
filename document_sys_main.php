<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    if($sSID == ""){
        echo("Please login.");
        exit();
    }
    $sClinicID = getSS("clinic_id");
    if($sClinicID == null){
        $sClinicID = getQS("clinic_id");
    }
    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = urlDecode(getQS("coltime"));
    $doc_type = getQS("doctype");
    $bill_id_show = getQS("billid");
    $bill_id = preg_replace("/[^A-Za-z0-9ก-๙เแ\-.]/", '', $bill_id_show);
    // echo $sSID."/".$sClinicID."/".$doc_type."//".$bill_id;

    $data_temp_file = "";
    $query = "select doc_template_file from i_doc_master_list where doc_code = ?;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $doc_type);

    if($stmt->execute()){
        $stmt->bind_result($doc_template_file);
        while ($stmt->fetch()) {
            $data_temp_file = $doc_template_file;
        }
        // print_r($data_permission);
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    
    $case_asset_list = "red";
    $query = "select distinct main.doc_code, main.doc_name, main.doc_template_file 
    from i_doc_master_list as main
    where main.doc_status = 1
    and main.clinic_id = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $sClinicID);

    if($stmt -> execute()){
        $stmt -> bind_result($doc_code, $doc_name, $doc_template_file);
        while($stmt -> fetch()){
            if($doc_code == $doc_type){
                $case_asset_list = "green";
                break;
            }
        }
        // echo $doc_code."/".$doc_type.":".$sClinicID;
    }
    $stmt -> close();
    $mysqli->close();

    $case_asset_doc_create = "red";
    $case_asset_doc_view = "red";
    if(isset($_SESSION["DOC"][$doc_type]["view"])){
        $case_asset_doc_view = "green";
    }
    else{
        $case_asset_doc_view = "red";
    }
    if(isset($_SESSION["DOC"][$doc_type]["create"])){
        $case_asset_doc_create = "green";
    }
    else{
        $case_asset_doc_create = "red";
    }

    $sJS = "";
    $sJS .= '$("#document_main #document_type").val("'.$doc_type.','.$data_temp_file.'");';
    $sJS .= '$("#document_main #document_type").change();';
    if($bill_id != ""){
        $sJS .= '$("#document_main #document_uid").val("'.$bill_id_show.'");';
    }
    else{
        $sJS .= '$("#document_main #document_uid").val("'.$sUid.'");';
    }
    $sJS .= '$("#document_main #document_date").val("'.$sColDate.'");';
    $sJS .= '$("#document_main #document_time").val("'.$sColTime.'");';

    $head_gen1 = "";
    $head_gen2 = "";
    $head_gen3 = "";
    $head_gen1 .= '<div class="fl-fix" id="documentmain">
    <div class="fl-wrap-row">
        <div class="fl-fill holiday-box-serch holiday-ml-0 holiday-mr-1">
            <div class="fl-wrap-row h-20 fa-sm">
                <div class="fl-fix w-20">
                    <i class="fa fa-list-ul" aria-hidden="true" style="color: '.$case_asset_list.'"></i>
                </div>
                <div class="fl-fix w-20">
                    <i class="fa fa-plus-square" aria-hidden="true" style="color: '.$case_asset_doc_create.'"></i>
                </div>
                <div class="fl-fix w-20">
                    <i class="fa fa-search" aria-hidden="true" style="color: '.$case_asset_doc_view.'"></i>
                </div>
            </div>
            <div class="fl-wrap-row h-30">
                <div class="fl-fix holiday-ml-2 smallfont2 holiday-mt-2" style="min-width: 95px">
                    <b><span>ประเภทเอกสาร:</span></b>
                </div>
                <div class="fl-fix holiday-mt-2" style="min-width: 150px">';
    if($doc_type != "MEDICAL_C"){
        $head_gen1 .= '<select id="document_type" name="document_type" class="smallfont2 input-group" disabled>';
    }else{
        $head_gen1 .= '<select id="document_type" name="document_type" class="smallfont2 input-group">';
    }
    $head_gen2 .=    '</select>
                </div>
                <div class="fl-fix" style="min-width: 10px"></div>
                
                <div class="fl-fix holiday-ml-2 smallfont2 holiday-mt-2" style="min-width: 36px">
                    <b><span>UID:</span></b>
                </div>
                <div class="fl-fix holiday-mt-2" style="min-width: 150px">
                    <input type="text" id="document_uid" class="input-group smallfont2 holiday-mt-01">
                </div>
                <div class="fl-fix holiday-ml-2 smallfont2 holiday-mt-2" style="min-width: 40px">
                    <b><span>Date:</span></b>
                </div>
                <div class="fl-fix holiday-mt-2" style="min-width: 150px">
                    <input type="text" id="document_date" class="input-group smallfont2 holiday-mt-01">
                </div>
                <div class="fl-fix holiday-ml-2 smallfont2 holiday-mt-2" style="min-width: 40px">
                    <b><span>Time:</span></b>
                </div>
                <div class="fl-fix holiday-mt-2" style="min-width: 150px">
                    <input type="text" id="document_time" class="input-group smallfont2 holiday-mt-01">
                </div>
                
                <div class="fl-fix" style="min-width: 30px"></div>
                <div class="fl-fix" style="min-width: 100px" id="btn_check">';

    $head_gen3 .= '</div>
                </div>
            </div>
        </div>

        <div class="fl-wrap-row">
            <div class="fl-fill holiday-box-head holiday-ml-0 holiday-mr-1">
                <div class="fl-wrap-row">
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 175px;">
                        <b><span>วันที่สร้าง</span></b>
                    </div>
                    <div class="fl-fill holiday-text-head holiday-smallfont2">
                        <b><span>หมายเหตุ</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 210px;">
                        <b><span>Visit</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 200px;">
                        <b><span>โดย</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 80px;">
                        <b><span>สถานะ</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 80px;">
                        <b><span>ดูเอกสาร</span></b>
                    </div>
                </div>
            </div>
        </div>
        </div>';
?>

<div id="document_main" class="fl-wrap-col holiday-mt-0" style="min-width:1024;">
    <span class="data_defult" data-uid="<? echo $sUid; ?>" data-ss="<? echo $sSID; ?>" data-clinicid="<? echo $sClinicID; ?>" data-tempfile="<? echo $data_temp_file; ?>"  data-doctype="<? echo $doc_type; ?>" data-coldate="<? echo $sColDate; ?>" data-coltime="<? echo $sColTime; ?>" data-billid="<? echo $bill_id; ?>"></span>
    <!-- HEAD -->
    <? echo $head_gen1; ?>
    <? ($doc_type == "MEDICAL_C"? $_POST["type_special"] = "y": $_POST["type_special"] = ""); include("document_opt_doc_type.php"); ?>
    <? echo $head_gen2; ?>
    <? echo $head_gen3; ?>
    <!-- END HEAD -->

    <div class='fl-fill fl-auto holiday-ml-0 holiday-mr-1' id="document_show_data">
        <!-- Ajax reload data -->
    </div>
</div>

<script>
    $(document).ready(function(){
        // ประเภทเอกสาร event change
        $("#document_main #document_type").unbind("change");
        $("#document_main #document_type").on("change", function(){
            var doc_code = $(this).val();
            if(doc_code != null){
                var split_doc_code = doc_code.split(",");
            }
            else{
                $("#document_type").val("")
                var split_doc_code = "";
            }

            var sid_send = $("#document_main .data_defult").data("ss");
            var clinicid_send = $("#document_main .data_defult").data("clinicid");
            var uid_send = $("#document_main .data_defult").data("uid");
            var coldate_send = $("#document_main .data_defult").data("coldate");
            var coltime_send = $("#document_main .data_defult").data("coltime");
            var doctype = $("#document_main .data_defult").data("doctype");
            var billid = $("#document_main .data_defult").data("billid");

            var doc_type_code = split_doc_code[0];
            var bill_id = "";
            // list_data
            if(billid != ""){
                bill_id = billid;
                // uid_send = uid_send;
                // doc_type_code = "RECEIPT";
            }

            // condition buttom
            var aData = {
                doctype: doc_type_code,
                uid: uid_send,
                coldate: coldate_send,
                coltime: coltime_send,
                tempname_file: split_doc_code[1],
                billid: bill_id
            };

            $.ajax({url: "document_sys_create_bt.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#document_main #btn_check").children().remove();
                    $("#document_main #btn_check").append(result);
            }});

            var aData = {
                doctype: doc_type_code,
                uid: uid_send,
                billid: bill_id,
                coldate: coldate_send,
                coltime: coltime_send,
            };

            $.ajax({url: "document_sys_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#document_main #document_show_data").children().remove();
                    $("#document_main #document_show_data").append(result);
            }});
        });

        <? echo $sJS; ?>

        $("#document_main #document_uid").prop("readonly", true);
        $("#document_main #document_date").prop("readonly", true);
        $("#document_main #document_time").prop("readonly", true);
    });
</script>