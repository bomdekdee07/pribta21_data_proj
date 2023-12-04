<?
	include("in_session.php");
	include_once("in_php_function.php");
	$sProjId = getQS("projid");
?>

<div class='fl-wrap-col projauth-list'>
	<div class='fl-wrap-row fs-s fl-mid h-xs row-color-2 row-header'>
		<div class='fl-fix w-s'>
		</div>
		<div class='fl-fix w-m'>
			User <span id='btnFindUser' title='Find User' style='color:green'><i class='fabtn fa fa-user'></i></span>
		</div>
		<div class='fl-fill '>
			Project
		</div>
		<div class='fl-fix w-s'>
			View
		</div>
		<div class='fl-fix w-s'>
			Enroll
		</div>
		<div class='fl-fix w-s'>
			Schedule
		</div>
		<div class='fl-fix w-s'>
			Data
		</div>
		<div class='fl-fix w-s'>
			Log
		</div>
		<div class='fl-fix w-s'>
			Lab
		</div>		
		<div class='fl-fix w-s'>
			Export
		</div>
		<div class='fl-fix w-s'>
			Query
		</div>
		<div class='fl-fix w-s'>
			Delete
		</div>
		<div class='fl-fix w-s'>
			Backdate
		</div>
		<div class='fl-fix w-s'>
			Admin
		</div>
		<div class='fl-fix w-m'>
			Control
		</div>
	</div>
	<div class='fl-wrap-row input-header fs-s fl-mid h-s row-color-2 row-header' style='line-height: 15px' >
		<div class='fl-fix w-s'>
			<i id='btnCancelData' class='fabtn fas fa-broom fa-2x'></i>
		</div>
		<div class='fl-wrap-col w-m'>
			<div class='fl-fill'><input id='txtSid' class='mar-topdown saveinput' style='width:70%' data-odata='' data-keyid='s_id' maxlength="50" data-pk="1" /><i id='txtSid-loader' class='fa fa-spinner fa-spin' style='display:none'></i></div>
			<div class='fl-fill'><span class='fs-xs' id='spanName'></span></div>
		</div>
		<div class='fl-fill '>
			<SELECT id='ddlProjId' class='fill-box saveinput' data-odata='' data-keyid='proj_id' data-pk="1" data-default='<? echo($sProjId); ?>'>
				<? $_GET["opt"]=1; include("project_inc_list.php"); ?>
			</SELECT>
		</div>
		<div class='fl-fix w-s'>
			<SELECT id='ddlAllowView' class=' saveinput' data-odata='' data-keyid='allow_view'  data-default='0'>
				<option value='1'>Yes</option>
				<option value='0'>No</option>
			</SELECT>
		</div>
		<div class='fl-fix w-s'>
			<SELECT id='ddlAllowEnroll' class=' saveinput' data-odata='' data-keyid='allow_enroll'  data-default='0'>
				<option value='1'>Yes</option>
				<option value='0'>No</option>
			</SELECT>
		</div>
		<div class='fl-fix w-s'>
			<SELECT id='ddlAllowSched' class=' saveinput' data-odata='' data-keyid='allow_schedule' data-default='0'>
				<option value='1'>Yes</option>
				<option value='0'>No</option>
			</SELECT>
		</div>
		<div class='fl-fix w-s'>
			<SELECT id='ddlAllowData' class=' saveinput' data-odata='' data-keyid='allow_data' data-default='0'>
				<option value='1'>Yes</option>
				<option value='0'>No</option>
			</SELECT>
		</div>
		<div class='fl-fix w-s'>
			<SELECT id='ddlAllowLog' class=' saveinput' data-odata='' data-keyid='allow_data_log' data-default='0'>
				<option value='1'>Yes</option>
				<option value='0'>No</option>
			</SELECT>
		</div>
		<div class='fl-fix w-s'>
			<SELECT id='ddlAllowLab' class=' saveinput' data-odata='' data-keyid='allow_lab' data-default='0'>
				<option value='1'>Yes</option>
				<option value='0'>No</option>
			</SELECT>
		</div>		
		<div class='fl-fix w-s'>
			<SELECT id='ddlAllowExport' class=' saveinput' data-odata='' data-keyid='allow_export' data-default='0'>
				<option value='1'>Yes</option>
				<option value='0'>No</option>
			</SELECT>
		</div>
		<div class='fl-fix w-s'>
			<SELECT id='ddlAllowQuery' class=' saveinput' data-odata='' data-keyid='allow_query' data-default='0'>
				<option value='1'>Yes</option>
				<option value='0'>No</option>
			</SELECT>
		</div>
		<div class='fl-fix w-s'>
			<SELECT id='ddlAllowDelete' class=' saveinput' data-odata='' data-keyid='allow_delete' data-default='0'>
				<option value='1'>Yes</option>
				<option value='0'>No</option>
			</SELECT>
		</div>
		<div class='fl-fix w-s'>
			<SELECT id='ddlAllowBackdate' class=' saveinput' data-odata='' data-keyid='allow_data_backdate' data-default='0'>
				<option value='1'>Yes</option>
				<option value='0'>No</option>
			</SELECT>
		</div>
		<div class='fl-fix w-s'>
			<SELECT id='ddlAllowAdmin' class=' saveinput' data-odata='' data-keyid='allow_admin' data-default='0'>
				<option value='1'>Yes</option>
				<option value='0'>No</option>
			</SELECT>
		</div>
		<div class='fl-fix w-m'>
			<span style='color:green'><i id='btnAdd' class="fabtn fas fa-plus-square fa-2x" data-mode='projauth_add'></i><i id='btnAdd-loader' style='display:none' class="fas fa-spinner fa-spin fa-2x"></i></span>
		</div>

	</div>
	<div class='fl-wrap-col row-list ' style='overflow-y: scroll' >
		<? $_GET["u_mode"]="projauth-list"; $_GET["opt"]="0"; include("project_auth_inc_list.php"); ?>

	</div>
</div>

<script>
$(document).ready(function(){

	$(".projauth-list #txtSid").unbind("change");
	$(".projauth-list #txtSid").on("change",function(){
		$(".projauth-list .row-list .data-row").hide();
		sVal = $(this).val().toLowerCase();

		//$(".projauth-list .row-list .showinput[data-keyid='s_id']").filter(function(){ 
			//return $(this).text().toLowerCase()==sVal;
		//}).show();

		$(".projauth-list .row-list .showinput[data-keyid='s_id']:Contains('"+sVal+"'),.projauth-list .row-list .showinput[data-keyid='s_name']:Contains('"+sVal+"')").closest(".data-row").show();
	});

	$(".projauth-list #btnAdd").unbind("click");
	$(".projauth-list #btnAdd").on("click",function(){
		aData = getDataRow($(".projauth-list .input-header"));
		if(aData==""){
			$.notify("No data changed");
			return;
		}
		aData["u_mode"]=$(this).attr("data-mode");

		startLoad($(".projauth-list #btnAdd,.projauth-list #btnCancelData"),$(".projauth-list #btnAdd-loader"));
		callAjax("project_auth_a.php",aData,function(rtnObj,aData){

			if(rtnObj.res!="1"){
				$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$.notify("Data Saved","success");

				if($(".projauth-list #btnAdd").attr("data-mode")=="projauth_update"){
					objRow= $(".projauth-list .row-list .data-row[data-projid='"+aData.proj_id+"'][data-sid='"+aData.s_id+"']");
					$(".projauth-list .input-header").find(".saveinput").each(function(ix,objx){
						sKey = $(objx).attr("data-keyid");
						sValue = "";
						if($(objx).val()=="1"){
							sValue="<i class='fas fa-check-circle'></i>";
						}

						objx = $(objRow).find(".showinput[data-keyid='"+sKey+"']")
						if(sKey=="s_id" || sKey=="proj_id"){
							
						}else{
							$(objx).html(sValue);
						}
						
					});
				}else if($(".projauth-list #btnAdd").attr("data-mode")=="projauth_add"){
					$(".projauth-list .row-list").prepend(rtnObj.msg);
					sName = $(".projauth-list #spanName").html();
				}
				$(".projauth-list #btnCancelData").trigger("click");
			}
			//
			endLoad($(".projauth-list #btnAdd,.projauth-list #btnCancelData"),$(".projauth-list #btnAdd-loader"));
		});
	});

	$(".projauth-list .btnedit").unbind("click");
	$(".projauth-list ").on("click",".btnedit",function(){
		/*
		objRow=$(this).closest(".data-row");

		$(objRow).find(".showinput").each(function(ix,objx){
			sKey = $(objx).attr('data-keyid');
			sVal = $(objx).html().trim();
			$(".projauth-list .input-header").find(".saveinput[data-keyid='"+sKey+"']").val(sVal);
			$(".projauth-list .input-header").find(".saveinput[data-keyid='"+sKey+"']").attr("data-odata",sVal);
		});
		$(".projauth-list #btnAdd").attr("data-mode","projauth_update");
		$(".projauth-list .input-header .saveinput[data-pk='1']").attr('readonly',true);
		*/
		objRow=$(this).closest(".data-row");
		sId = $(objRow).attr("data-sid");
		$("#txtSid").val(sId);
		$(".input-header #btnAdd").attr('data-mode',"projauth_update");
		searchSidInfo(sId);
	});


	$(".projauth-list .row-list .btndelete").unbind("click");
	$(".projauth-list .row-list").on("click",".btndelete",function(){
		if(confirm("Do you want to delete this record?")){

		}else{
			return;
		}
		objRow=$(this).closest(".data-row");
		sProjId =$(objRow).attr("data-projid");
		sSid =$(objRow).attr("data-sid");

		aData={u_mode:"projauth_del",projid:sProjId,sid:sSid};
		startLoad($(objRow).find(".action-control span"),$(objRow).find(".action-control .action-loader"));

		callAjax("project_auth_a.php",aData,function(rtnObj,aData){

			if(rtnObj.res!="1"){
				$.notify("Data is not remove. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$.notify("Data Removed Saved","success");
				$(objRow).remove();
				$(".projauth-list #btnCancelData").trigger("click");
			}
			//
			endLoad($(objRow).find(".action-control span"),$(objRow).find(".action-control .action-loader"));
		});

	});

	$(".projauth-list #btnCancelData").unbind("click");
	$(".projauth-list #btnCancelData").on("click",function(){

		$(".projauth-list .input-header .saveinput").each(function(ix,objx){
			if($(objx).attr("data-pk")=="1"){

			}else{
				$(objx).attr('data-odata','');
			}
			if($(objx).attr("data-default")){
				if($(objx).attr("data-default")!="") $(objx).val($(objx).attr("data-default"));
			}else{
				$(objx).val("");
			}
		});
		$(".projauth-list #btnAdd").attr("data-mode","projauth_add");
		$(".projauth-list #spanName").html("");
		$(".projauth-list .input-header .saveinput[data-pk='1']").removeAttr('readonly');
	});

	$(".projauth-list #btnFindUser").unbind("click");
	$(".projauth-list #btnFindUser").on("click",function(){
		objRow=$(this).closest(".data-row");
		sUrl = "staff_dlg_list.php";
		showDialog(sUrl,"Select User ","600","1024","",function(sResult){
			//CLose function
			if(sResult!=""){
				$("#txtSid").val(sResult);
				searchSidInfo(sResult);
			}
		},false,function(){
			//Load Done Function
		});
	});

	function searchSidInfo(sSid){
		startLoad($("#txtSid,.projauth-list .saveinput"),$("#txtSid-loader"));
		sProjId = $("#ddlProjId").val();
		aData={u_mode:"projauth_find",projid:sProjId,sid:sSid};
		$(".projauth-list #txtSid").attr("readonly",true);
		callAjax("project_auth_a.php",aData,function(rtnObj,aData){
			//$.notify(rtnObj["res"]);
			if(rtnObj.res!="1"){
				$.notify("Staff not found. Please try again\r\n"+rtnObj.msg,"error");
			}else if(rtnObj.res=="1"){
				$("#spanName").html(rtnObj.s_name);
				$(".projauth-list .saveinput").each(function(ix,objx){
					keyCol = $(objx).attr("data-keyid");
					if(rtnObj[keyCol] != undefined){
						$(objx).val(rtnObj[keyCol]);
						$(objx).attr("data-odata",rtnObj[keyCol]);
					}
				});
				if(rtnObj.isnew=="1"){
					$(".projauth-list #btnAdd").attr("data-mode","projauth_add");
				}else{
					$(".projauth-list #btnAdd").attr("data-mode","projauth_update");
				}
			}
			//
			endLoad($("#txtSid,.projauth-list .saveinput"),$("#txtSid-loader"));
		});
	}
});
</script>