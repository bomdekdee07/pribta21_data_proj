<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $u_mode = getQS("u_mode");
    $q_fwd = getQS("q");
    $fwdroom = getQS("fwdroom");
    $saleid = getQS("saleid");
    $projid = getQS("projid");
    $section_name = getQS("section_name");
    // echo $u_mode."/".$q_fwd;

    $js_bind_html = "";
    $js_bind_html .= '$("#queue_fwd_home").attr("data-mode", "'.$u_mode.'");';
    $js_bind_html .= '$("#queue_fwd_home").attr("data-queue", "'.$q_fwd.'");';
    $js_bind_html .= '$("#queue_fwd_home").attr("data-room", "'.$fwdroom.'");';
    $js_bind_html .= '$("#queue_fwd_home").attr("data-saleid", "'.$saleid.'");';
    $js_bind_html .= '$("#queue_fwd_home").attr("data-projid", "'.$projid.'");';
    $js_bind_html .= '$("#queue_fwd_home").attr("data-scname", "'.$section_name.'");';
    $js_bind_html .= '$("#queue_fwd_home [name=show_queue_gohome]").val("'.$q_fwd.'");';
    // echo $js_bind_html;
?>
<div class="fl-wrap-col" id="queue_fwd_home" data-mode="" data-queue="" data-room="" data-saleid="" data-projid="" data-scname="">
    <div class="fl-wrap-row h-5"></div>
    <div class="fl-wrap-col border-color-3" style="min-height: 350px; max-height: 350px;">
        <div class="fl-wrap-row h-30"></div>
        <div class="fl-wrap-row h-300">
            <div class="fl-fix w-50"></div>
            <div class="fl-fill fl-mid">
                <input type="text" class="font-s-10" name="show_queue_gohome" style="min-height: 300px; max-height: 300px; min-width: 300px; max-width: 300px; text-align: center; border-color:#F1948A; border-width: 3px;" readonly>
                <!-- <img src="assets/image/patient_fwd.png" alt="Girl in a jacket" width="300" height="300"> -->
            </div>
            <div class="fl-fix w-200 h-300 fl-mid">
                <img src="assets/image/go_home_fwd.jfif" alt="Girl in a jacket" width="200" height="200">
            </div>
            <div class="fl-fill fl-mid">
                <img src="assets/image/home_fwd.png" alt="Girl in a jacket" width="300" height="300">
            </div>
            <div class="fl-fix w-100"></div>
        </div>
    </div>
    <div class="fl-wrap-col border-color-2">
        <div class="fl-wrap-row">
            <div class="fl-fill fl-mid">
                <button class="font-s-8 bt-animation" name="bt_cf_queue_gohome" style="padding:72px 307px 72px 307px;">ยืนยัน</button>
            </div>
            <div class="fl-fill fl-mid">
                <button class="btn font-s-8" name="bt_cancel_queue_gohome" style="padding:72px 297px 72px 297px; background-color: #E74C3C; color:aliceblue;">ยกเลิก</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        <? echo $js_bind_html;?>

        $("#queue_fwd_home [name=bt_cf_queue_gohome]").off("click");
        $("#queue_fwd_home [name=bt_cf_queue_gohome]").on("click", function(){
            var sRoomNo = $("#queue_fwd_home").attr("data-room");

            if(sRoomNo!=undefined) {
                var sSaleId = $("#queue_fwd_home").attr("data-saleid");
                var sQ = $("#queue_fwd_home").attr("data-queue");
                var sprojid = $("#queue_fwd_home").attr("data-projid");
                var sSectionName = $("#queue_fwd_home").attr("data-scname");

                var aData = {
                    u_mode:"q_fwd",
                    q:sQ,
                    fwdroom:sRoomNo,
                    saleid:sSaleId,
                    projid: sprojid,
                    section_name: sSectionName
                };

                callAjax("queue_a.php",aData,function(rtnObj,aData){
                    if(rtnObj.res=="0"){
                        alert(rtnObj.msg);
                    }else{
                        var obj_this = $("#queue_fwd_home [name=bt_cf_queue_gohome]");
                        $.notify("Save Complete","success");
                        setDlgResult("ok", "");
                        setTimeout(close_dlg(obj_this), 2000);
                    }
                });
            }
        });

        $("#queue_fwd_home [name=bt_cancel_queue_gohome]").off("click");
        $("#queue_fwd_home [name=bt_cancel_queue_gohome]").on("click", function(){
            var obj_this = $(this);
            close_dlg(obj_this);
        });
    });

    function close_dlg(objthis){
        closeDlg(objthis, "0");
    }
</script>