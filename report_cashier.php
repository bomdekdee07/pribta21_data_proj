<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");
?>

<div class="fl-wrap-col" id="rp_cashier_excel">
    <div class="fl-wrap-row h-10"></div>
    <div class="fl-wrap-row h-30 font-s-3 fw-b">
        <div class="fl-fix w-20"></div>
        <div class="fl-fill fl-mid-left">
            Report Cashier
        </div>
    </div>

    <div class="fl-wrap-row h-90">
        <div class="fl-wrap-col w-20 h-75"></div>
        <div class="fl-wrap-col h-75 border" style="background-color: #F7DC6F;">
            <div class="fl-wrap-row h-15"></div>
            <div class="fl-wrap-row h-20 font-s-2">
                <div class="fl-fix w-10"></div>
                <div class="fl-fix w-225 fl-mid-left">
                    วันที่
                </div>
            </div>

            <div class="fl-wrap-row h-35 font-s-2">
                <div class="fl-fix w-10"></div>
                <div class="fl-fix w-225 fl-mid-left">
                    <input type="text" name="start_date" style="min-width: 199px; min-height: 26px; max-height: 26px;"/>
                </div>
                <div class="fl-fix w-50 fl-mid-left ml-2">
                    <button class="btn btn-primary font-s-2 fw-b" id="btFindCashier" style="padding: 2px 10px;"><i class="fa fa-search" aria-hidden="true"> ค้นหา</i></button>
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

    <div class="fl-wrap-row font-s-2 fw-b h-30 header-hide holiday-mr-3" style="display: none; background-color: #53BBDF;">
        <div class="fl-fix w-20" style="background-color: white;"></div>
        <div class="fl-fill fl-mid border-line-1" style="border-color: #1A5276;">
            Bill ID
        </div>
        <div class="fl-fix fl-mid border-line-1 w-100" style="border-color: #1A5276;">
            UID
        </div>
        <div class="fl-fix fl-mid border-line-1 w-80" style="border-color: #1A5276;">
            Queue
        </div>
        <div class="fl-fix fl-mid border-line-1 w-150" style="border-color: #1A5276;">
            Visit Date
        </div>
        <div class="fl-fix fl-mid border-line-1 w-170" style="border-color: #1A5276;">
            <i class="fa fa-prescription" style="color:yellow;"></i>Total Drug Sales
        </div>
        <div class="fl-fix fl-mid border-line-1 w-170" style="border-color: #1A5276;">
            <i class="fa fa-user" style="color:white;"></i>Total Service Charge
        </div>
        <div class="fl-fix fl-mid border-line-1 w-170" style="border-color: #1A5276;">
        <i class="fa fa-vial" style="color:green;"></i>All Lab Tests
        </div>
        <div class="fl-fix fl-mid border-line-1 w-170" style="border-color: #1A5276;">
            <i class="fa fa-dollar-sign" style="color:gold;"></i>All Net Per Bill
        </div>
    </div>

    <div class="fl-wrap-col fl-auto" id="rp_cashier_excel_detail"></div>

    <div class="fl-wrap-col h-100">
        <div class="fl-wrap-row h-30 holiday-mr-3">
            <div class="fl-fix w-20"></div>
            <div class="fl-fill border-line-1 fw-b fl-mid-left">
                <span class="holiday-ml-2">Total</span>
            </div>
            <div class="fl-fix border-line-1 w-170 fl-mid-right sale-drug-bind"></div>
            <div class="fl-fix border-line-1 w-170 fl-mid-right sale-service-bind"></div>
            <div class="fl-fix border-line-1 w-170 fl-mid-right sale-lab-bind"></div>
            <div class="fl-fix border-line-1 w-170 fl-mid-right sale-bill-bind"></div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#rp_cashier_excel [name=start_date]").off("datepicker");
        $("#rp_cashier_excel [name=start_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});

        $("#rp_cashier_excel #btFindCashier").off("click");
        $("#rp_cashier_excel").on("click", "#btFindCashier", function(){
            var sStart_date = $("#rp_cashier_excel [name=start_date]").val();
            var aData = {
                start_date: sStart_date
            }
            var check_date = $("#rp_cashier_excel [name=start_date]").val();
            if(check_date != ""){
                $.ajax({
                    url: "report_cashier_detail.php",
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(sResult){
                        $("#rp_cashier_excel .header-hide").show();
                        $("#rp_cashier_excel #rp_cashier_excel_detail").children().remove();
                        $("#rp_cashier_excel #rp_cashier_excel_detail").append(sResult);

                        var total_saleDrug_val = 0;
                        var total_saleDrug = 0
                        $(".sale-drug").each(function(){
                            total_saleDrug_val = $(this).html();
                            total_saleDrug += parseInt(total_saleDrug_val.replace(/,/g, ''));
                        })
                        $("#rp_cashier_excel .sale-drug-bind").children().remove();
                        $("#rp_cashier_excel .sale-drug-bind").append('<span class="holiday-mr-2">'+total_saleDrug.toLocaleString()+'</span>');

                        var total_saleDrug_val = 0;
                        var total_saleDrug = 0
                        $(".sale-service").each(function(){
                            total_saleDrug_val = $(this).html();
                            total_saleDrug += parseInt(total_saleDrug_val.replace(/,/g, ''));
                        })
                        $("#rp_cashier_excel .sale-service-bind").children().remove();
                        $("#rp_cashier_excel .sale-service-bind").append('<span class="holiday-mr-2">'+total_saleDrug.toLocaleString()+'</span>');

                        var total_saleDrug_val = 0;
                        var total_saleDrug = 0
                        $(".sale-lab").each(function(){
                            total_saleDrug_val = $(this).html();
                            total_saleDrug += parseInt(total_saleDrug_val.replace(/,/g, ''));
                        })
                        $("#rp_cashier_excel .sale-lab-bind").children().remove();
                        $("#rp_cashier_excel .sale-lab-bind").append('<span class="holiday-mr-2">'+total_saleDrug.toLocaleString()+'</span>');

                        var total_saleDrug_val = 0;
                        var total_saleDrug = 0
                        $(".sale-bill").each(function(){
                            total_saleDrug_val = $(this).html();
                            total_saleDrug += parseInt(total_saleDrug_val.replace(/,/g, ''));
                        })
                        $("#rp_cashier_excel .sale-bill-bind").children().remove();
                        $("#rp_cashier_excel .sale-bill-bind").append('<span class="holiday-mr-2">'+total_saleDrug.toLocaleString()+'</span>');
                    }
                })
            }
            else{
                alert("Please Select Date.");
            }
        });

        $("#rp_cashier_excel .export-toExcel").off("click");
        $("#rp_cashier_excel").on("click", ".export-toExcel", function(){
            var check_date = $("#rp_cashier_excel [name=start_date]").val();
            if(check_date != ""){
                var sStart_date = $("#rp_cashier_excel [name=start_date]").val();
                var gen_ling = "report_cashier_detail_excel.php?start_date="+sStart_date;
                location.href = gen_ling;
            }
            else{
                alert("Please Select Date.");
            }
        });
    });
</script>