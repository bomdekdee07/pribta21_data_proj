<div>
	<input id='txtDateTh' />

</div>

<script>
	$(document).ready(function(){
		var currentDate = new Date();
		var iRangeBefore = 543 -10;
		var iRangeAfter  = (543*1) +10;
		var sRange = "+"+iRangeBefore+":+"+iRangeAfter;
		currentDate.setYear(currentDate.getFullYear() + 543);

		$("#txtDateTh").datepicker({
		    changeMonth: true,
		    changeYear: true,
		    yearRange: sRange,
		    dateFormat: 'dd/mm/yy'


		  });
		//$('#date-of-birth').datepicker("setDate",currentDate );


	});

</script>