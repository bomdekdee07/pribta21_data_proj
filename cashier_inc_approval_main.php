<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $sUid = getQS("uid");
    $sColdate = getQS("coldate");
    $sColtime = getQS("coltime");
    $sSid = getSS("s_id");

    $bind_param = "sss";
    $array_val = array($sUid, $sColdate, $sColtime);
    // print_r($array_val);
    $data_statusBillCash = "";
    $data_updateNow = "";

    $query = "SELECT status,
        update_date
    from i_bill_cancel_approve 
    where uid = ?
    and collect_date = ?
    and collect_time = ?
    order by date_now;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_statusBillCash = $row["status"];
            $data_updateNow = $row["update_date"];
        }
        // $data_statusBillCash;
    }

    $Html_bindHead = "";
    $Html_bindHead .= '<div class="fl-wrap-col doctor-billCashApproval" style="min-height: 55px; max-height: 55px;" 
                            data-uid="'.$sUid.'"
                            data-coldate="'.$sColdate.'"
                            data-coltime="'.$sColtime.'"
                            data-sid="'.$sSid.'"
                            data-status="'.$data_statusBillCash.'"
                            data-updatenow="'.$data_updateNow.'">';
    $Html_bindEndHead = "";
    $Html_bindEndHead .= '</div>';
    $Html_bindWaitApprove = "";
    $Html_bindWaitApprove .=    '<div class="fl-wrap-row" style="min-height: 20px; max-height: 20px;">
                                    <div class="fl-fill fl-mid-left font-s-2 fw-b">
                                        แจ้งเตือนการยกเลิกใบเสร็จ
                                    </div>
                                </div>
                                <div class="fl-wrap-row" style="min-height: 30px; max-height: 30px;">
                                    <div class="fl-fill fl-mid-left">
                                        <button id="BtApproveBillCash" name="BtApproveBillCash" class="btn btn-success btn-AllBillCash" style="padding: 0px 7px 0px 7px;">Approve</button>
                                    </div>
                                    <div class="fl-fix w-10"></div>
                                    <div class="fl-fill fl-mid-left">
                                        <button id="BtRejectBillCash" name="BtRejectBillCash" class="btn btn-danger btn-AllBillCash" style="padding: 0px 7px 0px 7px;">Reject</button>
                                    </div>
                                </div>';

    $Html_bindApprove = "";
    $Html_bindApprove .= '      <div class="fl-wrap-row" style="min-height: 20px; max-height: 20px;">
                                    <div class="fl-fill fl-mid-left font-s-2 fw-b">
                                        กำลังดำเนินการ...
                                    </div>
                                </div>
                                <div class="fl-wrap-row" style="min-height: 30px; max-height: 30px;">
                                    <div class="fl-fill fl-mid-left">
                                        <button id="BtCompleteBillCash" name="BtCompleteBillCash" class="btn btn-success btn-AllBillCash" style="padding: 0px 7px 0px 7px;">เสร็จสิ้น</button>
                                    </div>
                                    <div class="fl-fix w-10"></div>
                                    <div class="fl-fill fl-mid-left">
                                        <button id="btnAddDrug" name="btnAddDrug" class="btn btn-danger" style="padding: 0px 7px 0px 7px;">แก้ไขรายการ</button>
                                    </div>
                                </div>';

    $Html_bindComplete = "";
    $Html_bindComplete .= '     <div class="fl-wrap-row" style="min-height: 20px; max-height: 20px;">
                                    <div class="fl-fill fl-mid-left font-s-2 fw-b">
                                        ดำเนินการแก้ไขใบเสร็จเสร็จสิ้น
                                    </div>
                                </div>
                                <div class="fl-wrap-row" style="min-height: 30px; max-height: 30px;">
                                    <div class="fl-fill fl-mid-left">
                                        <button id="BtTextBillCash" name="BtCompleteBillCash" class="btn btn-success btn-AllBillCash" style="padding: 0px 7px 0px 7px;">'.$data_updateNow.'</button>
                                    </div>
                                </div>';

    //Condition Approval detail
    $Html_bindDetail = "";
    if($data_statusBillCash == "W"){
        $Html_bindDetail = $Html_bindWaitApprove;
    }
    else if($data_statusBillCash == "A"){
        $Html_bindDetail = $Html_bindApprove;
    }
    else if($data_statusBillCash == "F"){
        $Html_bindDetail = $Html_bindComplete;
    }
    else if($data_statusBillCash == "C"){
        $Html_bindHead = ""; 
        $Html_bindEndHead = "";
    }

    //Bind Html All
    if($data_statusBillCash != "")
        echo $Html_bindHead.$Html_bindDetail.$Html_bindEndHead;
?>

<script>
    $(document).ready(function(){
        $("#bill_cash_approval .btn-AllBillCash").off("click");
        $("#bill_cash_approval .btn-AllBillCash").on("click", function(){
            var get_idBt = $(this).attr("id");
            console.log(get_idBt);
            var sSid = $("#bill_cash_approval .doctor-billCashApproval").data("sid");
            var sUid = $("#bill_cash_approval .doctor-billCashApproval").data("uid");
            var sColdate = $("#bill_cash_approval .doctor-billCashApproval").data("coldate");
            var sColtime = $("#bill_cash_approval .doctor-billCashApproval").data("coltime");
            var d = new Date();
            var month = d.getMonth()+1;
            var day = addZero(d.getDate());
            var h = addZero(d.getHours());
            var m = addZero(d.getMinutes());
            var s = addZero(d.getSeconds());
            var sDate_now = d.getFullYear() + '-' +(month<10 ? '0' : '') + month + '-' +(day<10 ? '0' : '') + day + " " + h + ":" + m + ":" +s;

            if(get_idBt == "BtApproveBillCash"){
                if (confirm("คุณแน่ใจหรือไม่?")){
                    var aData = {
                        mode: "approve",
                        uid: sUid,
                        coldate: sColdate,
                        coltime: sColtime,
                        sid: sSid,
                        date_now: sDate_now
                    }
                    // console.log(aData);
                    
                    $.ajax({
                        url: "cashier_inc_ajax_insup.php",
                        cache: false,
                        type: "POST",
                        data: aData,
                        success: function(sResult){
                            if(sResult == ""){
                                var url_gen = "cashier_inc_approval_main.php?uid="+sUid+"&coldate="+sColdate+"&coltime="+sColtime;
                                $("#bill_cash_approval").load(url_gen, function(){});
                            }
                        }
                    })
                }
            }
            else if(get_idBt == "BtCompleteBillCash"){
                if (confirm("คุณแน่ใจหรือไม่?")){
                    var aData = {
                        mode: "complete",
                        uid: sUid,
                        coldate: sColdate,
                        coltime: sColtime,
                        sid: sSid,
                        date_now: sDate_now
                    }
                    // console.log(aData);
                    
                    $.ajax({
                        url: "cashier_inc_ajax_insup.php",
                        cache: false,
                        type: "POST",
                        data: aData,
                        success: function(sResult){
                            if(sResult == ""){
                                var url_gen = "cashier_inc_approval_main.php?uid="+sUid+"&coldate="+sColdate+"&coltime="+sColtime;
                                $("#bill_cash_approval").load(url_gen, function(){});
                            }
                        }
                    })
                }
            }
            else if(get_idBt == "BtRejectBillCash"){
                if (confirm("คุณแน่ใจหรือไม่?")){
                    var aData = {
                        mode: "reject",
                        uid: sUid,
                        coldate: sColdate,
                        coltime: sColtime,
                        sid: sSid,
                        date_now: sDate_now
                    }
                    // console.log(aData);
                    
                    $.ajax({
                        url: "cashier_inc_ajax_insup.php",
                        cache: false,
                        type: "POST",
                        data: aData,
                        success: function(sResult){
                            if(sResult == ""){
                                var url_gen = "cashier_inc_approval_main.php?uid="+sUid+"&coldate="+sColdate+"&coltime="+sColtime;
                                $("#bill_cash_approval").load(url_gen, function(){});
                            }
                        }
                    })
                }
            }
        });
    });

    function addZero(i) {
        if (i < 10) {
            i = "0" + i;
        }
        return i;
    }
</script>