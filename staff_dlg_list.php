<?
include_once("in_php_function.php");

?>
<div class='fl-wrap-col staff-list'>
	<div class='fl-wrap-row fs-xsmall fl-mid h-50 row-color-2 row-header'>
		<div class='fl-fix w-50'>
		</div>
		<div class='fl-fix w-100'>
			ID<br/><input class='searchinput fill-box mar-topdown' data-keyid='s_id' />
		</div>
		<div class='fl-fix w-150 '>
			Name<br/><input class='searchinput fill-box mar-topdown' data-keyid='s_name'  />
		</div>
		<div class='fl-fill'>
			Remark<br><input class='searchinput fill-box mar-topdown' data-keyid='s_remark'/>
		</div>
		<div class='fl-fix w-150'>
			Email<br/><input class='searchinput fill-box mar-topdown' data-keyid='s_email'/>
		</div>
		<div class='fl-fix w-120'>
			Tel<br/><input class='searchinput fill-box mar-topdown' data-keyid='s_tel'  />
		</div>
	</div>
	<div id='divStaffList' class='fl-wrap-col row-list fl-auto' >
		<? $_GET["opt"]="2"; include("staff_opt_list.php"); ?>

	</div>
</div>

<script>
	$(document).ready(function(){
		$(".staff-list .searchinput").unbind("change");
		$(".staff-list .searchinput").on("change",function(){
			sKeyId = $(this).attr('data-keyid');
			sValue = $(this).val().trim();
			$("#divStaffList .data-row").show();
			if(sValue==""){
				
			}else{
				$("#divStaffList .showinput[data-keyid='"+sKeyId+"']").not(":contains("+sValue+")").closest(".data-row").hide();
				/*
				$("#divStaffList .data-row").each(function(ix,objx){

				});\
				*/
			}

		});

		$(".staff-list .row-list .btnedit").unbind("click");
		$(".staff-list .row-list").on("click",".btnedit",function(){
			objRow = $(this).closest(".data-row");
			sId = $(objRow).attr("data-sid");
			closeDlg(this,sId);
		});		
	});

</script>