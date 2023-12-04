<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");

    if($sSID == "" || $sClinicID == ""){
        exit("Please login again.");
    }

    $data_dashboard = array();
    $query = "SELECT st_list.supply_code,
        st_master.supply_name as name_dashboard,
        st_list.stock_lot,
        st_list.stock_amt,
        st_list.stock_exp_date as expri_date
    from i_stock_list st_list
    LEFT JOIN i_stock_master st_master ON(st_master.supply_code = st_list.supply_code)
    WHERE st_list.stock_exp_date <= (curdate()+ interval 31 day)
    and st_list.stock_exp_date > curdate()
    and st_list.clinic_id = ?
    and st_list.stock_amt > 0
    order by st_list.stock_exp_date;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $sClinicID);

    if($stmt->execute()){
        $stmt->bind_result($supply_code, $name_dashboard, $stock_lot, $stock_amt, $expri_date);
        while ($stmt->fetch()) {
            $data_dashboard[$supply_code]["code"] = $supply_code;
            $data_dashboard[$supply_code]["name"] = $name_dashboard;
            $data_dashboard[$supply_code]["lot"] = $stock_lot;
            $data_dashboard[$supply_code]["amt"] = $stock_amt;
            $data_dashboard[$supply_code]["exp_date"] = $expri_date;
        }
        // print_r($data_dashboard);
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    $sJS = "";
    if(count($data_dashboard) > 0){
        foreach($data_dashboard as $key => $value){
            $sJS .= '<div id="dashboard_detail" class="fl-wrap-row row-color h-25" style="margin-left: 2px; margin-top: 3px">';
            $sJS .=     '<div class="fl-fix holiday-smallfont2 holiday-ml-2" style="min-width: 35px;">';
            $sJS .=     '<b><i class="fa fa-check-square group-type-efit" style="color: blue;" aria-hidden="true" data-code="'.$value["code"].'" data-name="'.$value["name"].'" data-lot="'.$value["lot"].'" data-amt="'.$value["amt"].'" data-expdate="'.$value["exp_date"].'"></i></b>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2 check-type" style="min-width: 170px;">';
            $sJS .=         '<span>'.$value["code"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fill holiday-text-detail-left holiday-smallfont2 check-type">';
            $sJS .=         '<span>'.$value["name"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2 check-type" style="min-width: 170px;">';
            $sJS .=         '<span>'.$value["lot"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2 check-type" style="min-width: 170px;">';
            $sJS .=         '<span>'.$value["amt"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2 check-type" style="min-width: 200px;">';
            $sJS .=         '<span>'.$value["exp_date"].'</span>';
            $sJS .=     '</div>';
            $sJS .= '</div>';
        }
    }

    echo $sJS;
?>