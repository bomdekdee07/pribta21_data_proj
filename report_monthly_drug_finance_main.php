<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");
?>

<div class="fl-wrap-col" id="rp_drug_finance_excel">
    <div class="fl-wrap-row h-10"></div>
    <div class="fl-wrap-row h-30 font-s-3 fw-b">
        <div class="fl-fix w-20"></div>
        <div class="fl-fill fl-mid-left">
            Report Monthly Drug To Finance
        </div>
    </div>

    <div class="fl-wrap-row h-90">
        <div class="fl-wrap-col w-20 h-75"></div>
        <div class="fl-wrap-col h-75 border" style="background-color: #85C1E8;">
            <div class="fl-wrap-row h-15"></div>
            <div class="fl-wrap-row h-20 font-s-2">
                <div class="fl-fix w-10"></div>
                <div class="fl-fix w-225 fl-mid-left">
                    เดือน
                </div>
            </div>

            <div class="fl-wrap-row h-35 font-s-2">
                <div class="fl-fix w-10"></div>
                <div class="fl-fix w-200 fl-mid-left">
                    <select name="start_month" style="min-width: 199px; min-height: 26px; max-height: 26px;">
                        <option value="">Please Select Month.</option>
                        <option value="01">มกราคม</option>
                        <option value="02">กุมภาพันธ์</option>
                        <option value="03">มีนาคม</option>
                        <option value="04">เมษายน</option>
                        <option value="05">พฤษภาคม</option>
                        <option value="06">มิถุนายน</option>
                        <option value="07">กรกฎาคม</option>
                        <option value="08">สิงหาคม</option>
                        <option value="09">กันยายน</option>
                        <option value="10">ตุลาคม</option>
                        <option value="11">พฤศจิกายน</option>
                        <option value="12">ธันวาคม</option>
                    </select>
                </div>
                <div class="fl-fix w-200 fl-mid-left">
                    <select name="start_year" style="min-width: 160px; min-height: 26px; max-height: 26px;">
                        <!-- Append function auto year -->
                    </select>
                </div>
                <div class="fl-fix w-50 fl-mid-left ml-2">
                    <button class="btn btn-primary font-s-2 fw-b" id="btFindMonthlyDrug" style="padding: 2px 10px;"><i class="fa fa-search" aria-hidden="true"> ค้นหา</i></button>
                </div>
                <div class="fl-fill"></div>
                <div class="fl-wrap-col w-35 h-35 border-line-2">
                    <div class="fl-fill fl-mid">
                        <i class="fa fa-file-excel fabtn fa-2x export-toExcel" style="background-color:green" aria-hidden="true" title="Export to Excel"></i>
                    </div>
                </div>
                <div class="fl-fix w-5"></div>
            </div>
        </div>
        <div class="fl-wrap-col w-20"></div>
    </div>

    <div class="fl-wrap-row font-s-2 fw-b h-30 header-hide holiday-mr-2" style="display: none;  background-color: #7FD161;">
        <div class="fl-fix w-20" style="background-color: white;"></div>
        <div class="fl-fill fl-mid border-line-1" style="border-color: #40A237;">
            ชื่อยา
        </div>
        <div class="fl-fix fl-mid border-line-1 w-120" style="border-color: #40A237;">
            จำนวนที่ยกมา
        </div>
        <div class="fl-fix fl-mid border-line-1 w-120" style="border-color: #40A237;">
            ราคาที่ซื้อ
        </div>
        <div class="fl-fix fl-mid border-line-1 w-120" style="border-color: #40A237;">
            ซื้อเพิ่ม
        </div>
        <div class="fl-fix fl-mid border-line-1 w-120" style="border-color: #40A237;">
            จ่ายยา
        </div>
        <div class="fl-fix fl-mid border-line-1 w-120" style="border-color: #40A237;">
            ราคาที่ขาย
        </div>
        <div class="fl-fix fl-mid border-line-1 w-120" style="border-color: #40A237;">
            จำนวนที่ยกไป
        </div>
    </div>

    <div class="fl-wrap-col fl-auto" id="rp_drug_finance_excel_detail"></div>
</div>

<script>
    $(document).ready(function(){
        // Function auto strat and end year
        $("#rp_drug_finance_excel [name=start_year]").each(function(index, obj){
            appointment_auto_year(3, 5, $(this));
        });

        $("#rp_drug_finance_excel [name=start_month]").off("datepicker");
        $("#rp_drug_finance_excel [name=start_month]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});

        $("#rp_drug_finance_excel #btFindMonthlyDrug").off("click");
        $("#rp_drug_finance_excel").on("click", "#btFindMonthlyDrug", function(){
            var sStart_month = $("#rp_drug_finance_excel [name=start_month]").val();
            var sStart_year = $("#rp_drug_finance_excel [name=start_year]").val();
            var concat_str_date = sStart_year+"-"+sStart_month+"-01";
            var aData = {
                start_date: concat_str_date
            }

            if(sStart_month == ""){
                alert("Plese Select Month.");
            }
            else if(sStart_year == "" && sStart_month != ""){
                alert("Plese Select Year.");
            }
            else{
                $.ajax({
                    url: "report_monthly_drug_finance_detail.php",
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(sResult){
                        $("#rp_drug_finance_excel .header-hide").show();
                        $("#rp_drug_finance_excel #rp_drug_finance_excel_detail").children().remove();
                        $("#rp_drug_finance_excel #rp_drug_finance_excel_detail").append(sResult);
                    }
                })
            }
        });

        $("#rp_drug_finance_excel .export-toExcel").off("click");
        $("#rp_drug_finance_excel").on("click", ".export-toExcel", function(){
            var sStart_month = $("#rp_drug_finance_excel [name=start_month]").val();
            var sStart_year = $("#rp_drug_finance_excel [name=start_year]").val();
            var concat_str_date = sStart_year+"-"+sStart_month+"-01";

            if(sStart_month == ""){
                alert("Plese Select Month.");
            }
            else if(sStart_year == "" && sStart_month != ""){
                alert("Plese Select Year.");
            }
            else{
                var sStart_year = $("#rp_drug_finance_excel [name=start_month]").val();
                var gen_ling = "report_monthly_drug_finance_detail_excel.php?start_month="+concat_str_date;
                location.href = gen_ling;
            }
        });
    });

    function appointment_auto_year(back_year, next_year, element){
        var d = new Date();
        var curYear = d.getFullYear();
        var back_date = (curYear-back_year);
        var next_date = (curYear+next_year);
        var temp_st = "<option value=''>Please Select Year.</option>";

        for(var n=back_date;n <= next_date; n++){
            temp_st += "<option value='"+n+"'>"+n+"</option>";
        }
        
        element.children().remove();
        element.append(temp_st);
    }
</script>