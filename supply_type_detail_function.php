<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");
    $type_code = isset($_POST["type_code"])?$_POST["type_code"]:getQS("type_code");
    $mode = isset($_POST["mode"])?$_POST["mode"]:getQS("mode");

    if($sSID == "" || $sClinicID == ""){
        exit("Please login again.");
    }

    $data_request = array();
    $query = "SELECT 
        JSSO.supply_code,
        st_master.supply_name,
        JSSO.stock_lot,
        st_master.supply_desc,
        JSSO.stock_amt,
        st_master.supply_unit,
        JSSO.stock_exp_date,
        JSSO.stock_added_datetime,
        st_group.supply_group_code,
        JSSO.stock_cost
    from i_stock_list JSSO
    left join i_stock_master st_master on(st_master.supply_code = JSSO.supply_code)
    left join i_stock_group st_group on(st_group.supply_group_code = st_master.supply_group_code)
    left join i_stock_type st_type on(st_type.supply_group_type = st_group.supply_group_type)
    where st_group.supply_group_type = ?
    order by JSSO.stock_exp_date, JSSO.supply_code;";

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $type_code);

    if($stmt->execute()){
        $stmt->bind_result($supply_code, $supply_name, $supply_lot, $supply_desc, $stock_amt, $supply_unit, $stock_exp_date, $stock_added_datetime, $supply_group_code, $stock_cost);
        while ($stmt->fetch()) {
            $data_request[$stock_exp_date.$supply_code][$supply_lot]["sup_code"] = $supply_code;
            $data_request[$stock_exp_date.$supply_code][$supply_lot]["sup_name"] = $supply_name;
            $data_request[$stock_exp_date.$supply_code][$supply_lot]["sup_lot"] = $supply_lot;
            $data_request[$stock_exp_date.$supply_code][$supply_lot]["sup_desc"] = $supply_desc;
            $data_request[$stock_exp_date.$supply_code][$supply_lot]["sup_amt"] = $stock_amt;
            $data_request[$stock_exp_date.$supply_code][$supply_lot]["sup_unit"] = $supply_unit;
            $data_request[$stock_exp_date.$supply_code][$supply_lot]["sup_expdate"] = $stock_exp_date;
            $data_request[$stock_exp_date.$supply_code][$supply_lot]["sup_lastupdate"] = $stock_added_datetime;
            $data_request[$stock_exp_date.$supply_code][$supply_lot]["group_type"] = $supply_group_code;
            $data_request[$stock_exp_date.$supply_code][$supply_lot]["group_code"] = $supply_group_code;
            $data_request[$stock_exp_date.$supply_code][$supply_lot]["cost"] = $stock_cost;
        }
        // print_r($data_request);
    }
    $stmt->close();
    $mysqli->close();

    $sJS = "";
    $sHtmlExp = "";
    $sHtml = "";
    $toDay = new DateTime();
    foreach($data_request as $supply_code => $a_supLot){
        foreach($a_supLot as $supply_lot => $a_supinfo){
            $sRowcolor = "background-color: #DE6254";
            $ExpDate = new DateTime($a_supinfo["sup_expdate"]);
            // echo $a_supinfo["sup_expdate"]."/";
            $testDate = $ExpDate;
            $testDate->sub(new DateInterval('P6M'));
            if($testDate <= $toDay && $type_code == "1"){
                // echo $testDate->format("Y-m-d")."/".$toDay->format("Y-m-d");
            }
            else{
                $sRowcolor = "";
            }

            $no_pill = "display:none";
            if($a_supinfo["sup_amt"] > 0)
                $no_pill = "";

            $stBtAdjust = "";
            $stBtCost = "";
            if(getPerm("STOCK", $type_code, "admin") || getPerm("STOCK", $type_code, "view")){
                $stBtAdjust = '<button type="button" class="manage-stock btn btn-info mn-sthide holiday-ml-1" data-supcode="'.$a_supinfo["sup_code"].'" data-suplot="'.$a_supinfo["sup_lot"].'" style="display:none"><i class="fa fa-cog" aria-hidden="true"></i> Adjust Stock</button>';
                $stBtCost = '<span><i class="fa fa-cog manage-cost fabtn" data-supcode="'.$a_supinfo["sup_code"].'" data-suplot="'.$a_supinfo["sup_lot"].'"> '.$a_supinfo["cost"].'</i></span>';
            }
            else{
                $stBtCost = '<span>'.$a_supinfo["cost"].'</span>';
            }

            $sJS = '<div  class="fl-wrap-row row-color supply-detail-row row-hover h-40 fw-b loop-row group-'.$a_supinfo["group_code"].'" style="margin-left: 2px; margin-top: 3px; '.$no_pill.';'.$sRowcolor.';" data-typecode="'.$type_code.'" data-mode="'.$mode.'" data-amt="'.$a_supinfo["sup_amt"].'" data-group="'.$a_supinfo["group_code"].'">';
            $sJS .=     '<div class="fl-wrap-row">
                            <div class="fl-wrap-col w-130">
                                <div class="fl-wrap-row w-130">
                                    <div class="fl-fix holiday-text-detail holiday-smallfont2 w-130 fl-mid">
                                        <span>'.$a_supinfo["sup_code"].'</span>
                                    </div>
                                </div>
                                <div class="fl-wrap-row">
                                    <div class="fl-fix holiday-text-detail holiday-smallfont2 w-130 fl-mid">
                                        <span>'.$a_supinfo["sup_lot"].'</span>
                                    </div>
                                </div>
                            </div>
                            <div class="fl-fill holiday-text-detail font-s-1 fl-mid-left">
                                <span class="holiday-ml-1">'.wordwrap($a_supinfo["sup_name"], 55, "<br />\n").'</span>
                            </div>
                            <div class="fl-fill holiday-text-detail holiday-smallfont2 fl-mid-left" style="min-width: 200px">
                                <span class="holiday-ml-1">'.$a_supinfo["sup_desc"].'</span>
                            </div>
                            <div  class="fl-fix holiday-text-detail holiday-smallfont2 w-80 fl-mid">';
            $sJS .=             $stBtCost;                   
            $sJS .=         '</div>
                            <div class="fl-fix holiday-text-detail holiday-smallfont2 w-120 fl-mid adjust-val">
                                <span>'.$a_supinfo["sup_amt"].'</span>
                            </div>
                            <div class="fl-fix holiday-text-detail holiday-smallfont2 w-90 fl-mid">
                                <span>'.$a_supinfo["sup_unit"].'</span>
                            </div>
                            <div class="fl-fix holiday-text-detail holiday-smallfont2 fl-mid w-90">
                                <span>'.$a_supinfo["sup_expdate"].'</span>
                            </div>
                            <div class="fl-fix holiday-text-detail holiday-smallfont2 fl-mid w-100">
                                <span>'.$a_supinfo["sup_lastupdate"].'</span>
                            </div>
                            <div class="fl-fill holiday-text-detail holiday-smallfont2 fl-mid-left">';
            $sJS .=             $stBtAdjust;
            $sJS .=             '<button type="button" class="view-log btn btn-info holiday-ml-1" data-supplycode="'.$a_supinfo["sup_code"].'" data-lot="'.$a_supinfo["sup_lot"].'" data-supname="'.$a_supinfo["sup_name"].'"><i class="fa fa-search" aria-hidden="true"></i> View Log</button>
                            </div>
                        </div>';
            $sJS .= '</div>';
            if($testDate <= $toDay && $type_code == "1")
                $sHtmlExp .= $sJS;
            else
                $sHtml .= $sJS;
        }
    }

    $input_type = "<input type='hidden' id='txtGroupType' value='".$type_code."'/>";
    echo $input_type.$sHtmlExp.$sHtml;
?>
<script>
    $(document).ready(function(){
        var check_type_code = $("#txtGroupType").val();
        // console.log(check_type_code);

        if(check_type_code == "1" || check_type_code == "9")      
            $(".manage-stock").show();
        else 
            $(".manage-stock").hide();

        $("#ddGroupSupFilter").find("option").hide();
        $("#ddGroupSupFilter").find("option[data-supplytype="+check_type_code+"]").show();
        $("#ddGroupSupFilter").find("option[value='']").show();
    });
</script>