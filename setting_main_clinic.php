<?
	include("in_session.php");
	include_once("in_php_function.php");
?>

<div class='fl-wrap-col clinic-list'>
	<div class='fl-wrap-row fs-s fl-mid h-25 row-color-2 row-header'>
		<div class='fl-fix w-40'>
		</div>
		<div class='fl-fix w-80'>
			ID*
		</div>
		<div class='fl-fill '>
			Name
		</div>
		<div class='fl-fill '>
			Address
		</div>
		<div class='fl-fix w-150'>
			Email
		</div>
		<div class='fl-fix w-90'>
			Phone
		</div>
		<div class='fl-fix w-50'>
			Enable
		</div>
		<div class='fl-fix w-80'>
			Main
		</div>
		<div class='fl-fix w-80'>
			Old ID
		</div>		
		<div class='fl-fix w-200'>
			Control
		</div>
	</div>
	<div class='fl-wrap-row input-header fs-small fl-mid h-30  row-color-2 row-header'>
		<div class='fl-fix w-40 fl-mid'>
			<i id='btnCancelData' class='fabtn fas fa-broom fa-2x'></i>
		</div>
		<div class='fl-fix w-80'>
			<input id='txtClinicId' class='fill-box mar-topdown saveinput' data-odata='' data-keyid='clinic_id' maxlength="50" data-pk="1" />
		</div>
		<div class='fl-fill '>
			<input id='txtClinicName' class='fill-box mar-topdown saveinput' data-odata='' data-keyid='clinic_name' maxlength="200" />
		</div>
		<div class='fl-fill '>
			<input id='txtClinicAdd' class='fill-box mar-topdown saveinput' data-odata='' data-keyid='clinic_address' />
		</div>
		<div class='fl-fix w-150'>
			<input id='txtClinicEmail' class='fill-box mar-topdown saveinput' data-odata='' data-keyid='clinic_email' />
		</div>
		<div class='fl-fix w-90'>
			<input id='txtClinicPhone' class='fill-box mar-topdown saveinput' data-odata='' data-keyid='clinic_tel' maxlength="20" />
		</div>
		<div class='fl-fix w-50'>
			<SELECT id='ddlClinicStatus' class='fill-box saveinput' data-odata='' data-keyid='clinic_status'>
				<option value='1'>Enable</option>
				<option value='0'>Disable</option>
			</SELECT>
		</div>
		<div class='fl-fix w-80'>
			<input id='txtClinicMain' class='fill-box mar-topdown saveinput' data-odata='' data-keyid='main_clinic_id' />
		</div>
		<div class='fl-fix w-80'>
			<input id='txtClinicOld' class='fill-box mar-topdown saveinput' data-odata='' data-keyid='old_clinic_id' />
		</div>		
		<div class='fl-fix w-200 fl-mid'>
			<span style='color:green'><i id='btnAddClinic' class="fabtn fas fa-plus-square fa-2x" data-mode='clinic_add'></i><i id='btnAddClinic-loader' style='display:none' class="fas fa-spinner fa-spin fa-2x"></i></span>
		</div>


	</div>
	<div class='fl-wrap-col clinicrow-body fl-auto' >
		<div class='fl-fill clinicrow-list'>
		<? $_GET["u_mode"]="clinic-list"; include("clinic_opt_list.php"); ?>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){



	$("#btnAddClinic").unbind("click");
	$("#btnAddClinic").on("click",function(){
		aData = getDataRow($(".input-header"));
		if(aData==""){
			$.notify("No data changed");
			return;
		}
		aData["u_mode"]=$(this).attr("data-mode");


		startLoad($("#btnAddClinic,#btnCancelData"),$("#btnAddClinic-loader"));

		callAjax("clinic_a.php",aData,function(rtnObj,aData){

			if(rtnObj.res!="1"){
				$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$.notify("Data Saved","success");
				if($("#btnAddClinic").attr("data-mode")=="clinic_update"){
					objRow= $(".clinicrow-list .data-row[data-clinicid='"+aData.clinic_id+"']");
					$(".input-header").find(".saveinput").each(function(ix,objx){
						sKey = $(objx).attr("data-keyid");

						$(objRow).find(".showinput[data-keyid='"+sKey+"']").html($(objx).val());
					});
				}else if($("#btnAddClinic").attr("data-mode")=="clinic_add"){
					$(".clinicrow-body .clinicrow-list").prepend(rtnObj.msg);
				}
				$("#btnCancelData").trigger("click");
			}
			//
			endLoad($("#btnAddClinic,#btnCancelData"),$("#btnAddClinic-loader"));
		});
	});



	$(".clinicrow-list .btnedit").unbind("click");
	$(".clinicrow-list").on("click",".btnedit",function(){
		objRow=$(this).closest(".data-row");
		$(objRow).find(".showinput").each(function(ix,objx){
			sKey = $(objx).attr('data-keyid');
			sVal = $(objx).html().trim();
			$(".input-header").find(".saveinput[data-keyid='"+sKey+"']").val(sVal);
			$(".input-header").find(".saveinput[data-keyid='"+sKey+"']").attr("data-odata",sVal);
		});
		$("#btnAddClinic").attr("data-mode","clinic_update");
		$(".input-header .saveinput[data-pk='1']").attr('readonly',true);
	});

	$(".clinicrow-list .btnqueuelink").unbind("click");
	$(".clinicrow-list").on("click",".btnqueuelink",function(){
		sSite=$(this).attr('data-qlink');
		window.open("queue_index.php?site="+sSite);
	});

	$(".clinicrow-list .btnDocument").unbind("click");
	$(".clinicrow-list").on("click",".btnDocument",function(){
		objRow=$(this).closest(".data-row");
		sClinicId =$(objRow).attr("data-clinicid");
		sUrl="document_inc_main.php?clinicid="+sClinicId;
		showDialog(sUrl,"Document Management Authorization For "+sClinicId,"500","1000","",function(sResult){
			//CLose function
			if(sResult=="1"){
				//$.notify("Password Changed.","success");
			}

		},false,function(){
			//Load Done Function
		});
	});

	$(".clinicrow-list .btnclinicroom").unbind("click");
	$(".clinicrow-list").on("click",".btnclinicroom",function(){
		objRow=$(this).closest(".data-row");
		sClinicId =$(objRow).attr("data-clinicid");
		sUrl="setting_inc_room.php?clinicid="+sClinicId;
		showDialog(sUrl,"Room for "+sClinicId,"480","1024","",function(sResult){
			//CLose function
			if(sResult=="1"){
				//$.notify("Password Changed.","success");
			}

		},false,function(){
			//Load Done Function
		});
	});

	$(".clinicrow-list .btnholiday").unbind("click");
	$(".clinicrow-list").on("click",".btnholiday",function(){
		objRow=$(this).closest(".data-row");
		sClinicId =$(objRow).attr("data-clinicid");
		sUrl="clinic_inc_holiday.php?clinicid="+sClinicId;
		showDialog(sUrl,"Room for "+sClinicId,"380","350","",function(sResult){
			//CLose function
			if(sResult=="1"){
				//$.notify("Password Changed.","success");
			}

		},false,function(){
			//Load Done Function
		});
	});

	$(".clinicrow-list .btndelete").unbind("click");
	$(".clinicrow-list").on("click",".btndelete",function(){
		if(confirm("Do you want to delete this record?")){

		}else{
			return;
		}
		objRow=$(this).closest(".data-row");
		sClinicId =$(objRow).attr("data-clinicid");

		aData={u_mode:"clinic_del",clinicid:sClinicId};
		startLoad($(objRow).find(".clinic-action span"),$(objRow).find(".clinic-action .action-loader"));

		callAjax("clinic_a.php",aData,function(rtnObj,aData){

			if(rtnObj.res!="1"){
				$.notify("Data is not remove. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$.notify("Data Removed Saved","success");
				$(objRow).remove();
				$("#btnCancelData").trigger("click");
			}
			//
			endLoad($(objRow).find(".clinic-action span"),$(objRow).find(".clinic-action .action-loader"));
		});

	});

	$("#btnCancelData").unbind("click");
	$("#btnCancelData").on("click",function(){
		$(".input-header .saveinput").val("");
		$("#btnAddClinic").attr("data-mode","clinic_add");
		$(".input-header .saveinput[data-pk='1']").removeAttr('readonly');
	});

});
</script>