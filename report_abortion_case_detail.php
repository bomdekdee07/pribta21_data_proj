<?
    include("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $sToday = date("Y-m-d");
    $sVisitDate = getQS("vdate",$sToday);

    // Query HCV NHSO
    $bind_param = "s";
    $array_val = array($sVisitDate);
    $data_abortion_val = array();

    $query = "SELECT main.uid,
        main.collect_date,
        main.collect_time,
        service.data_result AS service_clinic,
        receive.data_result AS receive_abort
    from p_data_result main
    left join p_data_result service on(service.uid = main.uid and service.collect_date = main.collect_date and service.collect_time = main.collect_time and service.data_id = 'service_clinic')
    left join p_data_result receive on(receive.uid = main.uid and receive.collect_date = main.collect_date and receive.collect_time = main.collect_time and receive.data_id = 'receive_abort')
    where main.collect_date = ?
    and main.data_id = 'serv_coun_abort'
    and main.data_result = '1';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_abortion_val[$row["uid"]] = $row;
        }
    }
    $stmt->close();
    $mysqli->close();

    $selDate = date_create($sVisitDate);
    $showDate = date_format($selDate,"d/m/Y");
    $html_detail_abortion = "";
    $abortion_count = 0;
    $receive_abortion = "";

    // abortion
    foreach($data_abortion_val as $key_date => $val_abortion){
        if($val_abortion["receive_abort"] != ""){
            if($val_abortion["receive_abort"] == "Y"){
                $receive_abortion = "ได้รับยา";
            }
            else{
                $receive_abortion = "ไม่ได้รับยา";
            }
        }
        else{
            $receive_abortion = "ไม่พบข้อมูล";
        }

        if($val_abortion["service_clinic"] == "1" || $val_abortion["service_clinic"] == "2"){
            $html_detail_abortion .= '<div class="row-color row-hover"> 
                                <div class="fl-wrap-row h-25">
                                    <div class="fl-fix w-5"></div>
                                    <div class="fl-fill fl-mid-left">'.$val_abortion["uid"].'/ Abortion Case/ '.$receive_abortion.'</div>
                                </div>
                            </div>';
            $abortion_count++;
        }
    }

    $html_head_abortion = "";
    $html_head_abortion .= " <div class='row-color-2'>
                        <div class='fl-wrap-row h-30'>
                            <div class='fl-fix w-5'></div>
                            <div class='fl-fill fl-mid-left'>วันที่ $showDate จำนวน $abortion_count ราย</div>
                        </div>
                    </div>";

    // Total
    $sHtml= "<div class='fl-fix h-25'></div>
            <div class='fl-wrap-row h-25'><div class='fl-fill w-200'>Abortion Case : $abortion_count ราย</div></div>
            <div class='fl-wrap-row h-25'><div class='fl-fill w-200 fw-b'>Total : ".($abortion_count)." ราย</div></div>";
?>

<div class="fl-wrap-row fs-smaller">
    <div class="fl-wrap-col fl-auto" style='border-right: 2px solid white'>
        <div class="fl-wrap-row h-30 fw-b">
            <div class="fl-fill h-30 fl-mid bg-head-1">Abortion Case</div>
        </div>
        <? echo $html_head_abortion; ?> 
        <? echo $html_detail_abortion; ?>
    </div>
</div>
<div class='fl-wrap-col h-180 fl-auto fs-smaller'>
    <? echo($sHtml);	?>
</div>