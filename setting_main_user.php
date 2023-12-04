<?
	include("in_session.php");
	include_once("in_php_function.php");
?>
<style>
	.user-list .user-head{
		background-color: white;
	}
	.user-list .p-cmd{
		min-width: 50px;
	}
	.user-list .p-id{
		min-width: 70px;
	}
	.user-list .p-name{
		min-width: 150px;
	}
	.user-list .p-email{
		min-width: 150px;
	}
	.user-list .p-phone{
		min-width: 120px;
	}
	.user-list .p-status{
		min-width: 90px;
	}
	.user-list .p-li-lab{
		min-width: 100px;
		max-width: 100px;
	}
	.user-list .p-remark{
		min-width: 200px;
	}
	.user-list .p-action{
		text-align: center;
	}
	.user-list .p-action span{
		margin-right:5px;
	}

	.user-list input{
		width:80%;
	}
	.user-list{
		vertical-align: middle;
	}
	.user-list .user-row{
		max-height: 50px;
		min-height: 50px;
		vertical-align: middle;
		text-align: center;
		align-items: center;
	}
	.user-list .user-row:hover{
		filter:brightness(80%);
	}
	.user-list .user-head{
		max-height: 50px;
		font-size:12px;
	}
	.user-list .user-row:nth-child(even){
		background-color: white;
	}
	.user-list .user-row:nth-child(odd){
		background-color: #b8d1f3;
	}
	.user-list{
		font-size:small;
	}
	.row-is-selected{
		border:1px sold red;
	}

</style>
<div class='fl-wrap-col user-list'>
	<div class='fl-wrap-row user-head'>
		<div  id='btnCancelData' class='hideonsave fabtn fl-fix w-50 h-50 lh-50 fl-mid' >
			<i class=' fas fa-broom fa-2x'></i>
		</div>
		<div class='fl-fix p-id'>
			ID*<br/>
			<input id='txtStaffId' class='hideonsave' data-odata='' maxlength="100" />
		</div>
		<div class='fl-fix p-name'>
			Name<br/>
			<input id='txtName' class='hideonsave' data-odata='' maxlength="150" />
		</div>
		<div class='fl-fix p-email'>
			Email*<br/>
			<input id='txtEmail' class='hideonsave' data-odata=''  maxlength="200" />
		</div>
		<div class='fl-fix p-phone'>
			Phone<br/>
			<input id='txtPhone' class='hideonsave' data-odata=''  maxlength="100" />
		</div>
		<div class='fl-fix p-status'>
			Status<br/>
			<SELECT id='ddlStaffStatus' class='hideonsave' data-odata=''>
				<option value='1'>Enable</option>
				<option value='0'>Disable</option>
			</SELECT>
		</div>
		<div class='fl-fix p-li-lab'>
			License Lab <br/>
			<input id='txtLicenseLab' class='hideonsave' data-odata=''  maxlength="100"  />
		</div>
		<div class='fl-fix p-remark'>
			Remark <br/>
			<input id='txtRemark' class='hideonsave' data-odata=''  maxlength="1000"  />
		</div>
		<div class='fl-fill p-action'>
			Control<br/>
			<span style='color:green'><i id='btnAddUser' class="fabtn fas fa-plus-square fa-2x" data-mode='user_add'></i><i id='imgAddSpinner' style='display:none' class="fas fa-spinner fa-spin fa-2x"></i></span>
		</div>
	</div>
	<div class='fl-wrap-col userrow-body fl-auto' >
		<div class='fl-fill userrow-list'>
			<? include("list_user.php"); ?>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){

		$("#btnAddUser").unbind("click");
		$("#btnAddUser").on("click",function(){
			sId= $("#txtStaffId").val().trim();
			sName= $("#txtName").val().trim();
			sEmail= $("#txtEmail").val().trim();
			sPhone= $("#txtPhone").val().trim();
			sStatus= $("#ddlStaffStatus").val().trim();
			sLiLab= $("#txtLicenseLab").val().trim();
			sRemark= $("#txtRemark").val().trim();
			sMode = $("#btnAddUser").attr("data-mode");
			var aData = {u_mode:sMode,pid:sId,name:sName,email:sEmail,phone:sPhone,status:sStatus,lilab:sLiLab,remark:sRemark};
			if(sId=="" || sEmail=="" || checkEmail(sEmail)==false){
				$.notify("Please complete require input.");
				return;
			}

			$(".hideonsave").hide();

			startLoad($("#btnAddUser"),$("#imgAddSpinner"));
			callAjax("setting_a_user.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");

				}else if(rtnObj.res=="1"){
					$.notify("Data Saved","success");
					if($("#btnAddUser").attr("data-mode")=="user_update"){
						sId = $("#txtStaffId").val();
						sTRow = $(".userrow-body").find(".btnedituser[data-userid='"+sId+"']");
						sObj = $(sTRow).closest(".user-row");
						$(sObj).find(".p-name").find("span").html($("#txtName").val().trim());
						$(sObj).find(".p-email").find("span").html($("#txtEmail").val().trim());
						$(sObj).find(".p-phone").find("span").html($("#txtPhone").val().trim());
						$(sObj).find(".p-status").find(".ddlstaffstatus").val($("#ddlStaffStatus").val());
						$(sObj).find(".p-li-lab").find("span").html($("#txtLicenseLab").val().trim());
						$(sObj).find(".p-remark").find("span").html($("#txtRemark").val().trim());
					}else if($("#btnAddUser").attr("data-mode")=="user_add"){
						$(".userrow-body .userrow-list").prepend(rtnObj.msg);
					}
					$("#btnCancelData").trigger("click");
				}
				//
				$(".hideonsave").show();
				endLoad($("#btnAddUser"),$("#imgAddSpinner"));
			});
		});

		$("#btnCancelData").unbind("click");
		$("#btnCancelData").on("click",function(){
			$(".user-head input").val("");
			$(".user-head input").attr("data-odata","");
			$("#txtUserId").removeAttr("readonly");
			$("#ddlStaffStatus").val("1");
			$("#ddlStaffStatus").attr("data-odata","");
			$("#btnAddUser").attr("data-mode","user_add");
		});

		$(".userrow-body .btnedituser").unbind("click");
		$(".userrow-body").on("click",".btnedituser",function(){
			sObj = $(this).closest(".user-row");
			sId = $(sObj).find(".p-id").find("span").html();
			sName = $(sObj).find(".p-name").find("span").html();
			sEmail = $(sObj).find(".p-email").find("span").html();
			sPhone = $(sObj).find(".p-phone").find("span").html();
			sLicenseLab = $(sObj).find(".p-li-lab").find("span").html();
			sStatus = $(sObj).find(".p-status").find(".ddlstaffstatus").val();
			sRemark = $(sObj).find(".p-remark").find("span").html();

			sUrl="user_dlg_profile_edit.php?s_id="+sId;
			showDialog(sUrl,"Profile Editor","90%","800","",
			function(sResult){
				//CLose function
				if(sResult=="REFRESH"){
					$("#divUSetting #divProfilePreview").load("staff_inc_user_profile_preview.php",function(){
					});
				}
			},false,function(){});

			/*
			$("#txtStaffId").attr("readonly",true);
			$("#txtStaffId").val(sId);
			$("#txtStaffId").attr("data-odata",sId);
			$("#txtName").val(sName);
			$("#txtName").attr("data-odata",sName);
			$("#txtEmail").val(sEmail);
			$("#txtEmail").attr("data-odata",sEmail);
			$("#txtPhone").val(sPhone);
			$("#txtPhone").attr("data-odata",sPhone);
			$("#ddlStaffStatus").val(sStatus);
			$("#ddlStaffStatus").attr("data-odata",sStatus);
			$("#txtLicenseLab").val(sLicenseLab);
			$("#txtLicenseLab").attr("data-odata",sLicenseLab);
			$("#txtRemark").val(sRemark);
			$("#txtRemark").attr("data-odata",sRemark);
			$("#btnAddUser").attr("data-mode","user_update");
			*/
		});

		$(".userrow-body .btnpassword").unbind("click");
		$(".userrow-body").on("click",".btnpassword",function(){
			sId = $(this).attr("data-sid");
			sUrl = "user_dlg_reset_pass.php?sid="+sId;
			showDialog(sUrl,"Change Password","400","300","",function(sResult){
				//CLose function
				if(sResult=="1"){
					$.notify("Password Changed.","success");
				}

			},false,function(){
				//Load Done Function
			});
		});

		$(".userrow-body .btnusergroup").unbind("click");
		$(".userrow-body").on("click",".btnusergroup",function(){
			sId = $(this).attr("data-sid");
			objRow = $(this).closest(".user-row");
			sName = $(objRow).find(".p-name").find("span").html();
			sUrl = "user_dlg_clinic_auth.php?sid="+sId;
			showDialog(sUrl,"Section Management for : ["+sId+"] :"+sName,"90%","90%","",function(sResult){
				//CLose function
				if(sResult=="1"){
					$.notify("Password Changed.","success");
				}

			},false,function(){
				//Load Done Function
			});
		});

	});
</script>