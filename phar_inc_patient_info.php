<?
include_once("in_session.php");
include_once("in_php_function.php");
include_once("in_front_php_function.php");
$sUid=getQS("uid");
$sColD=getQS("collect_date");
$sColT=getQS("collect_time");
if($sColD=="") $sColD=getQS("coldate");
if($sColT=="") $sColT=getQS("coltime");
$sShowInfo=getQS("showinfo");
$sClinicId=getSS("clinic_id");
$sToday = date("Y-m-d");
$sTime = date("H:i:s");
$sQ=getQS("q");
$sSid=getSS("s_id");

$sData=getDataAttr($sUid,$sColD,$sColT,$sQ);

include("in_db_conn.php");
$sQuickInfo="";

if($sShowInfo){

	$aPInfo=array("food"=>"","food_txt"=>"","drug"=>"","drug_txt"=>"","dx"=>"","advise"=>"","cn_weight"=>"","note"=>"","treatment"=>"","plan"=>"");

	$query ="SELECT uid,collect_date,collect_time,data_id,data_result 
	FROM p_data_result
	WHERE uid=? AND (data_id IN ('food_intolerance','food_intolerance_txt','drug_allergy','drug_allergy_txt','cn_dx','cn_advise_urgen','cn_weight','cn_patient_note','cn_treatment','cn_plan')) AND collect_date=? AND collect_time=?
	ORDER BY data_id,collect_date,collect_time";

	//อันนี้ดึงข้อมูลช้ามาก ไม่ควรทำ ดึงมาทั้งหมด แล้วมา list ใน PHP ดีกว่า สำหรับข้อมูล 1 คน ถ้าทำเยอะๆ พิจารณาแยกกัน
	//$query ="SELECT uid,collect_date,collect_time,data_id,data_result FROM p_data_result WHERE uid=? AND (data_id IN ('food_intolerance','food_intolerance_txt','drug_allergy','drug_allergy_txt')) OR (collect_date=? AND collect_time=? AND data_id IN ('cn_dx','cn_advise_urgen','cn_weight')) ORDER BY collect_date,collect_time";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColD,$sColT);
	if($stmt->execute()){
		$stmt->bind_result($uid,$collect_date,$collect_time,$data_id,$data_result);
		while($stmt->fetch()){
			if($data_id=="food_intolerance"){
				$aPInfo["food"]=$data_result;
			}else if($data_id=="food_intolerance_txt"){
				$aPInfo["food_txt"]=$data_result;
			}else if($data_id=="drug_allergy"){
				$aPInfo["drug"]=$data_result;
			}else if($data_id=="drug_allergy_txt"){
				$aPInfo["drug_txt"]=$data_result;
			}
			if($collect_date==$sColD && $sColT==$collect_time){
				if($data_id=="cn_advise_urgen"){
					$aPInfo["advise"]=$data_result;
				}else if($data_id=="cn_dx"){
					$aPInfo["dx"]=$data_result;
				}else if($data_id=="cn_patient_note"){
					$aPInfo["note"]=$data_result;
				}else if($data_id=="cn_treatment"){
					$aPInfo["treatment"]=$data_result;
				}else if($data_id=="cn_plan"){
					$aPInfo["plan"]=$data_result;
				}else{
					$aPInfo[$data_id]=$data_result;
				}
			}
	

		}

	}




	if($aPInfo["food_txt"]=="" && $aPInfo["food"]=="N"){
		$aPInfo["food_txt"] = "ปฏิเสธแพ้อาหาร";
	}
	if($aPInfo["drug_txt"]=="" && $aPInfo["drug"]=="N"){
		$aPInfo["drug_txt"] = "ปฏิเสธแพ้ยา";
	}

	$sFood = "แพ้อาหาร : ".($aPInfo["food_txt"]);
	$sDrug = "แพ้ยา : ".($aPInfo["drug_txt"]);

	$sHistory = "";
	$query = "SELECT IQLL.room_no,room_detail,IQLL.s_id,s_name,queue_datetime,queue_status FROM i_queue_list_log IQLL 
	LEFT JOIN i_room_list IRL
	ON IRL.clinic_id = IQLL.clinic_id
	AND IQLL.room_no=IRL.room_no
	LEFT JOIN p_staff PS
	ON PS.s_id = IQLL.s_id
	WHERE  uid=? AND collect_date=? AND collect_time=? AND queue_type = '1' AND queue_call!=1 ORDER BY queue_datetime DESC
	";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss",$sUid,$sColD,$sColT);

	if($stmt->execute()){
	  $stmt->bind_result($room_no,$room_detail,$s_id,$s_name,$queue_datetime,$queue_status);
	  while ($stmt->fetch()) {

	  	$sTime = substr($queue_datetime,10);
	  	$sHistory .= "<div class='fl-wrap-row h-25 row-hover row-color-2'>
	  		<div class='fl-fix w-50'>$sTime</div>
	  		<div class='fl-wrap-col'>
	  			<div class='fl-fix ' style='line-height:12px'>[$room_no] $room_detail</div>
	  			<div class='fl-fill fw-b ' style='line-height:13px'>$s_name</div>
	  		</div>
	  	</div>";
	  }
	}


	$btnNoteToAll="";
//<textarea readonly='true' class='w-fill h-50'>".$aPInfo["advise"]."</textarea>
	if($sColD!='') $btnNoteToAll = "<i  id='btnEditNoteToAll' class=' fabtn fas fa-edit fa-lg' title='แก้ไขข้อมูลคนไข้ใน Visit นี้'></i>";
	//if($sToday!=$sColD || $sSid=="") $btnNoteToAll = "";

	$sQuickInfo = "
		<div class='fl-wrap-row h-50 row-color '>
			<div class='fl-fill lh-15 h-50 fs-smaller al-left fl-auto f-border fabtn popupbox'>
				<span class='fw-b'>Dx : </span>".$aPInfo["dx"]."
			</div>
			<div class='fl-fill lh-15 h-50 fs-smaller al-left fl-auto f-border fabtn popupbox'>
				<span class='fw-b'>Advice : </span>".$aPInfo["advise"]."
			</div>
			<div class='fl-fill lh-15 h-50 fs-smaller al-left fl-auto f-border fabtn popupbox'>
				<span class='fw-b'>Treatment : </span>".$aPInfo["treatment"]."
			</div>
			<div class='fl-fill lh-15 h-50 fs-smaller al-left fl-auto f-border fabtn popupbox'>
				<span class='fw-b'>Plan : </span>".$aPInfo["plan"]."
			</div>
			<div class='fl-fill wper-20 lh-15 h-50 fs-small al-left fl-mid'>
				 Note $btnNoteToAll : <textarea id='txtNoteToAll' readonly='true' class='w-fill h-50 fs-smaller'>".$aPInfo["note"]."</textarea>
			</div>
			<div class='fl-wrap-col wper-20 h-50 popupbox'>
				<div class='fl-wrap-col fs-xsmall fl-auto'>
				 ".$sHistory."
				 </div>
			</div>

		</div>
		<div class='fl-wrap-row h-25 row-color fl-mid  fs-small'>
			<div class='fl-fill lh-15  al-left fw-b'>
				".(($sToday==$sColD)?"<i class='btneditallergy fabtn fas fa-edit fa-lg' title='แก้ไขข้อมูลการแพ้ อาหาร และ ยา'></i>":"")." <span class='fabtn popupbox'>$sDrug</span>
			</div>
			<div class='fabtn popupbox fl-fill lh-15  al-left fw-b'>
				$sFood
			</div>
			<div class='fl-fix w-40'>WT: </div>
			<div class='fl-fix w-50 fw-b'>".$aPInfo["cn_weight"]."</div>
			<div class='fl-fix w-40'> kgs</div>
		</div>

	";
}




$mysqli->close();

include("lab_inc_patient_info.php");
?>

<div id='divLIPI2' class='fl-wrap-col h-75' data-showinfo='<? echo($sShowInfo."' ".$sData); ?>' >
	<? echo($sQuickInfo); ?>
</div>

<script>
	$(function(){
		$("#divLIPI2 #btnEditNoteToAll").off("click");
		$("#divLIPI2").on("click","#btnEditNoteToAll",function(){
			objRow = $(this).closest("#divLIPI2");
			var sUid = $(objRow).attr("data-uid");
			let sColD=$(objRow).attr('data-coldate');
			let sColT=$(objRow).attr('data-coltime');
			showDialog("patient_inc_edit_note.php?"+qsTxt(sUid,sColD,sColT),"Edit Patient's Note to All : "+sUid,"240","480","",
			function(sResult){
				if(sResult=="REFRESH"){
					sURL="data_result_a.php";
					aReData={"u_mode":"get_data_by_id","uid":sUid,"coldate":sColD,"coltime":sColT,"data_id":"cn_patient_note"};
					callAjax(sURL,aReData,function(jRes,rData){
						if(jRes.res=="1"){
							  $("#divLIPI2 #txtNoteToAll").val(jRes.data_result);
						}else{
							
						}
			        });
					var sShowInfo = $("#divLIPI2").attr("data-showinfo");
					$("#divLIPI2").parent().load("phar_inc_patient_info.php?"+qsTxt(sUid,sColD,sColT)+"&showinfo="+sShowInfo);
				}
			},false,function(){
				//Load Done Function
			});

		});


		$("#divLIPI2 .btneditallergy").off("click");
		$("#divLIPI2").on("click",".btneditallergy",function(){
			objRow = $(this).closest("#divLIPI2");
			var sUid = $(objRow).attr("data-uid");
			let sColD=$(objRow).attr('data-coldate');
			let sColT=$(objRow).attr('data-coltime');
			showDialog("patient_inc_edit_allergy.php?"+qsTxt(sUid,sColD,sColT),"Edit Patient's food and drug allergy : "+sUid,"240","480","",
			function(sResult){
				if(sResult=="REFRESH"){
					var sShowInfo = $("#divLIPI2").attr("data-showinfo");
					$("#divLIPI2").parent().load("phar_inc_patient_info.php?"+qsTxt(sUid,sColD,sColT)+"&showinfo="+sShowInfo);
				}
			},false,function(){
				//Load Done Function
			});

		});

	});
</script>