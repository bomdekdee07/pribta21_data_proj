<?

?>

<div class='fl-wrap-col' id="report_excel_old">
	<div class='fl-wrap-row bg-head-1 h-30'>
		<div class='fl-fix wper-30'>Title</div>
		<div class='fl-fill'>Download Files</div>
	</div>
	<div class='fl-wrap-row row-color h-30'>
		<div class='fl-fix wper-30 fl-mid-left'>HTPN</div>
		<div class='fl-fix w-250 fl-mid-left'><a href='p_export_hptn.php' style="color: #DA6911"><i class="fa fa-file-excel" aria-hidden="true"> All HTPN</i></a></div>
		<div class='fl-fix w-50 fl-mid-left font-s-2 fw-b'>
			Month:
		</div>
		<div class='fl-fix w-170 fl-mid-left font-s-1 fw-b'>
			<select name="month_select_hptn" class="input-group dd-month-select" style="text-align: center;" data-group="bt_export_hptn">
				<option	option	option value="">Please Select.</option>
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
		<div class="fl-fix w-20"></div>
		<div class='fl-fix w-40 fl-mid-left font-s-2 fw-b'>
			Year:
		</div>
		<div class='fl-fix w-100 fl-mid-left font-s-1 fw-b'>
			<select name="year_select_hptn" data-id="year_select_hptn" class="input-group dd-year-select" data-group="bt_export_hptn">
				<!-- Append function auto year -->
			</select>
		</div>
		<div class="fl-fix w-20"></div>
		<div class='fl-fix w-100 fl-mid-left font-s-1 fw-b'>
			<button name="bt_export_hptn" class="btn btn-primary font-s-1" style="padding: 0px 10px 0px 10px; font-weight: bold;" data-month="" data-year="" data-doccode="hptn">Export</button>
		</div>
	</div>

	<div class='fl-wrap-row row-color h-30'>
		<div class='fl-fix wper-30 fl-mid-left'>PURPOSE 2</div>
		<div class='fl-fix w-250 fl-mid-left'><a href='p_export_purpose2.php' style="color: #DA6911"><i class="fa fa-file-excel" aria-hidden="true"> All PURPOSE 2</i></a></div>
		<div class='fl-fix w-50 fl-mid-left font-s-2 fw-b'>
			Month:
		</div>
		<div class='fl-fix w-170 fl-mid-left font-s-1 fw-b'>
			<select name="month_select_purpose" class="input-group dd-month-select" style="text-align: center;" data-group="bt_export_purpose">
				<option	option	option value="">Please Select.</option>
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
		<div class="fl-fix w-20"></div>
		<div class='fl-fix w-40 fl-mid-left font-s-2 fw-b'>
			Year:
		</div>
		<div class='fl-fix w-100 fl-mid-left font-s-1 fw-b'>
			<select name="year_select_purpose" data-id="year_select_purpose" class="input-group dd-year-select" data-group="bt_export_purpose">
				<!-- Append function auto year -->
			</select>
		</div>
		<div class="fl-fix w-20"></div>
		<div class='fl-fix w-100 fl-mid-left font-s-1 fw-b'>
			<button name="bt_export_purpose" class="btn btn-primary font-s-1" style="padding: 0px 10px 0px 10px; font-weight: bold;" data-month="" data-year="" data-doccode="purpose2">Export</button>
		</div>
	</div>

	<div class='fl-wrap-row row-color h-30'>
		<div class='fl-fix wper-30 fl-mid-left'>DX</div>
		<div class='fl-fill fl-mid-left'><a href='export_dx_main.php'>DX Management</a></div>
	</div>
</div>

<script>
	$(document).ready(function(){
		appointment_auto_year(3, 5, $("[name=year_select_hptn]"));
		appointment_auto_year(3, 5, $("[name=year_select_purpose]"));
		defaultMY($("[name=month_select_hptn]"), $("[name=year_select_hptn]"));
		defaultMY($("[name=month_select_purpose]"), $("[name=year_select_purpose]"));

		var monthValHptn = $("[name=month_select_hptn]").val();
		var yearValHptn = $("[name=year_select_hptn]").val();
		var monthValPur = $("[name=month_select_purpose]").val();
		var yearValPur = $("[name=year_select_purpose]").val();

		$("[name=bt_export_hptn]").attr("data-month", monthValHptn);
		$("[name=bt_export_hptn]").attr("data-year", yearValHptn);
		$("[name=bt_export_purpose]").attr("data-month", monthValPur);
		$("[name=bt_export_purpose]").attr("data-year", yearValPur);

		// month select
		$("#report_excel_old .dd-month-select").off("change");
		$("#report_excel_old .dd-month-select").on("change", function(){
			var groupVal = $(this).attr("data-group");
			var monthVal = $(this).val();

			$("[name="+groupVal+"]").attr("data-month", monthVal);
		});
		
		// year select
		$("#report_excel_old .dd-year-select").off("change");
		$("#report_excel_old .dd-year-select").on("change", function(){
			var groupVal = $(this).attr("data-group");
			var yearVal = $(this).val();

			$("[name="+groupVal+"]").attr("data-year", yearVal);
		});

		// button export
		$("#report_excel_old .btn").off("click");
		$("#report_excel_old .btn").on("click", function(){
			var nameBtn = $(this).attr("name");
			var dataMonth = $("#report_excel_old [name="+nameBtn+"]").attr("data-month");
			var dataYear = $("#report_excel_old [name="+nameBtn+"]").attr("data-year");
			var dataDoccode = $("#report_excel_old [name="+nameBtn+"]").attr("data-doccode");
			var strUrl = "p_export_"+dataDoccode+".php?month="+dataMonth+"&year="+dataYear;
			// console.log(strUrl);

			window.open(strUrl, '_blank');
		});
	});

	function defaultMY(elementM, elementY){
		var d = new Date();
		var month = d.getMonth()+1;
		var year = d.getFullYear();

		elementM.val((month<10 ? '0' : '')+month);
		elementY.val(year);
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