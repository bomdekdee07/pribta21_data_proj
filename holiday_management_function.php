<?
    include("in_db_conn.php");
    include("in_session.php");
    include_once("in_php_function.php");

    $mode_id_get = isset($_POST["mode_id"])?$_POST["mode_id"]: getQS("mode_id");
    $year_value_get = isset($_POST["year_value"])?$_POST["year_value"]: getQS("year_value");
    $name_get = isset($_POST["name_search"])?$_POST["name_search"]: getQS("name_search");
    
    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");
    // echo "MODE: ".$mode_id_get."/".$sClinicID;
?>

<?
    $data_holiday_clinic = array();
    if($mode_id_get == "clinic"){
        $query = "select m.holiday_date, m.holiday_title, m.clinic_id, m.remark, n.clinic_name 
        from i_holiday m 
        left join p_clinic n on (m.clinic_id = n.clinic_id)
        where m.s_id = 'none' 
        and SUBSTRING(m.holiday_date, 1, 4) = ?
        order by m.holiday_date;";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("s", $year_value_get);

        if($stmt->execute()){
            $stmt->bind_result($holiday_date, $holiday_title, $clinic_id, $remark, $clinic_name);
            while ($stmt->fetch()) {
                $data_holiday_clinic[$holiday_date]["date"] = $holiday_date;
                $data_holiday_clinic[$holiday_date]["title"] = $holiday_title;
                $data_holiday_clinic[$holiday_date]["clinic_id"] = $clinic_id;
                $data_holiday_clinic[$holiday_date]["remark"] = $remark;
                $data_holiday_clinic[$holiday_date]["clinic_name"] = $clinic_name;
                $data_holiday_clinic[$holiday_date]["staff_name"] = "";
                $data_holiday_clinic[$holiday_date]["s_id"] = "";
                // print($data_holiday_clinic[$holiday_date]["date"]."<br>");
            }
        }
        else{
            $msg_error .= $stmt->error;
        }
        $stmt->close();
    }
    else if($mode_id_get == "staff"){
        $query = "select m.holiday_date, m.holiday_title, m.clinic_id, m.remark, n.clinic_name, ns.s_name, m.s_id from i_holiday m 
        left join p_clinic n on (m.clinic_id = n.clinic_id) 
        left join p_staff ns on (m.s_id = ns.s_id) 
        where m.s_id != 'none' 
        and SUBSTRING(m.holiday_date, 1, 4) = ? 
        and ns.s_name like '%".$name_get."%' order by m.holiday_date;";

        $stmt = $mysqli->prepare($query);
        $stmt -> bind_param("s", $year_value_get );

        if($stmt->execute()){
            $stmt->bind_result($holiday_date, $holiday_title, $clinic_id, $remark, $clinic_name, $s_name, $s_id);
            while ($stmt->fetch()) {
                $data_holiday_clinic[$holiday_date]["date"] = $holiday_date;
                $data_holiday_clinic[$holiday_date]["title"] = $holiday_title;
                $data_holiday_clinic[$holiday_date]["clinic_id"] = $clinic_id;
                $data_holiday_clinic[$holiday_date]["remark"] = $remark;
                $data_holiday_clinic[$holiday_date]["clinic_name"] = $clinic_name;
                $data_holiday_clinic[$holiday_date]["staff_name"] = $s_name;
                $data_holiday_clinic[$holiday_date]["s_id"] = $s_id;
                // print($data_holiday_clinic[$holiday_date]["date"]."<br>");
            }
        }
        else{
            $msg_error .= $stmt->error;
        }
        $stmt->close();
    }
    $mysqli->close();

    $sJS = '';
    
    if(count($data_holiday_clinic) > 0){
        foreach($data_holiday_clinic as $key => $value){
            $sJS .= '<div id="holiday_detail" class="fl-wrap-row" style="margin-left: 2px; margin-top: 3px">';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2" style="min-width: 55px;">';
            $sJS .=         '<a href="#"><b><i class="fa fa-edit edit-click" aria-hidden="true" data-clinicid="'.$value["clinic_id"].'" data-date="'.$value["date"].'" data-sid="'.$value["s_id"].'" data-mode="'.$mode_id_get.'"></i></b></a>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail-left holiday-smallfont2" style="min-width: 115px;">';
            $sJS .=         '<span>'.$value["clinic_name"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2" style="min-width: 160px;">';
            $sJS .=         '<span>'.$value["date"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2" style="min-width: 190px;">';
            $sJS .=         '<span>'.$value["staff_name"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fill holiday-text-detail-left holiday-smallfont2 holiday-ml-2">';
            $sJS .=         '<span>'.$value["title"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fill holiday-text-detail-left holiday-smallfont2 holiday-ml-4">';
            $sJS .=         '<span>'.$value["remark"].'</span>';
            $sJS .=     '</div>';
            $sJS .=     '<div class="fl-fix holiday-text-detail holiday-smallfont2" style="min-width: 55px;">';
            $sJS .=         '<a href="#"><b><i class="fa fa-times" style="color:red" aria-hidden="true" data-clinicid="'.$value["clinic_id"].'" data-date="'.$value["date"].'" data-sid="'.$value["s_id"].'" data-mode="'.$mode_id_get.'"></i></b></a>';
            $sJS .=     '</div>';
            $sJS .= '</div>';
        }

        echo $sJS;
    }
?>

<script>
    $(document).ready(function(){
        // Click Delete
        $("#holiday_detail .fa-times").unbind("click");
        $("#holiday_detail .fa-times").on("click", function(){
            var mode_can_id = $(this).data("mode");
            var year_substr = $(this).data("date").substring(0,4);
            if (confirm('คุณแน่ใจหรือไม่?')) {
                var aData = {
                    app_mode: "delete_holiday",
                    clinic_id: $(this).data("clinicid"),
                    date_res: $(this).data("date"),
                    sid: $(this).data("sid"),
                    // dataid: lst_data_obj,
                };

                // console.log(aData);
                callAjax("doctor_db_form_update.php", aData, function(){
                    var url_gen_doc = "holiday_management_function.php?mode_id="+mode_can_id+"&year_value="+year_substr;
                    // console.log(url_gen_doc);
                    $("#holiday_detail").parent().load(url_gen_doc);
                });
            }
        });

        // Click edit
        $("#holiday_detail .edit-click").unbind("click");
        $("#holiday_detail .edit-click").on("click", function(){
            var sid = $(this).data("sid") === undefined? "" : $(this).data("sid");
            var clinicid = $(this).data("clinicid");
            var date = $(this).data("date");
            var mode_can_id = $(this).data("mode");
            var year_substr = $(this).data("date").substring(0,4);
            var sUrl_appoint = "holiday_management_edit_create.php?s_id="+sid+"&clinic_id="+clinicid+"&date_res="+date;

            showDialog(sUrl_appoint, "Holiday Information", "500", "500", "", function(sResult){
                var url_gen_doc = "holiday_management_function.php?mode_id="+mode_can_id+"&year_value="+year_substr;
                // console.log(url_gen_doc);
                $("#holiday_detail").parent().load(url_gen_doc);
            }, false, function(sResult){});
        });
    });
</script>