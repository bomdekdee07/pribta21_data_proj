<?
    include_once("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $sUid = getQS("uid");
    $sColDate = getQS("coldate");
    $sColTime = getQS("coltime");
    // echo $sUid."/".$sColDate;
    
    // $sUid = "P18-00075";
    // $sColDate = "2020-12-25";
    // $sColTime = "10:49:26";

    $medicine_data = array();
    $total_amt_all = 0;
    $supply_code_old = "";
    $query = "SELECT ISOD.supply_code,
	ISOD.order_by,
	ISOD.dose_per_time,
	ISOD.dose_before,
	ISOD.dose_breakfast,
	ISOD.dose_lunch,
	ISOD.dose_dinner,
	ISOD.dose_night,
	ISOD.dose_day as total_amt,
	ISOD.order_note,
	ISOD.supply_desc,
    ISTMT.supply_name,
	ISTMT.dose_note,
	ISTMT.supply_unit
    from i_stock_order ISOD
    left join i_stock_master ISTMT on(ISTMT.supply_code = ISOD.supply_code)
    left join i_stock_group ISTG on(ISTG.supply_group_code = ISTMT.supply_group_code)
    where collect_date = ?
    and collect_time = ?
    and uid = ?
    and order_status != 'C'
    and ISTG.supply_group_type in (1, 9)
    order by order_status;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss', $sColDate, $sColTime, $sUid);

    if($stmt->execute()){
        $stmt->bind_result($supply_code,$order_by,$dose_per_time,$dose_before,$dose_breakfast,$dose_lunch,$dose_dinner,$dose_night,$total_amt,$order_note, $supply_desc, $supply_name, $dose_note,$supply_unit);
        while ($stmt->fetch()) {
            $medicine_data[$supply_code]["supply_code"] = $supply_code;
            $medicine_data[$supply_code]["order_by"] = $order_by;
            $medicine_data[$supply_code]["dose_per_time"] = $dose_per_time;
            $medicine_data[$supply_code]["dose_before"] = $dose_before;
            $medicine_data[$supply_code]["dose_breakfast"] = $dose_breakfast;
            $medicine_data[$supply_code]["dose_lunch"] = $dose_lunch;
            $medicine_data[$supply_code]["dose_dinner"] = $dose_dinner;
            $medicine_data[$supply_code]["dose_night"] = $dose_night;
            
            if($supply_code_old == $supply_code){
                $total_amt_all = $total_amt+$total_amt_all;
            }
            else{
                $total_amt_all = $total_amt;
            }
            $medicine_data[$supply_code]["total_amt"] = $total_amt_all;
            $medicine_data[$supply_code]["order_note"] = urlDecode($order_note);
            $medicine_data[$supply_code]["supply_name"] = $supply_name;
            $medicine_data[$supply_code]["dose_note"] =  urlDecode($dose_note);
            $medicine_data[$supply_code]["supply_unit"] = $supply_unit;
            $medicine_data[$supply_code]["supply_desc"] = $supply_desc;

            $supply_code_old = $supply_code;
        }
        // print_r($medicine_data);
    }

    $stmt->close();
    $mysqli->close();

    $temp_total_dose = "";
    $sJS_medicine = "";

    $sJS_medicine .=    '<div class="fl-wrap-col" id="medicine_total_price.php">
                            <div class="fl-wrap-row fw-b h-30 fs-smaller border" style="background-color: #D6B5E2;">
                                <div class="fl-fix wper-5 fl-mid"></div>
                                <div class="fl-fix wper-75 fl-mid-left">
                                    <span>ชื่อ</span>
                                </div>
                                <div class="fl-fix wper-10 fl-mid-left">
                                    <span>จำนวน</span>
                                </div>
                                <div class="fl-fill fl-mid"></div>
                            </div>
                            
                            <div class="fl-wrap-col fl-auto h-235">';

    foreach($medicine_data as $key => $val){
        $temp_total_dose = $val["dose_breakfast"]+$val["dose_lunch"]+$val["dose_dinner"]+$val["dose_night"];

        if($temp_total_dose == "0" || $temp_total_dose == ""){
            $temp_total_dose = $val["supply_desc"];
        }
        else{
            $temp_total_dose = $val["supply_desc"];

            if($val["dose_breakfast"]=="0" && $val["dose_lunch"]=="0" && $val["dose_dinner"]=="0" && $val["dose_night"]=="1" ){}
            else{
                if($val["dose_before"]=="A"){ 
                    $temp_total_dose .= ' หลังอาหาร'; 
                }
                else if($val["dose_before"]=="B")
                { 
                    $temp_total_dose .= ' ก่อนอาหาร'; 
                }
            }

            if($val["dose_breakfast"]=="1") $temp_total_dose .= "เช้า";
            if($val["dose_lunch"]=="1") $temp_total_dose .= "กลางวัน";
            if($val["dose_dinner"]=="1") $temp_total_dose .= "เย็น";
            if($val["dose_night"]=="1") $temp_total_dose .= "ก่อนนอน";
        }

        $sJS_medicine .=        '<div class="fl-wrap-row h-40 fs-smaller row-color">
                                    <div class="fl-fix wper-5 fl-mid"></div>
                                    <div class="fl-wrap-col wper-75">
                                        <div class="fl-fill fl-mid-left fw-b">
                                            <span>'.$val["supply_name"].'</span>
                                        </div>
                                        <div class="fl-fill fl-mid-left fs-xsmall">
                                            <span>'.$temp_total_dose.'</span>
                                        </div>
                                    </div>
                                    <div class="fl-fix wper-10 fl-mid-left fw-b">
                                        <span class="holiday-mr-3">'.$val["total_amt"].'</span>
                                    </div>
                                    <div class="fl-fill fl-mid-left fw-b">
                                        <span class="holiday-mr-6">'.$val["supply_unit"].'</span>
                                    </div>
                                </div>';
    }

    $sJS_medicine .=        '</div>
                        </div>';

    echo $sJS_medicine;
?>