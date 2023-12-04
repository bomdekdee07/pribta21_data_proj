<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");

    if($sSID == "" || $sClinicID == ""){
        exit("Please login again.");
    }

    $data_request = array();
    $query = "select request_id,
    request_title,
    request_datetime,
    case 
        when request_status = '00' then 'รอยื่นคำขอ'
        when request_status = '01' then 'รอการยืนยัน'
        when request_status = 'CF' then 'ยืนยันรอสินค้าเข้า'
        when request_status = 'FIN' then 'เสร็จสิ้น'
        when request_status = 'CC' then 'ยกเลิก'
        end status
    from i_stock_request_list
    where clinic_id = ?
    order by status, request_datetime DESC;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $sClinicID);

    if($stmt->execute()){
        $stmt->bind_result($request_id, $request_title, $request_datetime, $status);
        while ($stmt->fetch()) {
            $data_request[$request_id]["req_id"] = $request_id;
            $data_request[$request_id]["title"] = $request_title;
            $data_request[$request_id]["date"] = $request_datetime;
            $data_request[$request_id]["status"] = $status;
        }
        // print_r($data_request);
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    $sJS = "";
    if(count($data_request) > 0){
        foreach($data_request as $key => $value){
            $sJS .= '<div id="req_items_detail" class="fl-wrap-row row-color h-25 row-hover" style="margin-left: 2px; margin-top: 3px">';
            $sJS .=     '<div class="fl-fix holiday-smallfont2 holiday-ml-1" style="min-width: 160px;">';
            $sJS .=         '<a href="#"><b><i class="fa fa-wrench reqitem-edit" style="color: blue;" aria-hidden="true" data-reqid="'.$value["req_id"].'" data-title="'.$value["title"].'" data-date="'.$value["date"].'" data-status="'.$value["status"].'"></i></b></a>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2 check-type" style="min-width: 220px;">';
            $sJS .=         '<span>'.$value["req_id"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fill holiday-text-detail-left holiday-smallfont2 check-type">';
            $sJS .=         '<span>'.$value["title"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2 check-type" style="min-width: 170px;">';
            $sJS .=         '<span>'.$value["date"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2 check-type" style="min-width: 170px;">';
            $sJS .=         '<span>'.$value["status"].'</span>';
            $sJS .=     '</div>';
            $sJS .= '</div>';             
        }
    }

    echo $sJS;
?>
<script>
    $(document).ready(function(){
        $("#req_items_detail .reqitem-edit").unbind("click");
        $("#req_items_detail .reqitem-edit").on("click", function(){
            var req_id = $(this).data("reqid");
            var req_id_sub = $(this).data("reqid").substring(0, 1);
            if(req_id_sub == "S"){
                var sUrl_appoint = "supply_req_inc_main.php?request_id="+req_id;
            }
            else{
                var sUrl_appoint = "purchase_req_inc_main.php?request_id="+req_id;
            }

            showDialog(sUrl_appoint, "Supply Group Main", "90%", "1200", "", function(sResult){
                var url_gen = "supply_request_items_function.php";

                $("#req_items_detail").parent().load(url_gen);
            }, false, function(sResult){});
        });
    });
</script>