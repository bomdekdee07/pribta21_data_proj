<?
/* Project Register Main */

include("in_session.php");
include_once("in_php_function.php");
include("in_db_conn.php");

$sUID = getQS("uid");
$sUIC = "";

$query = "SELECT uic FROM patient_info WHERE uid=?
";

//echo "$sUID / query : $query";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$sUID);

if($stmt->execute()){
	$stmt->bind_result($sUIC);
	if ($stmt->fetch()) {

	}
}




?>



	<div class='div-uic-edit fl-wrap-row bg-msoft1 ptxt-s12 ph50'
	 data-uid='<? echo $sUID; ?>' data-olduic='<? echo $sUIC; ?>'>
		 <div class='fl-fix fl-mid pw300 '>
			 UIC: <INPUT type='text' id='txtuic' placeholder='Insert UIC' value='<? echo $sUIC; ?>' />
		 </div>

  </div>
	<div class='fl-wrap-row bg-mdark2 pbtn pbtn-ok ph30'>
		 <div class='fl-fill fl-mid btn-uic-update'>
        UPDATE UIC
		 </div>
	</div>





<script>

$(document).ready(function(){

  setDlgResult($('#txtuic').val());
	$(".btn-uic-update").unbind("click");
	$(".btn-uic-update").bind("click",function(){
		if($('#txtuic').val()==''){
			$('#txtuic').notify('Please insert UIC here.', 'info');
			return;
		}
		else if($('#txtuic').val() == $('.div-uic-edit').attr('data-olduic')){
			$('#txtuic').notify('No data change.', 'info');
			return;
		}


		var aData = {
			  u_mode: "update_uic",
				uid:$('.div-uic-edit').attr('data-uid'),
				uic:$('#txtuic').val(),
				olduic:$('.div-uic-edit').attr('data-olduic')
		};

		if(confirm("ยืนยันต้องการเปลี่ยน UIC ของ "+aData.uid+ " เป็น "+aData.uic)){

					callAjax("uid_data_a.php",aData,function(rtnObj,aData){
				//		endLoad(btnclick, btnclick.next(".fa-spinner"));
						if(rtnObj.res == 1){
							alert("เปลี่ยน UIC ของ "+aData.uid+" ให้เป็น: "+aData.uic +" แล้ว");

			        setDlgResult(aData.uic);
							closeDlg();

						}
						else if(rtnObj.res == 0){
							$.notify("Fail to update uic.", "error");

						}

						if(rtnObj.msg_error != "")
						alert(rtnObj.msg_error);

					});// call ajax
		}//confirm

	});





});





</script>
