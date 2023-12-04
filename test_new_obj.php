<div id='divData'>

</div>
<div>
	<span id='btnAddNew'>Add</span>
</div>
<script>
	$(document).ready(function(){
		


		$("#btnAddNew").on("click",function(){
			$("#divData").append("<input class='v-date' />");
			
			$(".v-date").each(function(ix,objx){
				if($(objx).hasClass("hasDatepicker")){

				}else{
					$(objx).datepicker();
				}
			});
			
		});

	});



</script>
