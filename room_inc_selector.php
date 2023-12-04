<?
include("in_session.php");
include_once("in_php_function.php");
include_once("in_setting_row.php");
$sId = getSS("s_id");
$sRoom = getSS("room_no");
$sClinic = getSS("clinic_id");
$sRoomName = getSS("room_name");
$sToday = date("Y-m-d");


include("in_db_conn.php");
// Check if user is in the room
$isEnterRoom=false;
$sRoomName=getSS("room_detail");

if($sId==""){
	echo("Please login.");
	exit();
}

//Check if the user is in the room
$query="SELECT IRL.room_no,room_detail,staff_logdate FROM i_room_login IRL
LEFT JOIN i_room_list IRLIST
ON IRLIST.room_no = IRL.room_no
AND IRLIST.clinic_id = IRL.clinic_id
WHERE visit_date=? AND IRL.room_status=1 AND s_id=? AND IRL.clinic_id=?";

//echo $query;
// echo $sId."/".$sRoom."/".$sToday."/".$sClinic;

$sHtml="";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("sss",$sToday,$sId,$sClinic);
$isRowFound=false;


if($stmt->execute()){
  $stmt->bind_result($room_no,$room_detail,$staff_logdate);
  while ($stmt->fetch()) {
  	$_SESSION["room_no"] = $room_no;
  	$_SESSION["room_detail"] = $room_detail;
  	$sRoom=$room_no;
  	$sRoomName=$room_detail;
  }
}


$sHtml="";
if($sRoom!=""){
	//User is in the room. Offer room out
	$sHtml.="<div class='fl-wrap-col'>
		<div class='fl-fix h-150'>
			You are currently in room number <br/> ขณะนี้ท่านประจำอยู่ที่ห้อง <br/> <h2>$sRoom : $sRoomName</h2>
		</div>
		<div class='fl-fill'>
			In cases there are Q in line. What would you like to do?<br/>
			หากมีคนไข้รอต่อคิวอยู่ ต้องการ <br/>
			<SELECT id='ddlQManage'>
				<option value='0'>Do nothing. Someone will take over the room. - ไม่ต้องทำอะไร</option>
				<option value='1'>Move them to register. - ย้ายคนไข้ไปอยู่จุดลงทะเบียนเพื่อส่งต่อ</option>
			</SELECT><br/>
			*ยังไม่สามารถย้ายไปยังห้องอื่นได้ จะสามารถใช้งานได้ในอนาคต
		</div>
		<div class='fl-fix h-100 fl-mid'>
			<i id='btnExitRoom' class='fabtn fas fa-door-closed fa-2x'>Exit Room</i>
			<i id='btnExitRoom-loader' class='fas fa-spinner fa-spin fa-2x' style='display:none'></i>
		</div>
	</div>
	";
}else{
	//User not in the room. Off room enter

	$query="SELECT IRL.room_no,room_detail,s_name,IRLIST.s_id,room_icon FROM i_room_list IRL 
	LEFT JOIN i_room_login IRLIST 
	ON IRLIST.room_no=IRL.room_no 
	AND IRLIST.clinic_id=IRL.clinic_id 
	AND IRLIST.visit_date=? 
	 AND IRLIST.room_status='1'
	LEFT JOIN p_staff PS 
	ON PS.s_id=IRLIST.s_id 
	WHERE IRL.room_status='1' AND IRL.clinic_id=? 
	AND IRL.section_id IN (SELECT section_id FROM i_staff_clinic WHERE s_id=? AND clinic_id=? AND sc_status=1 ) ORDER BY IRL.room_no*1";
	
	//echo $query;

	$sHtml="";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ssss",$sToday,$sClinic,$sId,$sClinic);


	if($stmt->execute()){
	  $stmt->bind_result($room_no,$room_detail,$s_name,$s_id,$room_icon);
	  while ($stmt->fetch()) {
	  	$sHtml.=getRoomListRow($room_no,$room_detail,$s_id,$s_name,$room_icon);
	  }
	}
}



$mysqli->close();

?>
<style>
	.fabtn-btn{
		padding:2px 5px;
		border:1px solid silver;
		background-color: pink;
		cursor: pointer;
		color:white;
	}
	.fabtn-btn:hover{
		filter:brightness(80%);
		background-color: silver;
	}

</style>
<div class='dlg-room-list fl-wrap-col' style='<? echo(($sRoom=="")?"":"display:none"); ?>'>
	<div class='fl-fix h-ss'>
		Please select ROOM / กรุณาเลือกห้องที่ต้องการ
	</div>
	<div class='fl-wrap-row h-ss row-color-2'>
		<div class='fl-fix w-s'>
			#
		</div>
		<div class='fl-fill'>
			ห้อง/Room
		</div>
		<div class='fl-fix w-xxl'>
			เจ้าหน้าที่/Staff
		</div>
	</div>
	<div class='fl-wrap-col fl-auto'>
		<? echo($sHtml); ?>
	</div>
</div>
<div class='dlg-room-logout' style='<? echo(($sRoom=="")?"display:none":""); ?>'>
	<? echo($sHtml); ?>
</div>

<script>
	$(document).ready(function(){

		$(".dlg-room-logout #btnExitRoom").unbind("click");
		$(".dlg-room-logout #btnExitRoom").bind("click",function(){
			ddlMove = $(".dlg-room-logout #ddlQManage").val();
			objThis = $(this);

			if(confirm("Do you want to exit the room?\r\nยืนยันออกจากห้อง")){
				aData = {u_mode:"room_exit",pq:ddlMove};
				startLoad($(".dlg-room-logout #btnExitRoom"),$(".dlg-room-logout #btnExitRoom-loader"));
				callAjax("room_a.php",aData,function(rtnObj,aData){

					if(rtnObj.res!="1"){
						$.notify("Fail to exit the room. Please try again\r\n"+rtnObj.msg,"error");
						closeDlg(objThis,"0");
					}else if(rtnObj.res=="1"){
						$.notify("Exit Room Success","success");
						closeDlg(objThis,"1");
					}
					//

					endLoad($(".dlg-room-logout #btnExitRoom"),$(".dlg-room-logout #btnExitRoom-loader"));
				});
			}
		});

		$(".dlg-room-list .btnenterroom").unbind("click");
		$(".dlg-room-list .btnenterroom").bind("click",function(){
			objBtn = $(this); objBtnLoader = $(this).next(".btnenterroom-loader");

			objRow = $(this).closest(".data-row");
			chkCF = $(objRow).find(".chkconfirm");
			isCF = "0"
			sRoomNo = $(objRow).attr('data-roomno');
			if($(chkCF).length){
				if($(chkCF).is(":checked")){
					isCF="1";
				}else{
					alert("มี จนท ประจำห้องอยู่ กรุณากดยืนยันเพื่อนำ จนท เดิมออก และ เข้าประจำห้องแทน\r\nThe is occupied by other staff please check confirm box to replace him/her.");
					return;
				}
			}else{

			}

			if(confirm("ยืนยันเข้าประจำห้อง \r\nConfirm enter the room")){

				aData = {u_mode:"room_enter",roomno:sRoomNo,iscf:isCF};
				objThis = $(this);
				startLoad($(".dlg-room-list .btnenterroom"),$(".dlg-room-list .btnenterroom-loader"));
				callAjax("room_a.php",aData,function(rtnObj,aData){

					if(rtnObj.res!="1"){
						$.notify("Fail to enter to the room. Please try again\r\n"+rtnObj.msg,"error");
						closeDlg(objThis,"0");
					}else if(rtnObj.res=="1"){
						$.notify("Enter Room Success","success");
						closeDlg(objThis,"1");
					}
					//

					endLoad($(".dlg-room-list .btnenterroom"),$(".dlg-room-list .btnenterroom-loader"));
				});
			}
		});
		
	});
</script>