<?
	include("in_session.php");
	include_once("in_php_function.php");
?>

<div class='fl-wrap-col room-list'>
	<div class='fl-wrap-row fs-s fl-mid h-xs row-color-2'>
		<div class='fl-fix w-50'>
		</div>
		<div class='fl-fix w-100'>
			Clinic ID
		</div>
		<div class='fl-fix w-50'>
			No
		</div>
		<div class='fl-fill '>
			Name
		</div>
		<div class='fl-fill '>
			Detail
		</div>
		<div class='fl-fix w-50'>
			Status
		</div>
		<div class='fl-fix w-50'>
			Section
		</div>
		<div class='fl-fix w-80'>
			Default
		</div>
		<div class='fl-fix w-80'>
			Icon
		</div>			
		<div class='fl-fix w-80'>
			Mng.
		</div>
	</div>
	<div class='fl-wrap-row input-header fs-s fl-mid h-ss  row-color-2'>
		<div class='fl-fix w-50'>
			<i id='btnCancelData' class='fabtn fas fa-broom fa-2x'></i>
		</div>
		<div class='fl-fix w-100'>
			<SELECT id='ddlClinicId' class='fill-box saveinput h-20' data-odata='' data-keyid='clinic_id' data-pk="1" >
				<? $_GET["opt"]="1"; $_GET["u_mode"]="clinic-list-only"; include("clinic_opt_list.php"); ?>
			</SELECT>
		</div>
		<div class='fl-fix w-50'>
			<input id='txtRoomId' class='fill-box saveinput mar-topdown h-20' data-odata='' data-keyid='room_no' maxlength="50" data-pk="1" />
		</div>
		<div class='fl-fill '>
			<input id='txtRoomName' class='fill-box mar-topdown saveinput h-20' data-odata='' data-keyid='room_name' maxlength="200" />
		</div>
		<div class='fl-fill '>
			<input id='txtRoomDetail' class='fill-box mar-topdown saveinput h-20' data-odata='' data-keyid='room_detail' maxlength="200" />
		</div>
		<div class='fl-fix w-50'>
			<SELECT id='ddlRoomStatus' class='fill-box saveinput h-20' data-default='1' data-odata='' data-keyid='room_status'>
				<option value='1'>Enable</option>
				<option value='0'>Disable</option>
			</SELECT>
		</div>
		<div class='fl-fix w-50'>
			<SELECT id='ddlRoomSection' class='fill-box saveinput h-20' data-default='' data-odata='' data-keyid='section_id'>
				<? $_GET["opt"]="1"; include("list_section.php"); ?>
			</SELECT>
		</div>
		<div class='fl-fix w-80'>
			<SELECT id='ddlRoomDefault' class='fill-box saveinput h-20' data-default='0' data-odata='' data-keyid='default_room'>
				<option value='0'>0.None</option>
				<option value='1'>1.Register</option>
				<option value='2'>2.Doctor</option>
				<option value='3'>3.Cashier & Pharma</option>
				<option value='9'>9.Home</option>
				<option value='10'>10.Wating Room</option>
			</SELECT>
		</div>
		<div class='fl-fix w-80'>
			<input id='txtRoomIcon' class='fill-box mar-topdown saveinput h-20' data-odata='' data-keyid='room_icon' maxlength="1000" />
		</div>	
		<div class='fl-fix w-80'>
			<span style='color:green'><i id='btnAddRoom' class="fabtn fas fa-plus-square fa-2x" data-mode='room_add'></i><i id='btnAddRoom-loader' style='display:none' class="fas fa-spinner fa-spin fa-2x"></i></span>
		</div>


	</div>
	<div class='fl-wrap-col row-body fl-auto' >
		<div class='fl-fill row-100ist'>
			<? $_GET["opt"]=""; $_GET["u_mode"]="room-list"; include("room_inc_list.php"); ?>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){



	$(".room-list #btnAddRoom").unbind("click");
	$(".room-list #btnAddRoom").on("click",function(){
		aData = getDataRow($(".room-list .input-header"));
		if(aData==""){
			$.notify("No data changed");
			return;
		}
		aData["u_mode"]=$(this).attr("data-mode");


		startLoad($(".room-list #btnAddRoom,.room-list #btnCancelData"),$(".room-list #btnAddRoom-loader"));

		callAjax("room_a.php",aData,function(rtnObj,aData){

			if(rtnObj.res!="1"){
				$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$.notify("Data Saved","success");
				if($(".room-list #btnAddRoom").attr("data-mode")=="room_update"){
					objRow= $(".room-list .row-100ist .data-row[data-clinicid='"+aData.clinic_id+"'][data-roomno='"+aData.room_no+"']");
					$(".room-list .input-header").find(".saveinput").each(function(ix,objx){
						sKey = $(objx).attr("data-keyid");

						$(objRow).find(".showinput[data-keyid='"+sKey+"']").html($(objx).val());
					});
				}else if($(".room-list #btnAddRoom").attr("data-mode")=="room_add"){
					$(".room-list .row-body .row-100ist").prepend(rtnObj.msg);
				}
				$(".room-list #btnCancelData").trigger("click");
			}
			//

			endLoad($(".room-list #btnAddRoom,.room-list #btnCancelData"),$(".room-list #btnAddRoom-loader"));
		});
	});

	$(".room-list .row-100ist .btneditroom").unbind("click");
	$(".room-list .row-100ist").on("click",".btneditroom",function(){
		objRow=$(this).closest(".data-row");
		$(objRow).find(".showinput").each(function(ix,objx){
			sKey = $(objx).attr('data-keyid');
			sVal = $(objx).html().trim();
			$(".room-list .input-header").find(".saveinput[data-keyid='"+sKey+"']").val(sVal);
			$(".room-list .input-header").find(".saveinput[data-keyid='"+sKey+"']").attr("data-odata",sVal);
		});
		$(".room-list #btnAddRoom").attr("data-mode","room_update");
		$(".room-list .input-header .saveinput[data-pk='1']").attr('readonly',true);
	});

	$(".room-list .row-100ist .btnqueuelink").unbind("click");
	$(".room-list .row-100ist").on("click",".btnqueuelink",function(){
		sSite=$(this).attr('data-qlink');
		window.open("queue_index.php?site="+sSite);
	});


	$(".room-list .row-100ist .btnroomdelete").unbind("click");
	$(".room-list .row-100ist").on("click",".btnroomdelete",function(){
		if(confirm("Do you want to delete this record?")){

		}else{
			return;
		}
		objRow=$(this).closest(".data-row");
		sClinicId =$(objRow).attr("data-clinicid");
		sRoomNo =$(objRow).attr("data-roomno");

		aData={u_mode:"room_del",clinicid:sClinicId,roomno:sRoomNo};
		startLoad($(objRow).find(".room-action span"),$(objRow).find(".room-action .action-loader"));

		callAjax("room_a.php",aData,function(rtnObj,aData){

			if(rtnObj.res!="1"){
				$.notify("Data is not remove. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$.notify("Data Removed Saved","success");
				$(objRow).remove();
				$(".room-list #btnCancelData").trigger("click");
			}
			//
			endLoad($(objRow).find(".room-action span"),$(objRow).find(".room-action .action-loader"));
		});

	});

	$(".room-list #btnCancelData").unbind("click");
	$(".room-list #btnCancelData").on("click",function(){

		$(".input-header .saveinput").each(function(ix,objx){

			if($(objx).attr("data-default")){
				if($(objx).attr("data-default")!="") $(objx).val($(objx).attr("data-default"));
			}else{
				$(objx).val("");
			}
		});
		$("#btnAddRoom").attr("data-mode","room_add");
		$(".input-header .saveinput[data-pk='1']").removeAttr('readonly');
	});

});
</script>