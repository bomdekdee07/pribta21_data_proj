<?
//JENG
include_once("in_session.php");
include_once("in_php_function.php");

$sUid=getQS("uid");
$sColDate=getQS("coldate");
$sColTime=getQS("coltime");
$sSID = getSS("s_id");
$sClinicID = getSS("clinic_id");

include("in_db_conn.php");
$sToday=date("Y-m-d");

$sHtmlKeyId = getHiddenPk($sUid,$sColDate,$sColTime);

//Patient Info
//get patient info
$query ="SELECT uid,uic,fname,sname,en_fname,en_sname,sex,gender,date_of_birth,nation,citizen_id,remark FROM patient_info WHERE uid=? LIMIT 1";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);

$sQuickInfo=""; $optProj="";
$imgPath="assets/image/nophoto.jpg";
$aPInfo=array();
if($stmt->execute()){
  $stmt->bind_result($uid,$uic,$fname,$sname,$en_fname,$en_sname,$sex,$gender,$date_of_birth,$nation,$citizen_id,$remark);
  while ($stmt->fetch()) {
	$imgPath = "idimg/".$citizen_id.".png";
	if(!file_exists($imgPath)){
		$imgPath = "idimg/".$uid.".png";
		if(!file_exists($imgPath))	$imgPath="assets/image/nophoto.jpg";
	}
	$aPInfo["uid"] = $uid;
	$aPInfo["uic"] = $uic;
	$aPInfo["date_of_birth"] = $date_of_birth;
	$aPInfo["fname"] = $fname;
	$aPInfo["sname"] = $sname;
	$aPInfo["en_fname"] = $en_fname;
	$aPInfo["en_sname"] = $en_sname;
	$aPInfo["sex"] = $sex;
	$aPInfo["gender"] = $gender;
	$aPInfo["remark"] = $remark;
	$aPInfo["nation"] = $nation;
	$aPInfo["citizen_id"] = $citizen_id;
	$aPInfo["food"]="";
	$aPInfo["food_txt"]="";
	$aPInfo["drug"]="";
	$aPInfo["drug_txt"]="";
  }
}

if(count($aPInfo)==0){
	//No Data Found. End
}else{
	$query ="SELECT uid,collect_date,collect_time,data_id,data_result FROM p_data_result WHERE uid=? AND data_id IN ('food_intolerance','food_intolerance_txt','drug_allergy','drug_allergy_txt') AND data_result !='' ORDER BY collect_date,collect_time";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUid);
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
		}

	}

	$sFood = ""; $sDrug = "";
	if($aPInfo["food_txt"]!=""){
		$sFood = "แพ้อาหาร : ".$aPInfo["food_txt"];
	}if($aPInfo["drug_txt"]!=""){
		$sDrug = "แพ้ยา : ".$aPInfo["drug_txt"];
	}

	$sName = $aPInfo["fname"];
	if($sName==""){
		$sName = $aPInfo["en_fname"]." ".$aPInfo["en_sname"];
	}else{
		$sName = $aPInfo["fname"]." ".$aPInfo["sname"];
	}
	$sSex = getBirthSex($aPInfo["sex"]);
	
	$sQuickInfo = "
		<div class='fl-wrap-row h-80 row-color'>
			<div class='fl-wrap-col w-80 row-color'>
				<div class='fl-fill fl-mid'>
					<img src='".$imgPath."' class='h-65'>
				</div>
				<div class='fl-fix h-15 lh-15 fw-b fs-small' style='text-align:center;vertical-align:top;' >
					$sUid
				</div>
			</div>
			<div class='fl-wrap-col row-color'>
				<div class='fl-fix h-15 lh-15 fw-b fs-small al-left' >
					$sName
				</div>
				<div class='fl-fix h-15 lh-15 fs-small al-left' >
					".$sSex."
				</div>
				<div class='fl-fix h-25 lh-12 fs-small al-left'>
					$sFood
				</div>
				<div class='fl-fix h-25 lh-12 fs-small al-left'>
					$sDrug
				</div>
			</div>
		</div>

	";



	//Project List
	//get Project List if exist
	
	$query="SELECT PPUL.proj_id,PP.proj_name,uid,pid,uid_status,PPUL.clinic_id,clinic_name FROM p_project_uid_list PPUL
	LEFT JOIN p_project PP
	ON PP.proj_id=PPUL.proj_id
	LEFT JOIN p_clinic PC
	ON PC.clinic_id=PPUL.clinic_id
	WHERE uid=?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sUid);


	if($stmt->execute()){
	  $stmt->bind_result($proj_id,$proj_name,$uid,$pid,$uid_status,$clinic_id,$clinic_name);
	  while ($stmt->fetch()) {
	  	$optProj.="<option value='$proj_id' data-pid='$pid' data-status='$uid_status' title='$clinic_name'>".$proj_name.":".$clinic_name.":".$pid."</option>";

	  }
	}



}
$stmt->close();

$bind_param = "ss";
$array_val = array($sSID, $sClinicID);
$data_section_check = array();

$query = "SELECT
	section_id
FROM i_staff_clinic 
WHERE s_id = ?
AND sc_status = '1' 
AND clinic_id = ?
AND section_id = 'D03_PHAR_SEARCH'
ORDER BY section_id;";

$stmt = $mysqli->prepare($query);
$stmt->bind_param($bind_param, ...$array_val);

if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()){
	$data_section_check[$row["section_id"]] = $row["section_id"];
	}
}
// print_r($data_section_check);
$stmt->close();

//Query section team search order pharmar
$data_section_all = array();
$query_search = "SELECT section_id from p_staff_section where section_enable = '1';";
$stmt = $mysqli->prepare($query_search);

if($stmt->execute()){
  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()){
    $data_section_all[$row["section_id"]] = $row["section_id"];
  }
}
// print_r($data_section_all);
$stmt->close();

// General note
$d_data_result = array(); // data result of uid, collect_date, collect_time
$query = "SELECT d.data_id, d.data_result, t.data_type, '1' as data_check
FROM p_data_result d
	left join p_form_list_data t on (d.data_id = t.data_id)
WHERE d.uid=? 
AND d.collect_date=? 
AND d.collect_time=? 
AND d.data_id = 'cn_patient_note'
and t.form_id = 'PHYSICAIN_CHART'";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('sss',$sUid, $sColDate, $sColTime); // echo "query : $query";

if($stmt->execute()){
	$stmt->bind_result($data_id, $data_result, $data_type, $data_check);
	while ($stmt->fetch()) {
		if(!isset($d_data_result[$data_id]))
			$d_data_result[$data_id]["data_id"] = $data_id;
			$d_data_result[$data_id]["data_result"] = $data_result;
			$d_data_result[$data_id]["data_type"] = $data_type;
			$d_data_result[$data_id]["data_check"] = $data_check;
			// print($d_data_result[$data_id]["data_id"].": ".$d_data_result[$data_id]["data_result"]."<br>");
	}
}
else{
	$msg_error .= $stmt->error;
}
$stmt->close();
$mysqli->close();

$check_search_pharmar = "0";
foreach($data_section_check as $keyid => $val){
	$check_search_pharmar = (isset($data_section_all[$keyid])?"1":"0");
	if($check_search_pharmar == "1")
		break;
}
// echo $check_search_pharmar;

// onload note
$sJS = "";
foreach($d_data_result as $d_id => $data_val){
	$data_id = $data_val["data_id"];
	$data_result = $data_val["data_result"];
	$data_type = $data_val["data_type"];
	$data_check = $data_val["data_check"];

	$d_result = "";
	if(isset($d_data_result[$d_id])){
		$d_result = $data_result;
	}

	if($data_type == "textarea"){
		// $d_result = convert_special_char($d_result);
		$sJS .= '$("textarea[name='.$data_id.']").val('.(json_encode($d_result)).');';
		if($data_check == "1")
		$sJS .= '$("textarea[name='.$data_id.']").attr("data-odata",'.(json_encode($d_result)).');';
	}
}

?>
<div id='divSOD' class='fl-wrap-row' >
	<? echo($sHtmlKeyId); ?>
	<div class='fl-wrap-col'>
		<div id='divSOD_find' class='fl-wrap-col' style='border-bottom:1px solid black'>
			<div class='fl-fix h-30 row-color-2 fl-mid'>Add New Supply</div>
			<? 
				//if($sToday==$sColDate){
					$_GET["isinc"]=1;$_GET["incserv"]=1;$_GET["showamt"]=1; 
					include("supply_inc_list.php");				
				//}
			?>
		</div>
		<div class='fl-wrap-col'>
			<div class='fl-wrap-row h-30 row-color-2 fl-mid'>
				<div class='fl-fill'>List</div>
				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='ค่าบริการทั่วไป 100 บาท/ Service Charge 100 Baht' data-supcode='SV0001' data-nodup='0' data-amt='100' data-comment=''>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-hand-holding-medical"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">100</span>
					</span>
				</div>
				<div class='fl-fix w-20'></div>
				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='ค่าส่งยา 100 บาท/ Drug Delivery Fee 100 Baht' data-supcode='SV0001' data-nodup='1' data-amt='100' data-comment='ค่าส่งยา'>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-truck"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">100</span>
					</span>
				</div>
				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='ค่าส่งยา 150 บาท/ Drug Delivery Fee 150 Baht' data-supcode='SV0001' data-nodup='1' data-amt='150' data-comment='ค่าส่งยา'>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-truck"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">150</span>
					</span>
				</div>
				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='ค่าส่งยา 200 บาท/ Drug Delivery Fee 200 Baht' data-supcode='SV0001' data-nodup='1' data-amt='200' data-comment='ค่าส่งยา'>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-truck"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">200</span>
					</span>
				</div>
				<div class='fl-fix w-20'></div>

				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='ค่าบริการฉีดยา 100 บาท/ Add Injection Service Fee 100 Baht' data-supcode='SI0001' data-nodup='1' data-amt='100' data-comment=''>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-syringe"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">100</span>
					</span>
				</div>
				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='ค่าบริการฉีดยา 200 บาท/ Add Injection Service Fee 200 Baht' data-supcode='SI0001' data-nodup='1' data-amt='200' data-comment=''>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-syringe"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">200</span>
					</span>
				</div>
				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='ค่าบริการฉีดยา 300 บาท/ Add Injection Service Fee 300 Baht' data-supcode='SI0001' data-nodup='1' data-amt='300' data-comment=''>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-syringe"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">300</span>
					</span>
				</div>
				<div class='fl-fix w-20'></div>


				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='เพิ่มค่าให้คำปรึกษา 100 บาท/ Add Counselor Service Fee 100 Baht' data-supcode='SV0005' data-nodup='1' data-amt='100' data-comment=''>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-id-badge"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">100</span>
					</span>
				</div>
				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='เพิ่มค่าให้คำปรึกษา 200 บาท/ Add Counselor Service Fee 200 Baht' data-supcode='SV0005' data-nodup='1' data-amt='200' data-comment=''>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-id-badge"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">200</span>
					</span>
				</div>	
				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='เพิ่มค่าให้คำปรึกษา 300 บาท/ Add Counselor Service Fee 300 Baht' data-supcode='SV0005' data-nodup='1' data-amt='300' data-comment=''>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-id-badge"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">300</span>
					</span>
				</div>

				<div class='fl-fix w-20'></div>

				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='เพิ่มค่า DF 100 บาท/ Add Dortor Fee 100 Baht' data-supcode='SV0002' data-nodup='1' data-amt='100' data-comment=''>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-stethoscope"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">100</span>
					</span>
				</div>
				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='เพิ่มค่า DF 300 บาท/ Add Dortor Fee 300 Baht' data-supcode='SV0002' data-nodup='1' data-amt='300' data-comment=''>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-stethoscope"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">300</span>
					</span>
				</div>
				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='เพิ่มค่า DF 400 บาท/ Add Dortor Fee 400 Baht' data-supcode='SV0002' data-nodup='1' data-amt='400' data-comment=''>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-stethoscope"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">400</span>
					</span>
				</div>
				<div class='fl-fix fabtn quick-add h-30 w-25 fl-mid fs-xsmall' title='เพิ่มค่า DF 500 บาท/ Add Dortor Fee 500 Baht' data-supcode='SV0002' data-nodup='1' data-amt='500' data-comment=''>
					<span class="fa-layers fa-fw w-25">
					<i class="fas fa-stethoscope"></i>
					<span class="fa-layers-text fa-inverse" data-fa-transform="" style="color:black">500</span>
					</span>
				</div>



				<div class='fl-fix w-20'></div>
			</div>
			<div id='divSOD_list' class='fl-fill fl-auto'>
				<? include("supply_order_list.php"); ?>
			</div>
			<div id='divSOD_list-loader' class='fl-fill' style='display:none'><i class='fa fa-spinner fa-spin fa-4x'></i></div>
		</div>
	</div>
	<div class='fl-wrap-col' style='max-width:30%'>
		<div class='fl-wrap-col h-80'>
			<? echo($sQuickInfo); ?>
		</div>
		<div class='fl-wrap-row h-30'>
			<div data-url='patient_inc_dx_history' class='fabtn quick-view fl-fill row-color-2 fl-mid'>History</div>
			<div class='fabtn fl-fill row-color-2 fl-mid'>Other</div>
		</div>
		<div id='divQuickView' class='fl-wrap-col fl-auto'>
			
		</div>
		<div id='divQuickView-loader' class='fl-wrap-col fl-mid' style='display:none'>
			<i class='fa fa-spinner fa-spin'></i>
		</div>

		<div class="fl-wrap-row" <? echo ($check_search_pharmar == "1"? "": "style='display:none;'") ?>>
			<div class="fl-fix smallfont2" style="min-width: 60px">
				<b><span class='language_th'>หมายเหตุ:</span></b>
			</div>
			<div class="fl-fix" style="min-width: 260px; max-height: 56px">
				<textarea name="cn_patient_note" data-id="cn_patient_note" data-require='' data-odata='' class='save-data v_text input-group smallfont2 input-group' value='' rows='3' style="max-height: 56px"></textarea>
			</div>
			<div class='fl-fix w-m' name="div_form_view_data" data-uid='<? echo($sUid); ?>' data-coldate='<? echo($sColDate); ?>' data-coltime='<? echo($sColTime); ?>' data-ss='<? echo($sSID); ?>' data-clinicid = '<? echo $sClinicID; ?>'>
				<button id='btn_save_form_view_serch' class='btn btn-success smallfont2 border' type='button' onclick='saveFormData_search();'><i class="fa fa-pencil-square-o" aria-hidden='true'></i> <b>บันทึกข้อมูล</b> </button>
				<i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
			</div>
		</div>
	</div>
</div>

<script>
$(function(){
	<?
		//if($sToday!=$sColDate) {
			//echo("$(\"#divSOD #divSOD_find\").remove();");
			//echo("$(\"#divSOD #divSOD_list .fabtn\").remove();");
		//}
	?>

	<? echo $sJS; ?>

	$("#divSOD .quick-view").unbind("click");
	$("#divSOD .quick-view").on("click",function(){
		sUid=getKeyVal($("#divSOD"),"uid");
		sUrl = $(this).attr("data-url")+".php?uid="+sUid;
		startLoad($("#divSOD #divQuickView"),$("#divSOD #divQuickView-loader"));
		$("#divSOD #divQuickView").load(sUrl,function(){
			endLoad($("#divSOD #divQuickView"),$("#divSOD #divQuickView-loader"));
		});
	});


	$("#divSOD .quick-add").unbind("click");
	$("#divSOD").on("click",".quick-add",function(){
		sMsg=$(this).attr("title");
		sSupCode=$(this).attr("data-supcode");
		sNoDup=$(this).attr("data-nodup");
		objr = $(this);
		sUid=getKeyVal($("#divSOD"),"uid");
		sColD=getKeyVal($("#divSOD"),"collect_date");
		sColT=getKeyVal($("#divSOD"),"collect_time");
		sAmt=$(this).attr('data-amt');
		sComment = $(this).attr('data-comment');
		//if(confirm("ยืนยัน"+sMsg+"?")){
			aData={u_mode:"quick_add_supply",uid:sUid,collect_date:sColD,collect_time:sColT,supply_code:sSupCode,sale_opt_id:"R01",nodup:sNoDup,amt:sAmt,comment:sComment};
			sURL="supply_a.php";
	        callAjax(sURL,aData,function(jRes,retAData){
				if(jRes.res=="1"){
					reloadSOD_List(sUid,sColD,sColT);
					setDlgResult("REFRESH",$(objr));
				}else{
					$.notify(jRes.msg);
				}
	        });
		//}
	});
	$("#divSOD #divSOD_list .btndeleteorder").unbind("click");
	$("#divSOD #divSOD_list").on("click",".btndeleteorder",function(){
		objr = $(this).closest(".data-row");
		sSupCode = $(objr).attr("data-supcode");
		sSupName = ($(objr).find(".supply-name").html());
		sUid=getKeyVal($("#divSOD"),"uid");
		sColD=getKeyVal($("#divSOD"),"collect_date");
		sColT=getKeyVal($("#divSOD"),"collect_time");
		btnThis= $(this).closest("#divSOD");
		if(confirm("ยืนยันลบข้อมูล?\r\nConfirm remove this item?\r\n"+sSupName)){
			sOCode = $(objr).attr("data-ocode");
			sStatus = $(objr).attr("data-ostatus");
			sIsService = $(objr).attr("data-isservice");
			sIsPaid = $(objr).attr("data-ispaid");
			sIsPickup = $(objr).attr("data-ispickup");

			sURL="supply_a.php"; aData={u_mode:"delete_supply_order",order_code:sOCode,supply_code:sSupCode,uid:sUid,collect_date:sColD,collect_time:sColT};
			startLoad($(objr).find(".fabtn"),$(objr).find(".fabtn-loader"));
	        callAjax(sURL,aData,function(jRes,retAData){
				if(jRes.res=="1"){
					$(objr).remove();
					setDlgResult("REFRESH",$(btnThis));

				}else{
					endLoad($(objr).find(".fabtn"),$(objr).find(".fabtn-loader"));	
				}

	        });
		}
	});

	$("#divSOD #divSOD_list .btneditorder").unbind("click");
	$("#divSOD #divSOD_list").on("click",".btneditorder",function(){
		objr = $(this).closest(".data-row");
		sSupCode = $(objr).attr("data-supcode");
		sSupName = ($(objr).find(".supply-name").html());
		sUid=getKeyVal($("#divSOD"),"uid");
		sColD=getKeyVal($("#divSOD"),"collect_date");
		sColT=getKeyVal($("#divSOD"),"collect_time");
		sOCode = $(objr).attr("data-ocode");
		sStatus = $(objr).attr("data-ostatus");
		sIsService = $(objr).attr("data-isservice");
		sIsPaid = $(objr).attr("data-ispaid");
		sIsPickup = $(objr).attr("data-ispickup");

		sURL="supply_order_edit_dlg.php?"+qsTxt(sUid,sColD,sColT)+"&supply_code="+sSupCode+"&order_code="+sOCode; 
		showDialog(sURL,qsTitle(sUid,sColD,sColT),"320","80%","",
		function(sResult){
			//CLose function
			if(sResult=="1"){
				reloadSOD_List(sUid,sColD,sColT);
				setDlgResult("REFRESH",$(objr));
			}
		},false,function(){
			//Load Done Function
		});

	});

	function reloadSOD_List(sUid,sColD,sColT){
		sUrl="supply_order_list.php?uid="+sUid+"&coldate="+sColD+"&coltime="+sColT;
		startLoad($("#divSOD #divSOD_list"),$("#divSOD #divSOD_list-loader"));
		$("#divSOD #divSOD_list").load(sUrl,function(){
			endLoad($("#divSOD #divSOD_list"),$("#divSOD #divSOD_list-loader"));
		});

	}

	$("#divSOD #divSOD_find .data-row").unbind("click");
	$("#divSOD #divSOD_find").on("click",".data-row",function(){
		sCode = ($(this).attr("data-supcode"));
		sName = encodeURIComponent($(this).find("div[data-keyid='supply_name']").html());
		sLot = $(this).attr('data-stklot');
		sType = $(this).attr('data-gtype');
		sIsService = $(this).attr('data-isservice');
		sAmt = $(this).attr('data-amt');
		objr = $(this).closest("#divSOD");
		if((sAmt=="0" || sAmt=="" || sAmt=="undefined") && sIsService=="0"){
			$.notify(decodeURIComponent(sName)+" : ไม่มีของ");
			return;
		}

		sUid = getKeyVal($("#divSOD"),"uid");
		sColD = getKeyVal($("#divSOD"),"collect_date");
		sColT= getKeyVal($("#divSOD"),"collect_time");

		if(sCode.substr(0, 3) == "PAK"){
			sUrl="supply_order_package_dlg.php?supply_code="+sCode+"&"+qsTxt(sUid,sColD,sColT);
			showDialog_edit(sUrl, "Package Items Group : "+sCode+" ["+decodeURIComponent(sName)+"]", "50%", "80%", "",
			function(sResult){
				//CLose function
				if(sResult=="1"){
					sUrl="supply_order_list.php?uid="+sUid+"&coldate="+sColD+"&coltime="+sColT;
					startLoad($("#divSOD #divSOD_list"),$("#divSOD #divSOD_list-loader"));
					$("#divSOD #divSOD_list").load(sUrl,function(){
						endLoad($("#divSOD #divSOD_list"),$("#divSOD #divSOD_list-loader"));
					});
					setDlgResult("REFRESH",$(objr));
				}
			}, true, function(){
				//Load Done Function
			});
		}
		else{
			sUrl="supply_order_inc_detail.php?supply_code="+sCode+"&"+qsTxt(sUid,sColD,sColT);
			showDialog(sUrl,sCode+" : "+decodeURIComponent(sName),"80%","80%","",
			function(sResult){
				//CLose function
				if(sResult=="1"){
					sUrl="supply_order_list.php?uid="+sUid+"&coldate="+sColD+"&coltime="+sColT;
					startLoad($("#divSOD #divSOD_list"),$("#divSOD #divSOD_list-loader"));
					$("#divSOD #divSOD_list").load(sUrl,function(){
						endLoad($("#divSOD #divSOD_list"),$("#divSOD #divSOD_list-loader"));
					});
					setDlgResult("REFRESH",$(objr));
				}
			},false,function(){
				//Load Done Function
			});
		}
	});
});

function getWObjValue_search(obj){
	var sValue = "";
	if($(obj)){
		var sTagName = $(obj).prop("tagName").toUpperCase();

		if(sTagName=="INPUT"){
			if($(obj).prop("type")){
				if($(obj).prop("type").toLowerCase()=="checkbox"){
					sValue = ($(obj).prop("checked"))?1:"";
				}
				else if($(obj).prop("type").toLowerCase()=="radio"){
					var sName = $(obj).attr("name");
					sValue = $("input[name='"+sName+"']").filter(":checked").val();
				}
				else{
					sValue = $(obj).val();
				}
			}
			else{
				sValue = $(obj).val();
			}
		}
		else{
			sValue = $(obj).val();
		}

		if($(obj).hasClass("v_date")){
			var arrDate = sValue.split("/");

			if(arrDate.length == 3){
				sValue = (parseInt(arrDate[2]) - 543)+"-"+arrDate[1]+"-"+ arrDate[0] ;
			}
		}
		
		return sValue;
	}
}

function saveFormData_search(){
        var divSaveData = "div_form_view_data";
        var lst_data_obj = [];

        // In case change value if not have value not change.
        $("#divSOD .save-data-radio:checked").each(function(ix,objx){
            $("input[name="+$(objx).data("id")+"]").data("val",  $(objx).val());
            // console.log("data_id_radio: "+$(objx).data("id")+"/"+$(objx).val());
        });
        $("#divSOD .save-data-radio:checked").each(function(ix,objx){
            $("input[name="+$(objx).data("id")+"]").data("val",  $(objx).val());
            // console.log("data_id_radio: "+$(objx).data("id")+"/"+$(objx).val());
        });

        var old_value = "";
        $("#divSOD .save-data").each(function(ix,objx){
            var objVal = "";
            var odata_val = "";
            
            objVal = getWObjValue_search($(objx));
            odata_val = $(objx).data("odata");
            if(typeof odata_val === "undefined"){
                odata_val = "";
            }
            if(typeof objVal === "undefined"){
                objVal  = "";
            }
            odata_val = (odata_val?odata_val.toString().replace(/"|'/g,''):odata_val); //ไม่ใช้แล้วเพราะใช้ json_encode()
            // console.log(odata_val+"new"+objVal);
            // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val.toString().replace(/'/g,"")); //cn_family_history_text
            // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val);

            if(objVal != odata_val){
                var data_item = {};

                data_item[$(objx).data("id")] = (objVal?objVal.toString().replace(/"|'/g,'') : objVal);
                lst_data_obj.push(data_item);
                // console.log("data_id: "+$(objx).data("id")+":"+objVal+"-"+odata_val+";");
            }

            old_value = $(objx).data("id");
        });

        if(lst_data_obj.length > 0){
            var aData = {
                uid:$("[name="+divSaveData +"]").data("uid"),
                coldate:$("[name="+divSaveData +"]").data("coldate"),
                coltime:$("[name="+divSaveData +"]").data("coltime"),
                sid:$("#data_defult").data("sid"),
                dataid:lst_data_obj,
            };
            // console.log(aData);

            callAjax("doctor_db_form_update.php", aData, saveFormDataComplete);
            $("#btn_save_form_view_serch").next(".spinner").show();
            $("#btn_save_form_view_serch").hide();
        }
        else{
            $.notify("No data change", "warn");
        }
    }

    function saveFormDataComplete(flagSave, aData, rtnDataAjax){
        // console.log(flagSave+"/"+rtnDataAjax);
        if(flagSave){
            $.notify("Save Data", "success");
            var divSaveData = "div_form_view_data";

            //update all odata of  value changed data_id
            var conValue = "";
            Object.keys(aData.dataid).forEach(function(i){
                Object.keys(aData.dataid[i]).forEach(function(data_id){
                    conValue = aData.dataid[i][data_id];
                    conValue = conValue;
                    // console.log(i+data_id + " - " +conValue);
                    $("[name="+data_id+"]").data("odata", conValue);
                });
            });

            // Open Dlg appointment calendar
            var sUid = flagSave["uid_rtn"];
            var sQ = flagSave["queue_rtn"];
            var coldate = $("[name=div_form_view_data]").data("coldate");
            
            var d = new Date();
            var day = d.getDate();
            var month = d.getMonth()+1;
            var year = d.getFullYear();
            var present_date = year+"-"+(month<10 ? '0' : '') + month +"-"+(day<10 ? '0' : '') + day;
            var sUrl = "queue_inc_fwd.php?uid="+sUid+"&q="+sQ;
            // console.log(sUrl);
            // console.log(coldate+"/"+present_date);

            if(sQ != "" && coldate == present_date){
                showDialog(sUrl,"FWD ส่งคิวต่อไปห้องอื่น","600","1024","",function(result){
                    
                },false,function(){
                    $("#divQueueFwd input[name='room_no'][value='24']").prop("checked",true);
                    $("#divQueueFwd input[name='room_no'][value='24']").focus();
                });
            }
        }

        $("#btn_save_form_view_serch").next(".spinner").hide();
        $("#btn_save_form_view_serch").show();
    }
</script>