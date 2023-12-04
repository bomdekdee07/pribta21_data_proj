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
    $doc_type = isset($_POST["doctype"])?$_POST["doctype"]: getQS("doctype");
    $uid = isset($_POST["uid"])?$_POST["uid"]: getQS("uid");
    $coldate = isset($_POST["coldate"])?$_POST["coldate"]: getQS("coldate");
    $coltime = isset($_POST["coltime"])?$_POST["coltime"]: getQS("coltime");
    $temp_name_file = isset($_POST["tempname_file"])?$_POST["tempname_file"]: getQS("tempname_file");
    $bill_id = isset($_POST["billid"])?$_POST["billid"]: getQS("billid");
    // echo $temp_name_file;

    $check_coldate_cur = true;
    // if($coldate != date("Y-m-d")){
    //     $check_coldate_cur = "false";
    // }

    $sJS = "";
    $sJS .= '<div id="document_sub_bt_create">
                <div class="data-defult" data-checkdate="'.$check_coldate_cur.'" data-doccode="'.$doc_type.'" data-clinicid="'.$sClinicID.'" data-ss="'.$sSID.'"  data-uid="'.$uid.'" data-coldate="'.$coldate.'" data-coltime="'.$coltime.'" data-tempfile="'.$temp_name_file.'" data-billid="'.$bill_id.'">
                ';
    if(isset($_SESSION["DOC"][$doc_type]["create"])){
        $sJS .= '<button type="button" id="document_new" class="btn smallfont2 holiday-add-btn"><b><i class="fa fa-plus-circle" aria-hidden="true"></i> สร้างเอกสารใหม่</b></button>';
    }
    else{
        $sJS .= '<span></span>';
    }
    $sJS .= '</div>';
    $sJS .= '</div>';
    
    echo $sJS;
?>

<script>
    $(document).ready(function(){
        $("#document_sub_bt_create #document_new").off("click");
        $("#document_sub_bt_create #document_new").on("click", function(){
            var d = new Date(Date.now());
            var month = d.getMonth()+1;
            var day = d.getDate();
            var year = d.getFullYear();

            var doc_code = $("#document_sub_bt_create .data-defult").data("doccode");
            var uid_send = $("#document_sub_bt_create .data-defult").data("uid");
            var coldate_send = $("#document_sub_bt_create .data-defult").data("coldate");
            var coltime_send = $("#document_sub_bt_create .data-defult").data("coltime");
            var sid_send = $("#document_sub_bt_create .data-defult").data("ss");
            var tempfile_send = $("#document_sub_bt_create .data-defult").data("tempfile");
            var tempfile_pdf = String($("#document_sub_bt_create .data-defult").data("tempfile"));
            var con_tempfile_pdf = tempfile_pdf.substr(0, tempfile_pdf.indexOf("_main"));
            var check_cur_date = $("#document_sub_bt_create .data-defult").data("checkdate");
            var bill_id = $("#document_sub_bt_create .data-defult").data("billid");

            var check_condition_old = false;
            var cur_date = year+"-"+(month<10 ? '0' : '') + month +"-"+(day<10 ? '0' : '')+day;
            if(coldate_send != cur_date)
                check_condition_old = true;
            
            // console.log(check_condition_old+":"+coldate_send+"/"+cur_date);
            
            var sUrl_appoint = tempfile_send+".php?uid="+uid_send+"&coldate="+coldate_send+"&coltime="+coltime_send+"&doctype="+doc_code+"&temp_file="+con_tempfile_pdf+"&billid="+bill_id;    
            
            // console.log(check_cur_date);
            if(check_cur_date != false){
                if(check_condition_old == true){
                    if (confirm('คุณต้องการสร้างเอกสารย้อนหลัง?')) {
                        showDialog(sUrl_appoint, "Document Master "+tempfile_send, "720", "1200", "", function(sResult){
                            var url_gen_doc = "document_sys_function.php?doctype="+doc_code+"&uid="+uid_send+"&billid="+bill_id+"&coldate="+coldate_send;
                            $("#document_show_data").load(url_gen_doc);
                        }, false, function(sResult){});
                    }
                }
                else{
                    showDialog(sUrl_appoint, "Document Master "+tempfile_send, "720", "1200", "", function(sResult){
                        var url_gen_doc = "document_sys_function.php?doctype="+doc_code+"&uid="+uid_send+"&billid="+bill_id+"&coldate="+coldate_send;
                        $("#document_show_data").load(url_gen_doc);
                    }, false, function(sResult){});
                }
            }
            else{
                alert("ไม่สามารถสร้างเอกสารใหม่ ได้เนื่องจากเป็นข้อมูลเก่า");
            }
        });
    });
</script>