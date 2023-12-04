<?
include_once("in_php_function.php");
$sClinic = getQS("clinic","IHRI");




?>

<style>
	.l-btn{
		height:70px;
		
	}
	.d-menu{

	}
	.d-btn{
		font-weight: bold;
		font-size:20px;
		border:2px solid silver;
		background-color: white;
	}
	.visit-row{
		line-height: 30px;
		font-size:14px;
		max-height: 30px;
		min-height: 30px;
		width:100%;
	}
	.visit-row:hover{
		filter:brightness(80%);
		cursor: pointer;
	}
	.visit-site{
		max-width: 100px;
		min-width: 100px;
	}
	.visit-uid{
		max-width: 80px;
		min-width: 80px;
	}
	.visit-date{
		max-width: 150px;
		min-width: 150px;
	}

</style>
<input type='hidden' id='txtClinic' value='<? echo($sClinic); ?>' />


<div class='fl-fix'>
	<div class='fl-wrap-row fl-mid' style='max-height:40px;background-color: #CABDCD;'>
		<div id='btnQueue' class='fl-fill btn d-btn'  style='line-height:40px;;' >
			By QUEUE
		</div>
		<div id='btnUID' class='fl-fill btn d-btn'  style='line-height:40px;;' >
			By UID
		</div>
	</div>
</div>
<div class='fl-wrap-row ' style='background-color: #CABDCD;'>
	<div id='divTxtQ' class='fl-wrap-col fl-mid' style=';background-color: silver;' >
		<div class='fl-fill fs-xxxl'>
		<input id='txtQueue' class='hide-on-load' style='width:200px; text-align: center;' maxlength="3" placeholder="Q" /><i id='imgQLoader' class='fa fa-spinner fa-spin fa-2x' style='display:none'></i>
		</div>
		<div class='fl-fill fs-xxxl'>
		<input id='txtUid' style='width:650px; text-align: center;' maxlength="10" placeholder="UID" data-load='0' />
		</div>
		<div class='fl-fill'>
		<button id='btnSubmit' class='l-btn hide-on-load roundcorner fs-xl'>START</button><img id='imgSubLoader' src='assets/image/spinner.gif' style='display:none;height:30px' />
		</div>
	</div>
	<div id='divTxtU' class='fl-wrap-col' style='display:none;background-color: silver;' >
		<div class='fl-fix fl-mid' style='max-height: 120px;min-height: 100px'><input id='txtByUid' style='width:450px;font-size:60px;text-align: center' maxlength="10" placeholder="UID" data-load='0' /><i id='btnSearchUID' class='fabtn fa fa-search-plus fa-3x' style='margin-left:10px'></i><i id='btnSearchUID-loading' class='fa fa-spinner fa-spin fa-3x' style='display:none'></i>
		</div>
		<div  id='divUIDResult' class='fl-wrap-col fl-auto' style='width: 100%'>

		</div>
		
	</div>
</div>

<script>
	$(function(){
		$("#btnQueue").unbind("click");
		$("#btnQueue").on("click",function(){
			$("#divTxtQ").show();
			$("#divTxtU").hide();
		});

		$("#btnUID").unbind("click");
		$("#btnUID").on("click",function(){
			$("#divTxtU").show();
			$("#divTxtQ").hide();
		});

		$("#txtQueue").unbind("change");
		$("#txtQueue").on("change",function(){

			sClinic = $("#txtClinic").val();
			sQ = $("#txtQueue").val();

			if(sQ=="") {
				$("#txtUid").val("");
				return;
			}
			$("#txtQueue").hide();
			$("#imgQLoader").show();
			$("#btnSubmit").hide();
			var aData = {u_mode:"get_uid_by_q",q:sQ,clinic:sClinic};
			
			callAjax("patient_a.php",aData,function(rtnObj,aData){
				if(rtnObj.res=="0"){
					alert("UID Not found on this Q. Please enter UID to bind with the Q.\r\nไม่พบ UID ของ Q นี้ กรุณาใส่ UID เพื่อผูกกับ Q");
					$("#txtUid").val("");
					$("#txtUid").attr('data-load','0');
				}else{
					$("#txtUid").val(rtnObj.uid);
					$("#txtUid").attr('data-load','1');
				}
				$("#txtQueue").show();
				$("#imgQLoader").hide();
				$("#btnSubmit").show();
			});
		});

		$("#btnSearchUID").unbind("click");
		$("#btnSearchUID").on("click",function(){
			sUid=$("#txtByUid").val();

			if(sUid==""){
				$("#txtByUid").notify("Please enter UID\r\nกรุณาใส่ UID");
				return;
			}
			var aData = {u_mode:"find_visit_by_uid",u:sUid};

			startLoad($("#btnSearchUID"),$("#btnSearchUID-loading"));
			callAjax("patient_a.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					$("#divUIDResult").html("Error:"+rtnObj.msg);
				}else if(rtnObj.res=="1"){
					$("#divUIDResult").html(rtnObj.msg);
				}
				endLoad($("#btnSearchUID"),$("#btnSearchUID-loading"));
				resetRowColor($("#divTxtU .visit-row"));
			});
			
		});

		$("#txtUid").unbind("change");
		$("#txtUid").on("change",function(){
			$(this).attr("data-load","0");
		});

		$("#divUIDResult .visit-row").unbind("click");
		$("#divUIDResult").on("click",".visit-row",function(){
			sUid=$(this).attr('data-uid');
			sColDate=$(this).attr('data-coldate');
			sColTime=$(this).attr("data-coltime");
			$("#divUIDResult").hide();
			loadQN(sUid,sColDate,sColTime);
		});
		/*
		$("#txtQueue").unbind("keypress");
		$("#txtQueue").on("keypress",function(e){
			if(e.which == 13){
			    $("#txtQueue").trigger("change");
			}
		});	
		*/
		$("#btnSubmitU").unbind("click");
		$("#btnSubmitU").on("click",function(){
			sUid=$("#txtByUid").val();
			if(sUid==""){
				$("#txtByUid").notify("Please enter UID\r\nกรุณาใส่ UID");
				return;
			}
		});

		$("#btnSubmit").unbind("click");
		$("#btnSubmit").on("click",function(){
			sUid = $("#txtUid").val();
			isLoad = $("#txtUid").attr("data-load");
			sQ = $("#txtQueue").val();

			if(sUid==""){
				$("#txtUid").notify("Please enter UID\r\nกรุณาใส่ UID");
				return;
			}else if(sQ==""){
				$("#txtQueue").notify("Please enter Q\r\nกรุณาใส่ Q");
				return;
			}

			if(isLoad=="0" && sQ!="" && sUid != ""){
				if(confirm("Q is not in Visit Data. Would you like to create today visit for this UID?\r\nQ นี้ยังไม่มี Visit Data. ยืนยันสร้าง Visit ขึ้นมาสำหรับวันนี้??")){

					var aData = {u_mode:"create_visit_data",q:sQ,clinic:sClinic,u:sUid};
						$("#btnSubmit").hide();
						$("#imgSubLoader").show();	
					callAjax("patient_a.php",aData,function(rtnObj,aData){
						if(rtnObj.res=="0"){
							alert("Unable to add today visit. Please enter Q and try again.\r\nระบบเพิ่ม Visit วันนี้ไม่ได้ กรุณาลองตรวจสอบ Q หรือ Uid อีกครั้ง");
							$("#btnSubmit").show();
							$("#imgSubLoader").hide();						
						}else{
							loadQN(sUid,rtnObj.curdate,rtnObj.curtime);
						}

					});

				};
			}else{
				$("#btnSubmit").hide();
				$("#imgSubLoader").show();	
				loadQN(sUid);
			}
		});

		function loadQN(sUid,sCurDate,sCurTime){
			sClinic = $("#txtClinic").val();
			sUrl = "qa_inc_pribta_main.php?q="+$("#txtQueue").val()+"&uid="+sUid+"&curdate="+sCurDate+"&curtime="+sCurTime+"&clinic="+sClinic;
			$("body").find("#qa2021").load(sUrl);

	}
	});
</script>