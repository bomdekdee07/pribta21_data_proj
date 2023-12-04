<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");
?>

<div class="fl-wrap-col" id="rp_monthly_drug_data">
    <div class="fl-wrap-row h-10"></div>
    <div class="fl-wrap-row h-30 font-s-3 fw-b">
        <div class="fl-fix w-20"></div>
        <div class="fl-fill fl-mid-left">
            Report Monthly Medicine Detail[DATA]
        </div>
    </div>

    <div class="fl-wrap-row h-90">
        <div class="fl-wrap-col w-20 h-75"></div>
        <div class="fl-wrap-col h-75 border" style="background-color: #D6EAF8;">
            <div class="fl-wrap-row h-15"></div>
            <div class="fl-wrap-row h-20 font-s-2">
                <div class="fl-fix w-10"></div>
                <div class="fl-fix w-225 fl-mid-left">
                    วันที่เริ่ม
                </div>
                <div class="fl-fix w-225 fl-mid-left">
                    ถึงวันที่
                </div>
                <div class="fl-fix w-225 fl-mid-left">
                    ประเภทยา
                </div>
            </div>

            <div class="fl-wrap-row h-35 font-s-2">
                <div class="fl-fix w-10"></div>
                <div class="fl-fix w-225 fl-mid-left">
                    <input type="text" name="start_date" style="min-width: 199px; min-height: 26px; max-height: 26px;"/>
                </div>
                <div class="fl-fix w-225 fl-mid-left">
                    <input type="text" name="end_date" style="min-width: 199px; min-height: 26px; max-height: 26px;"/>
                </div>
                <div class="fl-fix w-315 fl-mid-left">
                    <select id="drug_id" style="min-width: 299px; min-height: 26px; max-height: 26px;">
                        <option value="">--- Please Select ---</option>
                        <? include("report_monthly_drug_list.php"); ?>
                    </select>
                </div>
                <div class="fl-fix w-50 fl-mid-left ml-2">
                    <button class="btn btn-primary font-s-2 fw-b" id="btFindDrugSummary" style="padding: 2px 10px;"><i class="fa fa-search" aria-hidden="true"> ค้นหา</i></button>
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

    <div class="fl-wrap-row font-s-2 fw-b h-30 holiday-mr-3 header-hide" style="background-color: #7FD161; display: none;">
        <div class="fl-fix w-20" style="background-color: white"></div>
        <div class="fl-fix fl-mid border-line-1 w-110" style="min-width: 110px; max-width: 110px; border-color: #40A237;">
            Collect Date
        </div>
        <div class="fl-fix fl-mid border-line-1 w-90" style="min-width: 90px; max-width: 90px; border-color: #40A237;">
            UID
        </div>
        <div class="fl-fix fl-mid border-line-1 w-110" style="min-width: 110px; max-width: 110px; border-color: #40A237;">
            Supply Code
        </div>
        <div class="fl-fill fl-mid border-line-1" style="border-color: #40A237;">
            Name
        </div>
        <div class="fl-fix fl-mid border-line-1 w-150" style="border-color: #40A237;">
            Dispense Amount
        </div>
        <div class="fl-fix fl-mid border-line-1 w-80" style="border-color: #40A237;">
            Unit
        </div>
        <div class="fl-fix fl-mid border-line-1 w-170" style="border-color: #40A237;">
            Update date
        </div>
    </div>

    <div class="fl-wrap-col fl-auto" id="rp_monthly_drug_data_detail"></div>
</div>

<script>
    $(document).ready(function(){
        $("#rp_monthly_drug_data [name=start_date]").off("datepicker");
        $("#rp_monthly_drug_data [name=start_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});

        $("#rp_monthly_drug_data [name=end_date]").off("datepicker");
        $("#rp_monthly_drug_data [name=end_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});

        $("#rp_monthly_drug_data #btFindDrugSummary").off("click");
        $("#rp_monthly_drug_data").on("click", "#btFindDrugSummary", function(){
            var sStart_date = $("#rp_monthly_drug_data [name=start_date]").val();
            var sEnd_date = $("#rp_monthly_drug_data [name=end_date]").val();
            var sSupcode = $("#rp_monthly_drug_data #drug_id").val();
            var aData = {
                start_date: sStart_date,
                end_date: sEnd_date,
                sup_code: sSupcode
            }

            $.ajax({
                url: "report_monthly_drug_data_detail.php",
                method: "POST",
                cache: false,
                data: aData,
                success: function(sResult){
                    $("#rp_monthly_drug_data .header-hide").show();
                    $("#rp_monthly_drug_data #rp_monthly_drug_data_detail").children().remove();
                    $("#rp_monthly_drug_data #rp_monthly_drug_data_detail").append(sResult);
                }
            })
        });

        $("#rp_monthly_drug_data .export-toExcel").off("click");
        $("#rp_monthly_drug_data").on("click", ".export-toExcel", function(){
            var sStart_date = $("#rp_monthly_drug_data [name=start_date]").val();
            var sEnd_date = $("#rp_monthly_drug_data [name=end_date]").val();
            var sSupcode = $("#rp_monthly_drug_data #drug_id").val();
            var gen_ling = "report_monthly_drug_data_excel.php?start_date="+sStart_date+"&end_date="+sEnd_date+"&sup_code="+sSupcode;

            location.href = gen_ling;
        });
    });
</script>