<?
    include("in_session.php");
    include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");
    if($sClinicID == null){
        $sClinicID = getQS("clinic_id");
    }
    $mode_list_main = getQS("mode_list_main");
?>

<div id="holiday_management" class="fl-wrap-col holiday-mt-0" style="min-width:1024;">
    <span class="data_defult" data-ss=<? echo ($sSID == ""? "-": $sSID); ?> data-clinicid=<? echo ($sClinicID == ""? "-" : $sClinicID); ?> data-mode="<? echo ($mode_list_main == ""? "-": $mode_list_main) ?>" ></span>
    <!-- HEAD -->
    <div class="fl-fix">
        <div class="fl-wrap-row">
            <div class="fl-fix holiday-ml-0">
                <button type="button" id="holiday_clinic" class="btn btn-secondary clinic smallfont2 holiday-type-btn"><b><i class="fa fa-building" aria-hidden="true"></i> Clinic</b></button>
            </div>
            <div class="fl-fix holiday-ml-0">
                <button type="button" id="holiday_staff" class="btn btn-secondary staff smallfont2 holiday-type-btn"><b><i class="fa fa-users" aria-hidden="true"></i> Staff</b></button>
            </div>
        </div>
        <div class="fl-wrap-row">
            <div class="fl-fill holiday-box-serch holiday-ml-0 holiday-mr-1">
                <div class="fl-wrap-row holiday-mt-1">
                    <div class="fl-fix holiday-ml-2 smallfont2 holiday-mt-2" style="min-width: 20px">
                        <b><span>ปี:</span></b>
                    </div>
                    <div class="fl-fix holiday-mt-2" style="min-width: 150px">
                        <select id="holiday_year_select" name="holiday_year_select" class="smallfont2 input-group">
                            <!-- Function auto year -->
                        </select>
                    </div>
                    <div class="fl-fix" style="min-width: 10px"></div>
                    
                    <div class="fl-fix holiday-ml-2 smallfont2 holiday-mt-2" id="holiday_dide" style="min-width: 60px">
                        <b><span>เจ้าหน้าที่:</span></b>
                    </div>
                    <div class="fl-fix holiday-mt-2" style="min-width: 150px" id="holiday_dide">
                        <input type="text" id="holiday_serach" class="input-group smallfont2 holiday-mt-01">
                    </div>
                    
                    <div class="fl-fix" style="min-width: 30px"></div>
                    <div class="fl-fix" style="min-width: 100px">
                        <button type="button" id="holiday_new" class="btn smallfont2 holiday-add-btn"><b><i class="fa fa-plus-circle" aria-hidden="true"></i> เพิ่มวันหยุด</b></button>
                    </div>
                    <div class="fl-fix holiday-ml-1" style="min-width: 120px">
                        <button type="button" id="holiday_export" class="btn smallfont2 holiday-add-btn"><b><i class="fa fa-file-excel" aria-hidden="true"></i> Export Excel</b></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="fl-wrap-row">
            <div class="fl-fill holiday-box-head holiday-ml-0 holiday-mr-1">
                <div class="fl-wrap-row">
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 170px;">
                        <b><span style="margin-left: 20px;">คลีนิก</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 160px;">
                        <b><span>วันที่</span></b>
                    </div>
                    <div class="fl-fix holiday-text-head holiday-smallfont2" style="min-width: 190px;">
                        <b><span>เจ้าหน้าที่</span></b>
                    </div>
                    <div class="fl-fill holiday-text-head holiday-smallfont2">
                        <b><span>รายละเอียด</span></b>
                    </div>
                    <div class="fl-fill holiday-text-head holiday-smallfont2">
                        <b><span style="margin-right: 50px;">หมายเหตุ</span></b>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- DETAIL -->
    <div class='fl-fill fl-auto holiday-ml-0 holiday-mr-1' id="holiday_show_data">
        <!-- Ajax reload data -->
    </div>
</div>

<script>
    $(document).ready(function(){
        // Function auto strat and end year
        $("#holiday_management [name=holiday_year_select]").each(function(index, obj){
            appointment_auto_year(3, 5, $(this));
        });

        // Auto current year
        var d = new Date();
        $("#holiday_management #holiday_year_select").val(d.getFullYear());

        // Click ADD
        $("#holiday_management #holiday_new").unbind("click");
        $("#holiday_management #holiday_new").on("click", function(){
            var sid = $("#holiday_management .data_defult").data("ss");

            if($("#holiday_clinic").val() == "selected"){
                var sid = "";
            }
            
            var clinicid = $("#holiday_management .data_defult").data("clinicid");
            var d = new Date();holiday_convert_months
            var date = d.getFullYear()+"-"+holiday_convert_months(d.getMonth()+1)+"-"+holiday_convert_months(d.getDate());
            var mode_id = $("#holiday_management #holiday_clinic").val();
            if(mode_id == "selected"){
                var mode_can_id = "clinic";
            }
            else{
                var mode_can_id = "staff";
            }
            var year_substr = $("#holiday_management #holiday_year_select").val();
            var sUrl_appoint = "holiday_management_edit_create.php?s_id="+sid+"&clinic_id="+clinicid+"&date_res="+date+"&mode=create";

            showDialog(sUrl_appoint, "Holiday Information", "500", "500", "", function(sResult){
                var url_gen_doc = "holiday_management_function.php?mode_id="+mode_can_id+"&year_value="+year_substr;

                $("#holiday_show_data").load(url_gen_doc);
            }, false, function(sResult){});
        });

        // function search name staff
        $("#holiday_management #holiday_serach").unbind("keypress");
        $("#holiday_management #holiday_serach").on("keypress", function(){
            var year_select = $("#holiday_management #holiday_year_select").val();
            var name_key = $("#holiday_management #holiday_serach").val();
            var aData = {
                mode_id: "staff",
                year_value: year_select,
                name_search: name_key,
            };

            $.ajax({url: "holiday_management_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#holiday_management #holiday_show_data").children().remove();
                    $("#holiday_management #holiday_show_data").append(result);
            }});
        });

        // Year on change 
        $("#holiday_management #holiday_year_select").on("change", function(){
            if($("#holiday_clinic").val() == "selected"){
                var check_status = "clinic";
            }
            else{
                var check_status = "staff";
            }

            var year_select = $("#holiday_management #holiday_year_select").val();
            var aData = {
                mode_id: check_status,
                year_value: year_select,
            };

            $.ajax({url: "holiday_management_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#holiday_management #holiday_show_data").children().remove();
                    $("#holiday_management #holiday_show_data").append(result);
            }});
        });

        // function select list main clinic
        $("#holiday_clinic").unbind("click");
        $("#holiday_clinic").on("click", function(){
            $("#holiday_management #holiday_dide").hide();

            $("#holiday_management #holiday_clinic").attr("class", "btn btn-secondary clinic smallfont2 holiday-type-btn selected");
            $("#holiday_management #holiday_clinic").attr("value", "selected");
            $("#holiday_management #holiday_staff").attr("class", "btn btn-secondary clinic smallfont2 holiday-type-btn");
            $("#holiday_management #holiday_staff").attr("value", "");
            $("#holiday_management #holiday_serach").val("");
            

            var year_select = $("#holiday_management #holiday_year_select").val();
            var aData = {
                mode_id: "clinic",
                year_value: year_select,
            };

            $.ajax({url: "holiday_management_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#holiday_management #holiday_show_data").children().remove();
                    $("#holiday_management #holiday_show_data").append(result);
            }});
        })

        // click list main staff
        $("#holiday_staff").unbind("click");
        $("#holiday_staff").on("click", function(){
            $("#holiday_management #holiday_dide").show();

            $("#holiday_management #holiday_staff").attr("class", "btn btn-secondary staff smallfont2 holiday-type-btn selected");
            $("#holiday_management #holiday_staff").attr("value", "selected");
            $("#holiday_management #holiday_clinic").attr("class", "btn btn-secondary clinic smallfont2 holiday-type-btn");
            $("#holiday_management #holiday_clinic").attr("value", "");
            $("#holiday_management #holiday_serach").val("");

            var year_select = $("#holiday_management #holiday_year_select").val();
            var name_key = $("#holiday_management #holiday_serach").val();
            var aData = {
                mode_id: "staff",
                year_value: year_select,
                name_search: name_key,
            };

            $.ajax({url: "holiday_management_function.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    $("#holiday_management #holiday_show_data").children().remove();
                    $("#holiday_management #holiday_show_data").append(result);
            }});
        });
        
        // Autu load first page
        var check_condition = ($("#holiday_management .data_defult").data("mode"));
        if(check_condition == "-"){
            $("#holiday_management #holiday_clinic").trigger('click');
        }
    });

    function holiday_convert_months(months){
        var months_str = months.toString();
        months_str = months_str.padStart(2, "0");

        return months_str;
    }

    function appointment_auto_year(back_year, next_year, element){
        var d = new Date();
        var curYear = d.getFullYear();
        var back_date = (curYear-back_year);
        var next_date = (curYear+next_year);
        var temp_st = "<option value=''>Please Select.</option>";

        for(var n=back_date;n <= next_date; n++){
            temp_st += "<option value='"+n+"'>"+n+"</option>";
        }
        
        element.children().remove();
        element.append(temp_st);
    }
</script>