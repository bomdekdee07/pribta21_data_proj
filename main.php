<?
include("in_session.php");
include_once("in_php_function.php");
$sName = getSS("s_name");
$sClinic = getSS("clinic_id");
?>


<div class='fl-wrap-row h-30 lh-30 fs-xsmall' style='background-color: #CABDCD'>
	<div class='fl-fix fl-mid w-25' ><img class='fill-box' src='assets/image/pribta_logo_mini.png' /></div>
	<div class='fl-fix w-200 fl-mid' style='color:white'>
		<i class='fa fa-clinic-medical fa-2x'> <? echo($sClinic); ?></i>
	</div>
	<div id='divRoom' class='fl-wrap-row' style='max-width:255px;display:none'>
		<div class='fl-fill' style='min-width: 200px'>
			<SELECT id='ddlRoomList' style='width:100%'><? include("room_opt_list.php"); ?></SELECT>
		</div>
		<div class='fl-fix' style='min-width:50px'>
			<i id='btnEnterRoom' style='margin-left:10px' title='Get In/Out room' class='fabtn fa fa-door-open fa-2x'></i>
		</div>
	</div>
	<div id='divRoomLoader' class='fl-fill' style='display:none;max-width:255px'><i class='fa fa-spinner fa-spin'></i></div>
	<div class='fl-wrap-row' style=''>
		<? include("menu_inc_link.php"); ?>
	</div>

	<div id='divAdminBtn' class='fl-fill' style='text-align:right;display:none;margin-right:10px'>
		<? include("main_inc_setting_menu.php"); ?>| 
	</div>
	<div class='fl-fix' style='max-width:30px;text-align:right;margin-right:5px'>
		<i class="btnadminmenu fabtn fas fa-cogs fa-2x" title='Admin Menu' style=''></i> 
	</div>
	<div class='fl-fill btnHidden' style='max-width:150px;text-align:right;margin-right:10px'><? echo($sName); ?>
		<input type='hidden' id='txtSS' value='<? echo($sSessKey); ?>' />
	</div>

	<div class='fl-fix' style='min-width:30px;height:60px'><i id='btnLogout' title='Logout'  class="fabtn fas fa-sign-out-alt fa-2x"></i></div>
	
</div>

<div id='divContentBody' class='fl-wrap-row' style='background-color: #CABDCD'>
	<?  if(isset($sDefault)){
			if($sDefault!="") include($sDefault);
			
		}else{
			
		}
	?>
</div>
<div id='divContentLoading' class='fl-wrap-row' style='display:none;background-color: #CABDCD'>
	<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
</div>
<div class='fl-fix smallfont' style='min-height:20px;line-height:20px;background-color: silver' >
	@PRIBTA 2021 
</div>

<script>
function getSessKey(){
	return($("#txtSS").val());
}

$(document).ready(function(){

	$(".btndlglink").unbind("click");
	$(".btndlglink").on("click",function(){
		//if the divContent is on load. Ignore double click
		if(!$("#divContentBody").is(":visible")){
			return;
		}
		let sLink = $(this).attr("data-link");
		let sTitle = $(this).attr("title");
		let sH = $(this).attr("data-h");
		let sW = $(this).attr("data-w");

		if(sH==undefined) sH="600";
		if(sW==undefined) sW="800";

		showDialog(sLink+".php",sTitle,sH,sW,"",
		function(sResult){
			//CLose function
			if(sResult=="1"){
			}
		},false,function(){
			//Load Done Function
		});
	});

	$(".btnlink").unbind("click");
	$(".btnlink").on("click",function(){
		//if the divContent is on load. Ignore double click
		if(!$("#divContentBody").is(":visible")){
			return;
		}
		let sLink = $(this).attr("data-link");
		startLoad($("#divContentBody"),$("#divContentLoading"));
		$("#divContentBody").load(sLink+".php",function(){
			endLoad($("#divContentBody"),$("#divContentLoading"));
		});
	});

	if($("#ddlRoomList").val()!=""){
		$("#ddlRoomList").attr("disabled",true);
	}


	$(".btnadminmenu").unbind("click");
	$(".btnadminmenu").on("click",function(){
		$("#divAdminBtn").toggle("slide");
	});
	$("#btnLogout").unbind("click");
	$("#btnLogout").on("click",function(){
		if(confirm("Confirm logout from the system and room.")){
			var aData = {u_mode:"logout",sesskey:$("#txtSS").val()};
			$("#pribta21").hide();
			callAjax("login_a.php",aData,function(rtnObj,aData){
				$("#pribta21").load("login_inc.php",function(){
					$("#pribta21").show();
				});
			});
		}
	});

	$("#btnEnterRoom").unbind("click");
	$("#btnEnterRoom").on("click",function(){
		let isDisable = $("#ddlRoomList").attr("disabled");
		let roomNo = $("#ddlRoomList").val();

		if(roomNo=="" || roomNo==undefined) {
			$("#ddlRoomList").notify("Please Select Room");
			return;
		}
		let sStatus = (isDisable=="disabled" || isDisable==true);

		let sWho = $("#ddlRoomList option[value='"+roomNo+"']").attr("data-sid");
		if(sWho != "" && !sStatus){
			if(!confirm("Do you want to take over the room?\r\nมีเจ้าหน้าที่ประจำห้องอยู่ ยืนยันเข้าห้อง?")){
				return;
			}
		}

		var aData = {roomno:roomNo};
		aData.u_mode = ((sStatus)?"exit_room":"enter_room");

		startLoad($("#divRoom"),$("#divRoomLoader"));
		callAjax("room_a.php",aData,function(rtnObj,aData){
			if(rtnObj.res=="0"){
				$.notify("There are problem with your room status. Please try Ctr+F5.\r\nระบบพบปัญหาในการจับจองห้อง ท่านสามารถกด Ctr+F5 แล้วลองอีกครั้ง");
				endLoad($("#divRoom"),$("#divRoomLoader"));
			}else{
				if(aData.u_mode=="exit_room"){
					$("#ddlRoomList").removeAttr("disabled");
					$.notify("Left room success. ออกจากห้องแล้ว","success");
				}else if(aData.u_mode=="enter_room"){
					$("#ddlRoomList").attr("disabled",true);
					optRoom = $("#ddlRoomList option[value='"+aData.roomno+"']");
					sTmp = $(optRoom).text();
					aText = sTmp.split("|");
					$(optRoom).text(aText[0]+"| "+"HELLO");
					$.notify("Enter room success. เข้าห้องแล้ว","success");
				}

				endLoad($("#divRoom"),$("#divRoomLoader"));
			}

		});

	});
});


</script>