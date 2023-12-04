<?
    include("in_db_conn.php");
    include_once("in_php_function.php");
    include_once("in_session.php");

    $sClinicID = getSS("clinic_id") != ""?getSS("clinic_id") : getQS("clinic_id");
    $doc_group_code = getQS("doc_group");
    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = getQS("coltime");
    $sSID = getSS("s_id")!= ""? getSS("s_id") : getQS("s_id");
    $bill_id = getQS("billid");

    $uid_new = $sUid;
    if($bill_id != ""){
        $sUid = preg_replace("/[^A-Za-z0-9ก-๙เแ\-.]/", '', $bill_id);
    }

    $data_test = array("doc_name" => "", "doc_template_file" => "", "doc_code" => "", "uid" => "", "doc_datetime" => "", "file_name" => "");
    $query = "select main.doc_name, 
        main.doc_template_file,
        date_last.doc_code,
        date_last.uid,
        date_last.doc_datetime
    from i_doc_master_list main
    left join i_doc_list date_last on(main.doc_code = date_last.doc_code and date_last.uid = ?)
    where main.doc_code = ?
    and main.clinic_id = ?
    order by date_last.doc_datetime DESC
    LIMIT 1;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss', $sUid, $doc_group_code, $sClinicID);

    if($stmt->execute()){
        $stmt->bind_result($doc_name, $doc_template_file, $doc_code, $uid, $doc_datetime);
        while ($stmt->fetch()) {
            $data_test["doc_name"] = $doc_name;
            $data_test["doc_template_file"] = $doc_template_file;
            $data_test["doc_code"] = $doc_code;
            $data_test["uid"] = $uid;
            $data_test["doc_datetime"] = $doc_datetime;

            if($doc_datetime != "")
            $data_test["file_name"] = $doc_code."_".$uid."_".preg_replace("/[^A-Za-z0-9ก-๙เแ\.]/", "", $doc_datetime);
        }	
    }

    $stmt->close();
    $mysqli->close();

    // print_r($_SESSION["DOC"]);
    // echo $doc_group_code."/".$sSID;
    // echo ":".isset($_SESSION["DOC"][$doc_group_code]["view"]);
    $check_coldate_cur = false;
    $check_view = false;

    if($data_test["file_name"] != ""){
        if(isset($_SESSION["DOC"][$doc_group_code]["view"])){
            $check_view = true;
        }
    }

    if($sColDate == date("Y-m-d") ){
        if(isset($_SESSION["DOC"][$doc_group_code]["create"])){
            $check_coldate_cur = true;
        }
    }

    $sJS_doc_bt = "";
    $sJS_doc_bt .= '<div class="fl-wrap-row h-40" id="document_bt_main_sub" data-uid="'.$uid_new.'" data-ss="'.$sSID.'" data-clinicid="'.$sClinicID.'" data-doctype="'.$doc_group_code.'" data-coldate="'.$sColDate.'" data-coltime="'.$sColTime.'" data-tempfile="'.($data_test["doc_template_file"] != ""?$data_test["doc_template_file"]:"").'" data-billid="'.$bill_id.'">';
    
    if($check_coldate_cur){
        $sJS_doc_bt .= '<div class="fl-wrap-col document-bt-add w-50 fabtn fl-mid" id="document_bt_add">
            <i class="fa fa-plus-circle fa-lg fw-b" style="color: #58FF33;" aria-hidden="true"></i>
        </div>';
    }

    $sJS_doc_bt .= '<div class="fl-wrap-col document-bt" id="document_bt">
        <div class="fl-fill fw-b">
            <span>'.$data_test["doc_name"].'</span>
        </div>
        <div class="fl-fill fs-smaller">
            <span>'.($data_test["doc_datetime"] != "" ?$data_test["doc_datetime"]: "Not found document").'</span>
        </div>
    </div>';

    // echo $check_view;
    if($check_view)
    $sJS_doc_bt .= '<div class="fl-wrap-col document-bt-view fabtn fl-mid fw-b w-50" id="document_bt_view" data-name="'.($data_test["file_name"] != ""?$data_test["file_name"]:"").'">
        <i class="fa fa-search fa-lg" style="color: #33F3FF;" aria-hidden="true"></i>
    </div>';
    
    $sJS_doc_bt .= '</div>';

    echo $sJS_doc_bt;
?>

<script>
    $(document).ready(function(){
        $("#document_bt_main_sub #document_bt_view").off("click");
        $("#document_bt_main_sub #document_bt_view").click(function(){
            var file_name = $(this).data("name");
            
            if(file_name != ""){
                var gen_link = "pdfoutput/"+file_name+".pdf";
                window.open(gen_link,'_blank');
            }
        });

        $("#document_bt_main_sub #document_bt_add").off("click");
        $("#document_bt_main_sub #document_bt_add").click(function(){
            var row_data = $(this).closest("#document_bt_main_sub");
            var obj = $(this);

            var uid_send = $(row_data).data("uid");
            var col_date = $(row_data).data("coldate");
            var col_time = $(row_data).data("coltime");
            var doc_type =  $(row_data).data("doctype");
            var clinic_id =  $(row_data).data("clinicid");
            var billid = $(row_data).data("billid");
            if(billid != ""){
                if(billid.toString().indexOf("/") > 0)
                billid = billid.substring(0, 4)+""+billid.substring(5);
            }
            var tempfile =  $(row_data).data("tempfile");
            var sid =  $(row_data).data("ss");
            var sUrl_appoint = tempfile+".php?uid="+uid_send+"&coldate="+col_date+"&coltime="+col_time+"&doctype="+doc_type+"&billid="+billid;

            showDialog(sUrl_appoint, "Document Management", "500", "1200", "", function(sResult){
                var billid = $(row_data).data("billid");
                var url_gen_doc = "document_sys_bt.php?clinic_id="+clinic_id+"&doc_group="+doc_type+"&uid="+uid_send+"&coldate="+col_date+"&coltime="+col_time+"&s_id="+sid+"&billid="+billid+"&doctype="+doc_type;

                obj.closest("#document_bt_main_sub").parent().load(url_gen_doc);
            }, false, function(sResult){});
        });

        $("#document_bt_main_sub #document_bt").off("click");
        $("#document_bt_main_sub #document_bt").click(function(){
            var obj = $(this);
            var row_data = $(this).closest("#document_bt_main_sub");
            var uid_send = $(row_data).data("uid");
            var col_date = $(row_data).data("coldate");
            var col_time = $(row_data).data("coltime");
            var doc_type =  $(row_data).data("doctype");
            var clinic_id = $(row_data).data("clinicid");
            var billid = $(row_data).data("billid");
            var sid =  $(row_data).data("ss");
            var sUrl_appoint = "document_sys_main.php?uid="+uid_send+"&coldate="+col_date+"&coltime="+col_time+"&doctype="+doc_type+"&billid="+billid;

            showDialog(sUrl_appoint, "Document Management", "500", "1200", "", function(sResult){
                var url_gen_doc = "document_sys_bt.php?clinic_id="+clinic_id+"&doc_group="+doc_type+"&uid="+uid_send+"&coldate="+col_date+"&coltime="+col_time+"&s_id="+sid+"&billid="+billid;

                obj.closest("#document_bt").parent().load(url_gen_doc);
            }, false, function(sResult){});
        });
    });
</script>