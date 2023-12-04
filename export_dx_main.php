<?
    include("in_session.php");
    include('in_db_conn.php');
    include("in_head_script.php");
    include_once("in_php_function.php");
?>

<div class="fl-wrap-col" id="dx_main">
    <div class="fl-wrap-row h-30 holiday-mt-3">
        <div class="fl-fix w-100 fw-b fl-mid-left fs-smaller holiday-ml-9">
            <span>Visit Date:</span>
        </div>
        <div class="fl-fix w-300 fl-mid-left fs-smaller">
            <input type="text" name="visit_date" data-id="visit_date" style="width: 200px;">
        </div>
    </div>
    <div class="fl-wrap-row h-30 holiday-mt-1">
        <div class="fl-fix fl-mid-right" style="min-width: 470px">
            <button class="btn btn-success" id="export_data_dx">Export</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#dx_main [name=visit_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});
    });

    $("#dx_main #export_data_dx").off("click");
    $("#dx_main #export_data_dx").on("click", function() {
        var visit_date_s = $("#dx_main [name=visit_date]").val();
        var gen_link = "export_dx_excel.php?visitdate="+visit_date_s;
        window.open(gen_link,'_blank');
    });
</script>