<?
include_once("in_session.php");
include_once("in_php_function.php");
$sUid = getQS("uid");
$sColDate =getQS("coldate");
$sColTime=urldecode(getQS("coltime"));
$sNextForm=getQS("next_form_id");
$sHideInfo=getQS("hideinfo");
$sHideEdit=getQS("hideedit");
$bIsStaff=false;
$sSid = getSS("s_id");

if($sSid != "") 
	$bIsStaff = true;

$sCol="";
$sQS = "?uid=".$sUid."&coldate=".$sColDate."&coltime=".$sColTime;
$sObjProp = " data-uid='".$sUid."' data-coldate='".$sColDate."' data-coltime='".$sColTime."'";
$sJS = "";
$jsHtml ="var sAllForm=\"".$sNextForm."\";";

$aColList=array("uid"=>"","uic"=>"","fname"=>"","sname"=>"","en_fname"=>"","en_sname"=>"","nickname"=>"","sex"=>"","gender"=>"","date_of_birth"=>"","nation"=>"","citizen_id"=>"","	passport_id"=>"","id_address"=>"","id_district"=>"","id_province"=>"","id_zone"=>"","id_postal_code"=>"","use_id_address"=>"","address"=>""," district"=>"","province"=>"","zone"=>"","postal_code"=>"","country_other"=>"","tel_no"=>"","email"=>"","blood_type"=>"","line_id"=>""," em_name_1"=>"","em_relation_1"=>"","em_phone_1"=>"","em_name_2"=>"","em_relation_2"=>"","em_phone_2"=>"","religion"=>"","remark"=>"","sale_opt_id"=>"","prep_nhso"=>"");

include("in_db_conn.php");

foreach ($aColList as $col => $val) {
	$sCol.=(($sCol=="")?"":",").$col;
}

$aPInfo = array();
$query ="SELECT $sCol FROM patient_info WHERE uid=?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s",$sUid);
if($stmt->execute()){
  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()) {
    $aPInfo = $row;
  }
}

$mysqli->close();



if($sHideEdit=="1"){
	$sJS = '$("#divPII input").prop("readonly",true);';
}
foreach ($aPInfo as $col => $sVal) {
	if($sVal!="") $jsHtml.="setKeyVal($(\"#divPII\"),\"".$col."\",getShowText(\"".urlencode($sVal)."\"));";
}

?>

<div id='divPII' class='fl-wrap-col'  <? echo($sObjProp); ?>>
	<div class='fl-wrap-col h-75 fl-mid'  style='<? echo(($sHideInfo=="1")?"display:none":""); ?>'>
		<div class='fl-fix h-25'>กรุณายืนยันข้อมูลส่วนตัว เพื่อประโยชน์ในการรักษา </div>
		<div class='fl-fix h-25'>Please confirm your information. The data will be used for the treatment information.</div>
		<div class='fl-fix h-25 fc-red'>IMPORTANT : หากท่านต้องการ ขอรับใบรับรองแพทย์ กรุณาใส่ข้อมูลที่ถูกต้อง</div>
	</div>
	<div class='fl-wrap-col fl-auto fs-small'>
		<div class='fl-wrap-row h-40 row-hover' style='background-color: white'>
			<div class='fl-wrap-col w-180'>
				<input type='hidden' data-keyid='uid' class='saveinput' data-pk='1' data-odata='<? echo($sUid); ?>' value='<? echo($sUid); ?>' />
				
				<div class='fl-wrap-row'>
					<div id='btnViewPInfoLog' class=' fabtn fl-fix w-30'  style='<? echo(($bIsStaff)?"":""); ?>'><i  class=' fas fa-info-circle fa-lg'></i></div>
					<div class='fl-fill lh-20 al-right fw-b'>ชื่อ*</div>
				</div>
				<div class='fl-fill lh-20 al-right'>First Name*</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-mid'><input name='fname' data-keyid='fname' value='' data-odata='' class='saveinput checkdata w-fill' data-require="1" /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>นามสกุล*</div>
				<div class='fl-fill lh-20 al-right'>Last Name*</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-mid'><input name='sname'  data-keyid='sname' value='' data-odata='' class='saveinput checkdata w-fill' data-require="1" /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>ชื่อเล่น</div>
				<div class='fl-fill lh-20 al-right'>Nickname</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-mid'><input name='nickname'  data-keyid='nickname' value='' data-odata='' class='saveinput checkdata w-fill'/></div>
		</div>

		<div class='fl-wrap-row h-40 row-hover' style='background-color: white'>
			<div class='fl-wrap-col w-180'>
				<input type='hidden' data-keyid='uid' class='saveinput' data-pk='1' data-odata='<? echo($sUid); ?>' value='<? echo($sUid); ?>' />
				
				<div class='fl-wrap-row'>
					<div id='btnViewPInfoLog' class=' fabtn fl-fix w-30'  style='<? echo(($bIsStaff)?"":""); ?>'><i  class=' fas fa-info-circle fa-lg'></i></div>
					<div class='fl-fill lh-20 al-right fw-b'>ชื่ออังกฤษ*</div>
				</div>
				<div class='fl-fill lh-20 al-right'>First Name*</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-mid'><input name='en_fname' data-keyid='en_fname' value='' data-odata='' class='saveinput checkdata w-fill' /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>นามสกุลอังกฤษ*</div>
				<div class='fl-fill lh-20 al-right'>Last Name*</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-mid'><input name='en_sname'  data-keyid='en_sname' value='' data-odata='' class='saveinput checkdata w-fill' /></div>
		</div>


		<div class='fl-wrap-row h-40 row-hover'  style='background-color: white'>
			<div class='fl-wrap-col w-180'>
				<div class='fl-fill lh-20 al-right fw-b'>วันเกิด*</div>
				<div class='fl-fill lh-20 al-right'>Date of Birth*</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-mid'><input name='date_of_birth' data-keyid='date_of_birth' value='' data-odata='' class='saveinput checkdata w-fill' data-require="1" /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>เพศกำเนิด*</div>
				<div class='fl-fill lh-20 al-right'>Sex at Birth*</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-mid'><SELECT name='sex' data-keyid='sex' data-odata='' class='saveinput checkdata w-fill h-25' data-require="1"><option value="">-- เลือก --</option><option value='1'>ชาย / Man</option><option value='2'>หญิง / Women</option><option value='3'>มีเพศสรีระทั้งชายและหญิง / Intersex</option></SELECT></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>อัตลักษณ์ทางเพศ*</div>
				<div class='fl-fill lh-20 al-right'>Gender identity</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-mid'><SELECT data-keyid='gender' data-require='1' data-odata='' class='saveinput checkdata w-fill h-25' name='gender'>
				    <option value="">-- เลือก --</option>
				    <option value="1" data-forsex='3'>ไม่แน่ใจ (Questioning)</option>
				    <option value="2" data-forsex='1'>ชาย (male)</option>
				    <option value="3" data-forsex='2'>หญิง (female)</option>
				    <option value="10" data-forsex='1'>ชายที่มีเพศสัมพันธ์กับชาย (MSM)</option>
					<option value="4" data-forsex='1'>ชายข้ามเพศเป็นหญิง (transgender women)</option>
				    <option value="5" data-forsex='2'>หญิงข้ามเพศเป็นชาย (transgender men)</option>
					<option value="6" data-forsex='1'>เกย์ (Gay man)</option>
					<option value="7" data-forsex='2'>เลสเบี้ยน (Lesbian)</option>
					<option value="8" data-forsex='3'>ไม่อยู่ในกรอบเพศชายหญิง (Gender variance/non-binary)</option>
					<option value="11" data-forsex='3'>ไบเซ็กชวล (Bisexual)</option>
					<option value="9" data-forsex='3'>ไม่ขอตอบ</option>
				    </SELECT>
			</div>
		</div>


		<div class='fl-wrap-row h-60 row-hover'  style='background-color: white'>
			<div class='fl-wrap-col w-180'>
				<div class='fl-fill lh-20 al-right fw-b'>ศาสนา</div>
				<div class='fl-fill lh-20 al-right'>Religion</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-mid'><SELECT class='saveinput checkdata w-fill h-25' name='religion' data-keyid='religion'>
						<option value='1'>ไม่นับถือศาสนาใด (Irreligious)</option>
						<option value='2'>พุทธ (Buddhism)</option>
						<option value='3'>คริสต์ (Christianity)</option>
						<option value='4'>อิสลาม (Islam)</option>
						<option value='5'>ฮินดู (Hinduism)</option>
						<option value='6'>อื่นๆ (Others)</option>
					</SELECT></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-25 al-right fw-b'>สัญชาติ</div>
				<div class='fl-fill lh-25 al-right'>Nationality</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-wrap-col'>
				<div class='fl-fill al-left'>
					<label><input name='nation' type='radio' value='THA' data-keyid='nation' data-odata='' class='saveinput checkdata' />ไทย Thai</label><br/>
				</div>
				<div class='fl-wrap-row'>
					<div class='fl-fix w-80 al-left'><label><input name='nation' data-keyid='nation' type='radio' value='2' data-odata='' class='saveinput checkdata' />อื่นๆ Other </label></div>
					
					<div class='fl-fill'><SELECT data-keyid='country_other' name='country_other' class='saveinput checkdata w-fill h-25' data-odata='' ><? include("country_inc_option.php"); ?></SELECT>
					</div>
				</div>
			</div>
			<div class='fl-wrap-col w-130'>
				<div id='btnUnlockCitizenId' class='fl-fix  h-15 lh-15 al-right fw-b'>บัตรประชาชน</div>
				<div class='fl-fix h-10 lh-10 al-right'>Thai Citizen Id</div>
				<div class='fl-fix h-15 lh-15 al-right fw-b'>พาสปอร์ท</div>
				<div class='fl-fix h-10 lh-10 al-right'>Passport</div>
			</div>
			<div class='fl-wrap-col w-10'>
				<div class='fl-fix lh-25 fl-mid fw-b'>:</div>
				<div class='fl-fix lh-25 fl-mid fw-b'>:</div>
			</div>
			<div class='fl-wrap-col'>
				<div class='fl-fill fl-mid'><input data-keyid='citizen_id' size='13' maxlength="13" value='' type="number" data-odata='' class='h-20 saveinput checkdata w-fill' name='citizen_id' /></div>
				<div class='fl-fill fl-mid'><input data-keyid='passport_id' size='20' value='' data-odata='' class='h-20 saveinput checkdata w-fill' name='passport_id' /></div>
			</div>

		</div>


		<div class='fl-wrap-row h-50 row-hover' style='background-color: #dffdff'>
			<div class='fl-wrap-col w-180'>
				<div class='fl-fill lh-25 al-right fw-b'>กรุ๊ปเลือด</div>
				<div class='fl-fill lh-25 al-right'>Blood Group</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><SELECT data-keyid='blood_type' class='h-25 saveinput checkdata' name='blood_type' data-odata>
					    <option value="">-- เลือก --</option>
					    <option value="A">เอ A</option>
					    <option value="B">บี B</option>
					    <option value="AB">เอบี AB</option>
					    <option value="O">โอ O</option>
						<option value="NA">ไม่ทราบ Unknown</option>	
		    		</SELECT></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-25 al-right fw-b'>ที่อยู่ตามบัตร</div>
				<div class='fl-fill lh-25 al-right'>ID Address</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-mid'><textarea data-keyid='id_address' value='' data-odata=''  class='saveinput checkdata w-fill' name='id_address'></textarea></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-25 al-right fw-b'>แขวง/ตำบล</div>
				<div class='fl-fill lh-25 al-right'>Area</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input data-keyid='id_zone' name='id_zone' value='' data-odata='' class='saveinput checkdata' />
			</div>
		</div>
	


		<div class='fl-wrap-row h-40 row-hover' style='background-color: #dffdff'>
			<div class='fl-wrap-col w-180'>
				<div class='fl-fill lh-20 al-right fw-b'>เขต/อำเภอ</div>
				<div class='fl-fill lh-20 al-right'>District</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input data-keyid='id_district' name='id_district' value='' data-odata='' class='saveinput checkdata' /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>จังหวัด</div>
				<div class='fl-fill lh-20 al-right'>Province</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input data-keyid='id_province' name='id_province' value='' data-odata='' class='saveinput checkdata' /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>รหัสไปรษณีย์</div>
				<div class='fl-fill lh-20 al-right'>Postcode</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input maxlength="15" name='id_postal_code' data-keyid='id_postal_code' value='' data-odata='' class='saveinput checkdata' />
			</div>
		</div>


		<div class='fl-wrap-row h-50 row-hover' style='background-color: #ffedfe'>
			<div class='fl-wrap-col w-180'>
				<div class='fl-fill lh-25 al-right fw-b'>ที่อยู่ที่ติดต่อ</div>
				<div class='fl-fill lh-25 al-right'>Contact</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-wrap-col'>
				<div class='fl-fill fl-vmid'><label><input name='use_id_address' data-keyid='use_id_address' type='radio' value='1' data-odata='' class='saveinput' />ใช้ที่อยู่เดียวกับบัตร</label></div>
				<div class='fl-fill fl-vmid'><label><input name='use_id_address' type='radio' value='2' class=' checkdata' />อื่นๆ Other </label></div>
			</div>

			<div class='fl-wrap-col w-130 contactaddress'>
				<div class='fl-fill lh-25 al-right fw-b'>ที่อยู่</div>
				<div class='fl-fill lh-25 al-right'>Contact Address</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b contactaddress'>:</div>
			<div class='fl-fill fl-mid contactaddress'><textarea data-keyid='address' value='' data-odata=''  class='saveinput checkdata w-fill' name='address'></textarea></div>

			<div class='fl-wrap-col w-130 contactaddress'>
				<div class='fl-fill lh-25 al-right fw-b'>แขวง/ตำบล</div>
				<div class='fl-fill lh-25 al-right'>Area</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b contactaddress'>:</div>
			<div class='fl-fill fl-vmid contactaddress'><input data-keyid='zone' name='zone' value='' data-odata='' class='saveinput checkdata' />
			</div>
		</div>

		<div class='fl-wrap-row h-40 row-hover contactaddress' style='background-color: #ffedfe'>
			<div class='fl-wrap-col w-180'>
				<div class='fl-fill lh-20 al-right fw-b'>เขต/อำเภอ</div>
				<div class='fl-fill lh-20 al-right'>District</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input data-keyid='district' name='district' value='' data-odata='' class='saveinput checkdata' /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>จังหวัด</div>
				<div class='fl-fill lh-20 al-right'>Province</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input data-keyid='province' name='province' value='' data-odata='' class='saveinput checkdata' /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>รหัสไปรษณีย์</div>
				<div class='fl-fill lh-20 al-right'>Postcode</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input maxlength="15" name='postal_code' data-keyid='postal_code' value='' data-odata='' class='saveinput checkdata' />
			</div>
		</div>


		<div class='fl-wrap-row h-40 row-hover' style='background-color: white'>
			<div class='fl-wrap-col w-180'>
				<div class='fl-fill lh-20 al-right fw-b'>โทรศัพท์*</div>
				<div class='fl-fill lh-20 al-right'>Phone*</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input data-keyid='tel_no' name='tel_no' value='' data-odata='' class='saveinput checkdata' data-require='1' /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>Email</div>
				<div class='fl-fill lh-20 al-right'><i class='fas fa-envelope fa-lg'></i></div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input data-keyid='email' name='email' value='' data-odata='' class='saveinput checkdata w-fill' /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>Line Id</div>
				<div class='fl-fill lh-20 al-right' style='color:green'><i class='fab fa-line fa-lg'></i></div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input maxlength="15" name='line_id' data-keyid='line_id' value='' data-odata='' class='saveinput checkdata' />
			</div>
		</div>

		<div class='fl-wrap-row h-40 row-hover' style=';background-color: #ffedfe'>
			<div class='fl-wrap-col w-180'>
				<div class='fl-fill lh-20 al-right fw-b'>ชื่อ ช่องทางติดต่อฉุกเฉิน 1</div>
				<div class='fl-fill lh-20 al-right'>Emergency Contact 1</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input data-keyid='em_name_1' name='em_name_1' value='' data-odata='' class='saveinput checkdata' /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>ความสัมพันธ์ 1</div>
				<div class='fl-fill lh-20 al-right'>Relationship 1</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input data-keyid='em_relation_1' name='em_relation_1' value='' data-odata='' class='saveinput checkdata' /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>เบอร์โทรศัพท์</div>
				<div class='fl-fill lh-20 al-right'>Phone number 1</i></div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input maxlength="15" name='em_phone_1' data-keyid='em_phone_1' value='' data-odata='' class='saveinput checkdata' />
			</div>
		</div>


		<div class='fl-wrap-row h-40 row-hover' style=';background-color: #ffedfe'>
			<div class='fl-wrap-col w-180'>
				<div class='fl-fill lh-20 al-right fw-b'>ชื่อ ช่องทางติดต่อฉุกเฉิน 2</div>
				<div class='fl-fill lh-20 al-right'>Emergency Contact 2</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input data-keyid='em_name_2' name='em_name_2' value='' data-odata='' class='saveinput checkdata' /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>ความสัมพันธ์ 2</div>
				<div class='fl-fill lh-20 al-right'>Relationship 2</div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input data-keyid='em_relation_2' name='em_relation_2' value='' data-odata='' class='saveinput checkdata' /></div>

			<div class='fl-wrap-col w-130'>
				<div class='fl-fill lh-20 al-right fw-b'>เบอร์โทรศัพท์</div>
				<div class='fl-fill lh-20 al-right'>Phone number 2</i></div>
			</div>
			<div class='fl-fix w-10 fl-mid fw-b'>:</div>
			<div class='fl-fill fl-vmid'><input maxlength="15" name='em_phone_2' data-keyid='em_phone_2' value='' data-odata='' class='saveinput checkdata' />
			</div>
		</div>

		<div class='fl-wrap-row h-60' style='<? echo(($bIsStaff)?"":"display:none"); ?>;background-color: white'>
			<div class='fl-fix w-180 al-right fw-b'>Remark</div>
			<div class='fl-fix w-10 fw-b'>:</div>
			<div class='fl-fill'><textarea id='txtremark' class='saveinput checkdata w-fill' name='remark' data-keyid='remark'></textarea></div>
			<div class='fl-wrap-col f-border w-200' style='<? echo(($sSid=="")?"display:none":""); ?>'>
				<div class='fl-fix fw-b fl-mid'>NHSO ID</div>
				<div class='fl-fill fl-mid'><input class='saveinput wper-80' data-keyid='prep_nhso' name='prep_nhso' data-odata=''  /> </div>
			</div>
		</div>
	</div>
	<div class='fl-fix h-50 fl-mid row-hover' style='background-color: white;<? echo(($sHideEdit!="1" || $bIsStaff)?"":"display:none"); ?>'>
		<input type='button' id='btnUpdatePInfo' value='ยืนยัน / Confirm' class='fs-xlarge'/><img id='imgPLoader' src='assets/image/spinner.gif' style='display:none'  />
	</div>
</div>



<script>
	function isEmpty(sText){
		if(!sText|| $sText=="") return "";
		else return sText; 
	}
	$(function(){
		<? echo($jsHtml); ?>

		$("#txtDOB").datepicker({dateFormat:"yy-mm-dd",changeMonth:true,changeYear:true});
		
		$("#divPII #btnViewPInfoLog").off("click");
		$("#divPII #btnViewPInfoLog").on("click",function(){
			sUid=getKeyVal($("#divPII"),"uid");
			sUrl="patient_inc_info_log.php?uid="+sUid;
			showDialog(sUrl,"PatientInfo Log for "+sUid,"90%","90%","",
			function(sResult){
				//CLose function
				if(sResult=="1"){
				}
			},false,function(){
				//Load Done Function
			});
		});


		$("#divPII #btnUpdatePInfo").off("click");
		$("#divPII #btnUpdatePInfo").on("click",function(){
			objPII = $(this).closest("#divPII");
			objThis=$(this);
			let sUid = $(objPII).attr("data-uid");
			let sColDate = $(objPII).attr("data-coldate");
			let sColTime = $(objPII).attr("data-coltime");

			$(objPII).find(".bg-error").removeClass("bg-error");

			//Check IsRequire
			$(objPII).find(".saveinput[data-require='1']").each(function(ix,objx){
				sKeyId=$(objx).attr("data-keyid");
				objVal=getKeyVal(objPII,sKeyId);
				if(objVal==""){
					$(objx).addClass("bg-error");
					
				}
			});

			if($(objPII).find(".bg-error").length){
				$.notify("กรุณาตอบคำถามในข้อที่สำคัญ (สีแดง)\r\nMissing Data. (Red Box)");
				return;
			}

			sEmail = getKeyVal(objPII,"email");
			if(sEmail != "" && !checkEmail(sEmail)){
				$.notify("รูปแบบ Email ไม่ถูกต้อง \r\nInvalid Email Format");
				$(objPII).find("input[data-keyid='email']").focus();
				return;
			}

			aData=getDataRow(objPII);

			aAllForm = sAllForm.split(",");  sNextForm = ""; isFound = false;
			for(ix=1;ix<aAllForm.length;ix++){
				sNextForm += ((sNextForm=="")?"":",")+aAllForm[ix];
				isFound=true;
			}

			var sUrl = "";

			if(aAllForm.length>0) sUrl = "p_form_view.php?s_id=patient&form_id="+aAllForm[0]+"&lang=th&uid="+sUid+"&coldate="+sColDate+"&coltime="+sColTime+"&next_form_id="+sNextForm;


			
			if(aData.length==0){
				$.notify("ไม่มีข้อมูลเปลี่ยนแปลง\r\nNo data changed.","info");

				if(sAllForm==""){

				}else{
					$.notify("กรุณารอสักครู่\r\nPlease wait.","success");
					$(objPII).parent().load(sUrl,function(){});	
				}
				
			}else{
				aData.u_mode="update_patient_info";
				aData.u=sUid;
				aData.coldate=sColDate;
				aData.coltime=sColTime;
				startLoad($("#btnUpdatePInfo"),$("#imgPLoader"));
				callAjax("patient_a.php",aData,function(rtnObj,aData){
					if(rtnObj.res=="0"){
						$.notify(rtnObj.msg);
						endLoad($("#btnUpdatePInfo"),$("#imgPLoader"));
					}else if(rtnObj.res=="1"){
						setKeyAllOld(objPII);
						if(sAllForm==""){
							$.notify("Data Saved.\r\nบันทึกสำเร็จ","success");
							setDlgResult("REFRESH",$("#btnUpdatePInfo"));
							endLoad($("#btnUpdatePInfo"),$("#imgPLoader"));
						}else{
							$.notify("กรุณารอสักครู่\r\nPlease wait.","success");
							$(objPII).parent().load(sUrl,function(){});

						}
					}
				});
			}
			
		});


		$("#divPII input[name='use_id_address']").off("change");
		$("#divPII input[name='use_id_address']").on("change",function(){
			objPII=$(this).closest("#divPII");
			useIDAdd=getKeyVal(objPII,"use_id_address");
			if(useIDAdd=="1"){
				//Use Citizen
				$(objPII).find(".contactaddress").hide();
			}else{
				$(objPII).find(".contactaddress").show();
			}
		});

		$("#divPII #btnUnlockCitizenId").off("dblclick");
		$("#divPII #btnUnlockCitizenId").on("dblclick",function(){
			$.notify("Citizen Id is unlocked.","info");
			objPII=$(this).closest("#divPII");
			$(objPII).find(".saveinput[data-keyid='citizen_id']").removeAttr("disabled");
		});


		$("#divPII input[name='nation']").off("change");
		$("#divPII input[name='nation']").on("change",function(){
			objPII=$(this).closest("#divPII");
			nation=getKeyVal(objPII,"nation");
			citizen_id=getKeyVal(objPII,"citizen_id");
			if(nation=="1" || nation=="THA"){
				//Thai
				if(citizen_id*1==0 || citizen_id*1==1 || citizen_id==""){
					$(objPII).find(".saveinput[data-keyid='citizen_id']").removeAttr("disabled");
				}else{
					$(objPII).find(".saveinput[data-keyid='citizen_id']").attr("disabled",true);
				}
				//$(objPII).find(".saveinput[data-keyid='citizen_id']").removeAttr("disabled");

				$(objPII).find(".saveinput[data-keyid='country_other']").val("");
				$(objPII).find(".saveinput[data-keyid='country_other']").prop("disabled",true);
			}else{
				if(citizen_id*1==0 || citizen_id*1==1 || citizen_id=="" ) setKeyVal(objPII,"citizen_id","0000000000000");
				$(objPII).find(".saveinput[data-keyid='country_other']").removeAttr("disabled");
				$(objPII).find(".saveinput[data-keyid='citizen_id']").attr("disabled",true);
			}
		});


		//$("#divPII .saveinput[name='use_id_address']").trigger("change");
		//$("#divPII input[name='nation']").trigger("change");
		$("#divPII .saveinput").trigger("change");

		function getShowText(sObjValue){
			var	result = "";

			if(sObjValue){
				skey = new RegExp(/&/i,'g');
				//result = sObjValue.replace(skey,"&#38;");
				result = sObjValue.replace(skey,"\u2026");

				skey = new RegExp(/'/i,'g');
				//result = result.replace(skey,"&#39;");
				result = result.replace(skey,"\u2027");

				skey = new RegExp(/"/i,'g');
				//result = result.replace(skey,"&#34;");
				result = result.replace(skey,"\u2022");

				skey = new RegExp(/</i,'g');
				//result = result.replace(skey,"&#60;");
				result = result.replace(skey,"\u003C");

				skey = new RegExp(/>/i,'g');
				//result = result.replace(skey,"&#62;");
				result = result.replace(skey,"\u003E");

				skey = new RegExp(/ /i,'g');
				//result = result.replace(skey,"&#32;");
				result = result.replace(skey,"\u2002");
				
			}
			result = decodeURIComponent(result.replace(/\+/g, ' '));

			return result;
		}
	});
</script>