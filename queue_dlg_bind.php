<?
include("in_session.php");
include_once("in_php_function.php");
$sColDate = getQS("coldate");


$sUid = getQS("uid");

?>

<div class='fl-wrap-row'>
	<div id='qlist' class='fl-fix fl-wrap-col' style="min-width:300px">
		<? include("queue_inc_list.php"); ?>
	</div>
	<div  id='divTxtQ' class='fl-wrap-col fl-mid' style='background-color: #CABDCD;align-items: center;text-align: center;'>
		<div class='fl-fill'>
			<input id='txtQueue' type='number' class='hide-on-load' style='width:50%; font-size:100px;text-align: center;' maxlength="3" placeholder="Q" /><img id='imgQLoader' src='assets/image/spinner.gif' style='display:none;height:30px' />
		</div>
		<div class='fl-fill'>
			<input id='txtUid' style='width:100%; font-size:80px;text-align: center;' placeholder="UID" readonly="true" value='<? echo($sUid); ?>' />
		</div>
		<div class='fl-fill'>
			<button id='btnBindQ' class='l-btn hide-on-load roundcorner'>BIND</button><img id='imgSubLoader' src='assets/image/spinner.gif' style='display:none;height:30px' />
		</div>

	</div>
</div>

<script>
	$(function(){
		$("#btnBindQ").unbind("click");
		$("#btnBindQ").on("click",function(){

			sQ = $("#txtQueue").val();

			if(sQ=="") {
				$("#txtQueue").notify("Please enter available Q Number\r\nกรุณาใส่หมายเลขคิว");
				return;
			}

			sUid=$("#txtUid").val();

			$("#imgSubLoader").show();
			$("#btnBindQ").hide();
			var aData = {q:sQ,uid:sUid};
			callAjax("idiot_q_bind.php",aData,function(rtnObj,aData){
				if(rtnObj.res=="0"){
					alert(rtnObj.msg);
					$("#btnBindQ").show();
				}else{
					$("#qlist").load("queue_inc_list.php");
				}
				$("#imgSubLoader").hide();
				//$("#btnBindQ").show();
			});
		});
	});

</script>