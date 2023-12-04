<?
	include("in_session.php");
	include_once("in_php_function.php");
?>

<div class='fl-wrap-col proj-list'>
	<div class='fl-wrap-row fs-s fl-mid h-xs row-color-2'>
		<div class='fl-fix proj-cmd w-m'>
		</div>
		<div class='fl-fix proj-id w-s'>
			ID*
		</div>
		<div class='fl-fill proj-name'>
			Title
		</div>
		<div class='fl-fill proj-desc'>
			Desc
		</div>
		<div class='fl-fill proj-remark'>
			Remark
		</div>
		<div class='fl-fix proj-group_amt w-s'>
			Group
		</div>
		<div class='fl-fill proj-pid-format'>
			PID Format
		</div>
		<div class='fl-fill proj-pid-running-digit w-s'>
			Digit
		</div>
		<div class='fl-fix proj-enable w-sm'>
			Enable
		</div>
		<div class='fl-fix proj-action w-l'>
			Control
		</div>
	</div>
	<div class='fl-wrap-row proj-head fs-s fl-mid h-ss  row-color-2'>
		<div class='fl-fix proj-cmd w-m fl-mid'>
			<i id='btnCancelData' class='fabtn fas fa-broom fa-2x'></i>
		</div>
		<div class='fl-fix w-s'>
			<input id='txtProjId' class='fs-s fill-box saveinput mar-topdown' data-odata='' data-keyid='proj_id' maxlength="50" data-pk="1" />
		</div>
		<div class='fl-fill '>
			<input id='txtProjName' class='fill-box saveinput mar-topdown' data-odata='' data-keyid='proj_name' data-odata='' maxlength="150" />
		</div>
		<div class='fl-fill '>
			<input id='txtProjDesc' class='fill-box saveinput mar-topdown' data-odata='' data-keyid='proj_desc' data-odata='' maxlength="500" />
		</div>
		<div class='fl-fill '>
			<input id='txtProjRemark' class='fill-box saveinput mar-topdown' data-odata='' data-keyid='proj_remark' maxlength="500" />
		</div>
		<div class='fl-fix  w-s'>
			<input id='txtProjGrpAmt' class='fill-box saveinput mar-topdown' data-odata='' data-keyid='proj_group_amt' />
		</div>
		<div class='fl-fill '>
			<input id='txtProjPIDFormat' class='fill-box saveinput mar-topdown' data-odata='' data-keyid='proj_pid_format' />
		</div>
		<div class='fl-fill w-s'>
			<input id='txtProjPIDRunning' class='fill-box saveinput mar-topdown' data-odata='' data-keyid='proj_pid_runing_digit' />
		</div>
		<div class='fl-fix w-sm'>
			<SELECT id='ddlProjIsEnable' class='fill-box saveinput' data-odata='' data-keyid='is_enable'>
				<option value='1'>Enable</option>
				<option value='0'>Disable</option>
			</SELECT>
		</div>
		<div class='fl-fix proj-action w-l'>
			<span style='color:green'><i id='btnAddProj' class="fabtn fas fa-plus-square fa-2x" data-mode='proj_add'></i><i id='btnAddProj-loader' style='display:none' class="fas fa-spinner fa-spin fa-2x"></i></span>
		</div>
	</div>
	<div class='fl-wrap-col projrow-body fl-auto' >
		<div class='fl-fill projrow-list'>
		<? include("project_inc_list.php"); ?>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){

	$(".proj-list #btnAddProj").unbind("click");
	$(".proj-list #btnAddProj").on("click",function(){
		aData = getDataRow($(".proj-list .proj-head"));
		if(aData==""){
			$.notify("No data changed");
			return;
		}
		aData["u_mode"]=$(this).attr("data-mode");


		startLoad($("#btnAddProj,.proj-list #btnCancelData"),$("#btnAddProj-loader"));

		callAjax("project_a_info.php",aData,function(rtnObj,aData){

			if(rtnObj.res!="1"){
				$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$.notify("Data Saved","success");
				if($("#btnAddProj").attr("data-mode")=="proj_update"){
					objRow= $(".projrow-list .proj-row[data-projid='"+aData.proj_id+"']");
					$(".proj-head").find(".saveinput").each(function(ix,objx){
						sKey = $(objx).attr("data-keyid");

						$(objRow).find(".showinput[data-keyid='"+sKey+"']").html($(objx).val());
					});
				}else if($("#btnAddProj").attr("data-mode")=="proj_add"){
					$(".projrow-body .projrow-list").prepend(rtnObj.msg);
				}
				$(".proj-list #btnCancelData").trigger("click");
			}
			//
			endLoad($("#btnAddProj,.proj-list #btnCancelData"),$("#btnAddProj-loader"));
		});
	});

	$(".projrow-list .btneditproj").unbind("click");
	$(".projrow-list .btneditproj").on("click",function(){
		objRow=$(this).closest(".proj-row");
		$(objRow).find(".showinput").each(function(ix,objx){
			sKey = $(objx).attr('data-keyid');
			sVal = $(objx).html().trim();
			$(".proj-head").find(".saveinput[data-keyid='"+sKey+"']").val(sVal);
			$(".proj-head").find(".saveinput[data-keyid='"+sKey+"']").attr("data-odata",sVal);
		});
		$("#btnAddProj").attr("data-mode","proj_update");
		$(".proj-head .saveinput[data-pk='1']").attr('readonly',true);
	});

	$(".projrow-list .btnprojdelete").unbind("click");
	$(".projrow-list .btnprojdelete").on("click",function(){
		if(confirm("Do you want to delete this record?")){

		}else{
			return;
		}
		objRow=$(this).closest(".proj-row");
		sProjId =$(objRow).attr("data-projid");

		aData={u_mode:"proj_del",projid:sProjId};
		startLoad($(objRow).find(".proj-action span"),$(objRow).find(".proj-action .action-loader"));

		callAjax("project_a_info.php",aData,function(rtnObj,aData){

			if(rtnObj.res!="1"){
				$.notify("Data is not remove. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$.notify("Data Removed Saved","success");
				$(objRow).remove();
				$("#btnCancelData").trigger("click");
			}
			//
			endLoad($(objRow).find(".proj-action span"),$(objRow).find(".proj-action .action-loader"));
		});

	});


	$(".projrow-list .btnprojauth").unbind("click");
	$(".projrow-list .btnprojauth").on("click",function(){
		objRow=$(this).closest(".data-row");
		sProjId = $(objRow).attr('data-projid');


		sUrl = "setting_main_project_auth.php?projid="+sProjId;
		showDialog(sUrl,"Project Authorization management : "+sProjId,"600","1024","",function(sResult){
			//CLose function
			if(sResult=="1"){
				//$.notify("Password Changed.","success");
			}

		},false,function(){
			//Load Done Function
			$.notify("Please select Staff");
		});

	});

	$(".proj-list #btnCancelData").unbind("click");
	$(".proj-list #btnCancelData").on("click",function(){
		$(".proj-head .saveinput").val("");
		$("#btnAddProj").attr("data-mode","proj_add");
		$(".proj-head .saveinput[data-pk='1']").removeAttr('readonly');
	});

});
</script>