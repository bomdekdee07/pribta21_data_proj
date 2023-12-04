<?
    include("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $sToday = date("Y-m-d");
    $sVisitDate = getQS("vdate",$sToday);

    // Query HCV NHSO
    $bind_param = "s";
    $array_val = array($sVisitDate);
    $data_hcv_val = array();

    $query = "SELECT main.uid,
        main.collect_date,
        main.collect_time,
        service.data_result AS service_clinic
    from p_data_result main
    left join p_data_result service on(service.uid = main.uid and service.collect_date = main.collect_date and service.collect_time = main.collect_time and service.data_id = 'service_clinic')
    where main.collect_date = ?
    and main.data_id = 'serv_coun_tm_hcv'
    and main.data_result = '1';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_hcv_val[$row["uid"]] = $row;
        }
    }
    $stmt->close();

    // Query HCV refer
    $bind_param = "s";
    $array_val = array($sVisitDate);
    $data_hcvRefer_val = array();

    $query = "SELECT main.uid,
        main.collect_date,
        main.collect_time,
        service.data_result AS service_clinic
    from p_data_result main
    left join p_data_result service on(service.uid = main.uid and service.collect_date = main.collect_date and service.collect_time = main.collect_time and service.data_id = 'service_clinic')
    where main.collect_date = ?
    and main.data_id = 'cbo_refer_reason_hcv'
    and main.data_result = '1';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_hcvRefer_val[$row["uid"]] = $row;
        }
    }
    $stmt->close();

    // Query HCV Paotang
    $bind_param = "s";
    $array_val = array($sVisitDate);
    $data_paotang_val = array();

    $query = "SELECT main.uid,
        main.collect_date,
        main.collect_time,
        service.data_result AS service_clinic
    from p_data_result main
    left join p_data_result service on(service.uid = main.uid and service.collect_date = main.collect_date and service.collect_time = main.collect_time and service.data_id = 'service_clinic')
    where main.collect_date = ?
    and main.data_id = 'paotang_refer_hcv'
    and main.data_result = '1';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_paotang_val[$row["uid"]] = $row;
        }
    }
    $stmt->close();
    $mysqli->close();

    $selDate = date_create($sVisitDate);
    $showDate = date_format($selDate,"d/m/Y");
    $html_detail_hcv = "";
    $hcv_count = 0;

    // HCV NHSO
    foreach($data_hcv_val as $key_date => $val_hcv){
        if($val_hcv["service_clinic"] == "1" || $val_hcv["service_clinic"] == "2"){
            $html_detail_hcv .= '<div class="row-color row-hover"> 
                                <div class="fl-wrap-row h-25">
                                    <div class="fl-fix w-5"></div>
                                    <div class="fl-fill fl-mid-left">'.$val_hcv["uid"].'/ HCV Treatment</div>
                                </div>
                            </div>';
            $hcv_count++;
        }
    }

    $html_head_hcv = "";
    $html_head_hcv .= " <div class='row-color-2'>
                        <div class='fl-wrap-row h-30'>
                            <div class='fl-fix w-5'></div>
                            <div class='fl-fill fl-mid-left'>วันที่ $showDate จำนวน ".($hcv_count)." ราย</div>
                        </div>
                    </div>";

    // HCV refer
    $html_detail_hcvRfer = "";
    $hcv_count_refer = 0;

    foreach($data_hcvRefer_val as $key_date => $val_hcvRfer){
        if($val_hcvRfer["service_clinic"] == "1" || $val_hcvRfer["service_clinic"] == "2"){
            $html_detail_hcvRfer .= '<div class="row-color row-hover"> 
                                <div class="fl-wrap-row h-25">
                                    <div class="fl-fix w-5"></div>
                                    <div class="fl-fill fl-mid-left">'.$val_hcvRfer["uid"].'/ HCV refer</div>
                                </div>
                            </div>';
            $hcv_count_refer++;
        }
    }

    $html_head_hcv_refer = "";
    $html_head_hcv_refer .= " <div class='row-color-2'>
                        <div class='fl-wrap-row h-30'>
                            <div class='fl-fix w-5'></div>
                            <div class='fl-fill fl-mid-left'>วันที่ $showDate จำนวน ".($hcv_count_refer)." ราย</div>
                        </div>
                    </div>";

    // HCV Paotang
    $html_detail_hcvPaotang = "";
    $hcv_count_paotang = 0;

    foreach($data_paotang_val as $key_date => $val_hcvpaotang){
        if($val_hcvpaotang["service_clinic"] == "1" || $val_hcvpaotang["service_clinic"] == "2"){
            $html_detail_hcvPaotang .= '<div class="row-color row-hover"> 
                                <div class="fl-wrap-row h-25">
                                    <div class="fl-fix w-5"></div>
                                    <div class="fl-fill fl-mid-left">'.$val_hcvpaotang["uid"].'/ HCV Paotang</div>
                                </div>
                            </div>';
            $hcv_count_paotang++;
        }
    }

    $html_head_hcv_paotang = "";
    $html_head_hcv_paotang .= " <div class='row-color-2'>
                        <div class='fl-wrap-row h-30'>
                            <div class='fl-fix w-5'></div>
                            <div class='fl-fill fl-mid-left'>วันที่ $showDate จำนวน ".($hcv_count_paotang)." ราย</div>
                        </div>
                    </div>";

    // Total
    $sHtml= "<div class='fl-fix h-25'></div>
            <div class='fl-wrap-row h-25'><div class='fl-fill w-200'>HCV NHSO : $hcv_count ราย</div></div>
            <div class='fl-wrap-row h-25'><div class='fl-fill w-200'>HCV refer : $hcv_count_refer ราย</div></div>
            <div class='fl-wrap-row h-25'><div class='fl-fill w-200'>HCV refer : $hcv_count_paotang ราย</div></div>
            <div class='fl-wrap-row h-25'><div class='fl-fill w-200 fw-b'>Total : ".($hcv_count+$hcv_count_refer+$hcv_count_paotang)." ราย</div></div>";

?>

<div class="fl-wrap-row fs-smaller">
    <div class="fl-wrap-col fl-auto" style='border-right: 2px solid white'>
        <div class="fl-wrap-row h-30 fw-b">
            <div class="fl-fill h-30 fl-mid bg-head-1">HCV NHSO</div>
        </div>
        <? echo $html_head_hcv; ?> 
        <? echo $html_detail_hcv; ?>
    </div>

    <div class="fl-wrap-col fl-auto" style='border-right: 2px solid white'>
        <div class="fl-wrap-row h-30 fw-b">
            <div class="fl-fill h-30 fl-mid bg-head-1">HCV refer</div>
        </div>
        <? echo $html_head_hcv_refer; ?> 
        <? echo $html_detail_hcvRfer; ?>
    </div>

    <div class="fl-wrap-col fl-auto" style='border-right: 2px solid white'>
        <div class="fl-wrap-row h-30 fw-b">
            <div class="fl-fill h-30 fl-mid bg-head-1">HCV Paotang</div>
        </div>
        <? echo $html_head_hcv_paotang; ?> 
        <? echo $html_detail_hcvPaotang; ?>
    </div>
</div>
<div class='fl-wrap-col h-180 fl-auto fs-smaller'>
    <? echo($sHtml);	?>
</div>