<?
include("in_session.php");
include_once("in_db_conn.php");
include_once("in_php_function.php");
include_once("in_php_pop99.php");


$uid = getQS("uid");
$collect_date = getQS("coldate");
$collect_time = getQS("coltime");
$form_id = getQS("form_id");
$sVisitid = getQS("visitid");
$sProjid = getQS("projid");

$sLang = getQS("lang");
if($sLang == "") $sLang="th"; // thai default language

$sClinicid = getSS('clinic_id');

$s_id = "";
$sVisitstatus = ""; $sLaborderid = "";
$form_name_th="";$form_name_en="";$form_protocol_version="";


$is_external = 0;
// link form external link
$sLink = getQS("link");
if($sLink != ""){
  include_once("in_php_encode.php");
  $sLink = decodeSingleLink($sLink);
  $arr = explode(",",$sLink);
  if(count($arr) <> 6){
    echo "Invalid Link: Please contact staff.";
    exit();
  }
  else{
    $form_id = $arr[0];
    $uid = $arr[1];
    $collect_date = $arr[2];
    $collect_time = $arr[3];
    $sProjid = $arr[4];
    $sValidate = $arr[5];
    if($sValidate != "ihriform"){
      echo "Invalid Link2: Please contact staff.";
      exit();
    }

    $is_external = 1;
    $flag_external_update = 1;
    $msg_external_update = "";
    $query ="SELECT is_done FROM p_data_form_done
    WHERE form_id =? AND uid=? AND collect_date=? AND collect_time=? ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ssss',$form_id, $uid,$collect_date , $collect_time);
    //echo "$sID, $sProjid / $query";
    if($stmt->execute()){
      $result = $stmt->get_result();
      if($row = $result->fetch_assoc()) {
        if($row['is_done']){
          $flag_external_update = 0;
          $msg_external_update = "แบบฟอร์มได้ทำแล้ว";
        }
      }
    }
    $stmt->close();


//    if($collect_date != )
    if($flag_external_update){
      if(date("Y-m-d") != $collect_date){

        $query ="SELECT count(visit_id) as visit_amt_after FROM p_project_uid_visit
        WHERE uid=? AND proj_id=? AND visit_date > ? and visit_status <> 0";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('sss',$uid,$sProjid, $collect_date );
        //echo "$sID, $sProjid / $query";
        if($stmt->execute()){
          $result = $stmt->get_result();
          if($row = $result->fetch_assoc()) {
            if($row['visit_amt_after'] > 0){
              $flag_external_update = 0;
              $msg_external_update = "แบบฟอร์มนี้เกินกำหนดแล้ว";
            }

          }
        }
        $stmt->close();

      }
    }

    if($flag_external_update == 0) {
      echo "ไม่สามารถทำแบบฟอร์มนี้ได้เนื่องจาก $msg_external_update";
  //    exit();
    }
  }

}// end external link



  $query = "SELECT PUV.visit_status, LO.lab_order_id
  FROM p_project_uid_visit PUV
  LEFT JOIN p_lab_order LO ON LO.collect_date = PUV.visit_date AND LO.proj_id=PUV.proj_id AND LO.timepoint_id=''
  WHERE PUV.uid=? AND PUV.visit_date=? AND PUV.proj_id=? AND PUV.visit_id=?
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ssss',$uid, $collect_date, $sProjid, $sVisitid);
  //echo "query : $query";
  if($stmt->execute()){
    $stmt->bind_result($sVisitstatus, $sLaborderid);
    if ($stmt->fetch()) {
    }// if
  }
  else{
    $msg_error .= $stmt->error;
  }
  $stmt->close();



$allow_view = 0; $allow_data = 0; $allow_databackdate = 0;
$fnc_save_formdone = "";




// check permission
include_once("in_session.php");


$s_id = getSS('s_id');


$saveButton_JS = "";

if(date("Y-m-d") == $collect_date || $is_external==1){
  $saveButton_JS = "$('.btn-save-data').show();";
}

//echo "date: ".date("Y-m-d");
if($s_id == ''){ // patient case
  $s_id = getQS("s_id");
}
else{ // staff
  $query ="SELECT * FROM p_staff_auth
  WHERE s_id =? AND proj_id=? ";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ss',$s_id, $sProjid);
//echo "$sID, $sProjid / $query";
  if($stmt->execute()){
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()) {
      $allow_view = $row['allow_view'];
      $allow_data = $row['allow_data'];
      $allow_databackdate = $row['allow_data_backdate'];
    }
  }
  $stmt->close();


  $flag_allow_data = 0;
  if($sProjid == ''){ // form in qa_index
    $flag_allow_data = 1;
  }
  else{ // form in pribta21
    if($allow_data){
      $flag_allow_data = 1;
    }
  }




  if($flag_allow_data){
      if($saveButton_JS == '')
      $saveButton_JS .= "$('.btn-save-data-activate').show();";

      $saveButton_JS .= "
      $('.div-form-view .btn-save-data-activate').off('click');
      $('.div-form-view .btn-save-data-activate').on('click',function(){
         $('.div-form-view .btn-save-data').show();
         $(this).hide();
      });
      ";

      // allow to save incomplete form data
      $fnc_save_formdone = "
      if(flag_form_done == 2){
         if(confirm('ข้อมูลในฟอร์มยังไม่ครบถ้วน ต้องการยืนยันที่จะบันทึกหรือไม่?')){
         }
         else{
           return;
         }
      }//flag_form_done
      ";
  }
  else{
    // allow to save incomplete form data
    $fnc_save_formdone = "
    if(flag_form_done == 2){
      $.notify('ข้อมูลไม่ครบ | Incomplete Data.','error');
      return;
    }
    ";
  }

  if($sVisitstatus == '1'){ // สถานะเสร็จสิ้น ห้ามแก้ไขฟอร์ม
     if($allow_databackdate == '0'){
       $saveButton_JS = "$('.div-btn-save').html('- <b>แบบฟอร์มสถานะเสร็จสิ้นแล้ว ไม่สามารถแก้ไขได้</b> (กรุณาติดต่อเจ้าหน้าที่ฝ่ายจัดการข้อมูล) -');";
       $fnc_save_formdone = "return;";
     }
  }


  $saveButton_JS .= "$('.btn-hist-log').show();";




} // end staff






//Jeng Code for next form
$sNFormId = getQS("next_form_id");
$aFormList = array(); $qsNextForm = "";
if($sNFormId!=""){
  $aFormList = explode(",",$sNFormId);
  $sNFormId = $aFormList[0];
  for($ix=1;$ix<count($aFormList);$ix++){
    $qsNextForm.= (($qsNextForm=="")?"":",").$aFormList[$ix];
  }
}
// End Jeng Code



if($s_id == ""){
  if (session_status() == PHP_SESSION_NONE) session_start();

   if(isset($_SESSION["s_id"])){
     $s_id =$_SESSION["s_id"];
   }
}

$show_data_id = getQS("show_data_id");




$form_row_height = '50';
$d_choice_hide = "";







$query = "SELECT
FL.form_name_th, FL.form_name_en, PP.protocol_version
FROM p_form_list as FL
LEFT JOIN i_protocol_form PF ON PF.form_id=FL.form_id
LEFT JOIN i_project_protocol PP ON PP.protocol_id=PF.protocol_id

WHERE FL.form_id=?
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$form_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($form_name_th, $form_name_en, $form_protocol_version);
  if ($stmt->fetch()) {
  }// if
}
else{
  $msg_error .= $stmt->error;
}
$stmt->close();



// data itm properties
$d_itm_prop = array();
$query = "SELECT  data_id, attr_id, attr_val
FROM p_form_list_data_attribute FLDA,
p_form_list as FL
WHERE FLDA.form_id=? AND FLDA.form_id=FL.form_id
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$form_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_id, $attr_id, $attr_val);
  while ($stmt->fetch()) {
      if(!isset($d_itm_prop[$data_id] ))  $d_itm_prop[$data_id] = array();

      $d_itm_prop[$data_id][$attr_id] = $attr_val;
  }// while
}
else{
  $msg_error .= $stmt->error;
}
$stmt->close();




// data itm sub list (choice of dropdown/radio)
$d_sub_item = array(); // sub data item in form
$query = "SELECT  FLD.data_id, FLD.data_type as comp_data_type,DL.data_type ,
DSL.data_value, DSL.data_name_th, DSL.data_name_en, FLD.is_require
FROM p_data_sub_list DSL, p_form_list_data FLD
LEFT JOIN p_data_list DL ON (DL.data_id=FLD.data_id)
WHERE FLD.form_id=? AND DSL.data_id=FLD.data_id
ORDER BY DSL.data_id, DSL.data_seq
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$form_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_id, $comp_data_type, $data_type, $data_value, $data_name_th, $data_name_en, $is_require);
  while ($stmt->fetch()) {
    if (empty($data_type)) {
      $data_type = $comp_data_type; // qlabel, html
    }

      if($data_type == "dropdown"){
        if(!isset($d_sub_item[$data_id] ))  $d_sub_item[$data_id] = "";
        //$d_sub_item[$data_id] .= "<option class='ddl lang th' data-val='th$data_id$data_value' value='$data_value' >$data_name_th </option><option class='ddl lang en' data-val='en$data_id$data_value' value='$data_value' > $data_name_en</option>";
        $d_sub_item[$data_id] .= "<option class='ddl' data-valth='$data_name_th' data-valen='$data_name_en'  value='$data_value' >$data_name_th </option>";
      }
      else if($data_type == "radio"){
        if(!isset($d_sub_item[$data_id] ))  $d_sub_item[$data_id] = "";
        $radio_css = ""; $radio_html = "<br>";
        $optalign = isset($d_itm_prop[$data_id]['optalign'] )?$d_itm_prop[$data_id]['optalign']:"V"; 
        if($optalign == 'H') {
          $radio_css = "style='margin-right:10px;'"; $radio_html = "";
        }
        $d_sub_item[$data_id] .= "<label class='pbtn' $radio_css><input type='radio' class='save-data v-radio'  name='$data_id' data-id='$data_id' data-isrequire='$is_require' data-odata='' value='$data_value' /> <span class='lang th'>$data_name_th</span><span class='lang en'>$data_name_en</span> </label>$radio_html";
      }

  }// while
}
else{
$msg_error .= $stmt->error;
}
$stmt->close();




//retrive dataID
$arr_data_result = array();
$arr_data_latest_result = array();

// latest data
$query_add_latest = '';
if($collect_date != "0000-00-00")
$query_add_latest = " AND r.collect_date <='$collect_date' AND r.collect_time <='23:59:00' ";

$query = "SELECT  r.data_id, r.data_result
FROM p_data_result as r, p_data_list as dl ,
p_form_list_data as f
WHERE r.uid=? AND f.form_id=?
AND r.data_id = f.data_id AND r.data_id=dl.data_id
AND dl.data_category = '2' $query_add_latest
ORDER BY r.collect_date
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('ss',$uid, $form_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_id, $data_result);
  while ($stmt->fetch()) {
     $arr_data_latest_result[$data_id] = $data_result;
  }// while
}
else{
$msg_error .= $stmt->error;
}
$stmt->close();


// get visit data
$query = "SELECT  ds.data_id, ds.data_result
FROM p_data_result as ds, p_form_list_data as fld
WHERE ds.uid=? AND ds.collect_date=? AND ds.collect_time=?
AND ds.data_id=fld.data_id AND fld.form_id=?
AND and ds.proj_id = ?;
";
//echo "$uid, $form_id / $query";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('ssss',$uid, $collect_date, $collect_time, $form_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_id, $data_result);
  while ($stmt->fetch()) {
  //  $arr_data_result[$data_id] = $data_result;
    $arr_data_result[$data_id] = htmlentities($data_result, ENT_QUOTES);
  }//while
}
$stmt->close();

$sHtml = "";
$query = "SELECT  FLD.data_seq, FLD.data_id, FLD.data_type as comp_data_type,DL.data_type, FLD.data_value,FLD.data_value_en,FLD.is_require,
DL.data_question_th as data_desc, DL.data_name_th as data_name, DL.data_prefix_th as prefix, DL.data_suffix_th as suffix,
DL.data_question_en as data_desc_en, DL.data_name_en as data_name_en, DL.data_prefix_en as prefix_en, DL.data_suffix_en as suffix_en
FROM p_form_list_data FLD
LEFT JOIN  p_data_list DL ON (FLD.data_id=DL.data_id)
WHERE FLD.form_id=?
ORDER BY FLD.data_seq
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$form_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_seq,  $data_id,$comp_data_type,  $data_type, $data_value,$data_value_en, $is_require,
  $data_desc, $data_name,  $prefix, $suffix, $data_desc_en, $data_name_en,  $prefix_en, $suffix_en
  );
  $width = "";
  $cur_q_label = "";
  while ($stmt->fetch()) {

    if (empty($data_type)) {
      $data_type = $comp_data_type; // qlabel, html
    }

    if($data_type == "html"){ // html
      $sHtml .= "<div id='$data_id' class='$data_type'><span class='lang th'>$data_value</span><span class='lang en'>$data_value_en</span></div>";
    }
    else if($data_type == "q_label"){ // topic title
      $cur_q_label = $data_id;
      if($data_value == '')
      $sHtml .= "<div id='$data_id'  class='q_label bg-mdark1 mt-2 mb-1'><hr></div>";

      else
      $sHtml .= "<div id='$data_id'  class='q_label bg-mdark1 ptxt-white ptxt-b px-1 py-2 mt-2 mb-1'><span class='lang th'>$data_value</span><span class='lang en'>$data_value_en</span></div>";
    }
    else { // data comp item
      $width = isset($d_itm_prop[$data_id]['width'])?$d_itm_prop[$data_id]['width']:"";
      $width = ($width !="")?"width:".$width."px;":"";

      $placeholder =isset($d_itm_prop[$data_id]['placeholder'])?$d_itm_prop[$data_id]['placeholder']:"";
      $placeholder = "placeholder='".(($placeholder !="")?$placeholder:"$data_desc $data_desc_en")."'";


      $special_data_class = "";
      $color_require = ($is_require == '1')?"pbg-yellow":"";
      if(isset($d_itm_prop[$data_id]['hideprefixsuffix'])){
        if($d_itm_prop[$data_id]['hideprefixsuffix'] == '1'){
          $prefix=""; $suffix=""; $prefix_en=""; $suffix_en="";
        }
      }


      $odata=""; $data_result="";
      if(isset($arr_data_result[$data_id])){
        $odata = $arr_data_result[$data_id];
        $data_result = $odata;
      }
      else{ // no value in selected visit
        if(isset($arr_data_latest_result[$data_id])){
          $data_result = $arr_data_latest_result[$data_id];
          $odata="odata"; // use to avoid blank in checkbox
        }

      }
      //echo "<br> $data_id / $data_type";

      $sHtml .= "<span class='showid'>$data_id</span><div id='$data_id' class='data-item pl-2'>";
      $sHtml .= "<span class='lang th'>$prefix </span><span class='lang en'>$prefix_en </span> ";
      if($data_type == "text" || $data_type == "date"){
        if($data_type == "date"){
          if(isset($d_itm_prop[$data_id]['partialdate'])){
            if($d_itm_prop[$data_id]['partialdate'] == '1'){
              $special_data_class="v-date-partial";
            }
          }
          if(isset($d_itm_prop[$data_id]['isthaidate'])){
            if($d_itm_prop[$data_id]['isthaidate'] == '1'){
              $data_type = "date-th";
            }
          }
        }//date
        $sHtml .= " <INPUT type='text' name='$data_id' value='$data_result' data-id='$data_id' data-odata='$odata' data-isrequire='$is_require' class='save-data $color_require v-$data_type $special_data_class' style='$width' $placeholder>";
      }
      else if($data_type == "number"){
        $sHtml .= " <INPUT type='number' name='$data_id' value='$data_result' data-id='$data_id' data-odata='$odata' data-isrequire='$is_require' class='save-data $color_require v-$data_type $special_data_class' style='$width' $placeholder>";
      }
      else if($data_type == "checkbox"){
      //  echo "<br>$data_id:$data_result";
        $is_check = ($data_result == '1')?"checked":"";
        if($data_name == "รักษาไวรัสตับอักเสบซี (Hepatitis C)"){
          $sHtml .= "<label class='pbtn'><INPUT type='checkbox' id='hcv_serv_chk' name='$cur_q_label' data-id='$data_id' data-odata='$odata' data-isrequire='$is_require' class='save-data v-checkbox $special_data_class' $is_check><span class='mx-1 lang th'>$data_name</span><span class='mx-1 lang en'>$data_name_en</span></label> <button id='form_hcv' disabled>บันทึกแบบฟอร์ม HCV</button";
        }else{
          $sHtml .= "<label class='pbtn'><INPUT type='checkbox' name='$cur_q_label' data-id='$data_id' data-odata='$odata' data-isrequire='$is_require' class='save-data v-checkbox $special_data_class' $is_check><span class='mx-1 lang th'>$data_name</span><span class='mx-1 lang en'>$data_name_en</span></label>";
        }
        // $sHtml .= "<label class='pbtn'><INPUT type='checkbox' name='$cur_q_label' data-id='$data_id' data-odata='$odata' data-isrequire='$is_require' class='save-data v-checkbox $special_data_class' $is_check><span class='mx-1 lang th'>$data_name</span><span class='mx-1 lang en'>$data_name_en</span></label>";
      }
      else if($data_type == "dropdown"){
        //echo "<br>ddl: $data_id/$data_result";
        $select_option = isset($d_sub_item[$data_id])?$d_sub_item[$data_id]:"";
        if($data_result != "") {
          $data_result = htmlspecialchars_decode($data_result);
          $txt_to_replace = "'$data_result'";
          $txtreplace = $txt_to_replace." selected ";
          $select_option = str_replace($txt_to_replace,$txtreplace,$select_option);
        }

        $sHtml .= "<SELECT  name='$data_id' data-odata='$odata' data-id='$data_id' data-isrequire='$is_require' class='save-data v-dropdown $color_require $special_data_class' style='$width'>";
        $sHtml .= "<option class='ddl' data-valth='เลือก [$data_desc]' data-valen='Select [$data_desc_en]'  value=''>เลือก [$data_desc]</option>";
        $sHtml .= $select_option;
        $sHtml .= "</SELECT>";

        if(isset($d_itm_prop[$data_id]['hidesomechoice'])){
          $hidesomechoice = $d_itm_prop[$data_id]['hidesomechoice'];
          $arrhidechoice = explode(",",$hidesomechoice);
          foreach($arrhidechoice as $hidechoice){
            $d_choice_hide .= "$(\"select[name='$data_id'] option[value='$hidechoice']\").hide();";
          }//foreach
        }

      }
      else if($data_type == "radio"){
      //  echo "$data_id <br>";
        $radio_option = isset($d_sub_item[$data_id])?$d_sub_item[$data_id]:"";
        if($data_result != "") {
          $data_result = htmlspecialchars_decode($data_result);
          $txt_to_replace = "value='$data_result'";
          $txtreplace = $txt_to_replace." checked ";
          $radio_option = str_replace($txt_to_replace,$txtreplace,$radio_option);

          $txt_to_replace = "data-odata=''";
          $txtreplace = "data-odata='$odata'";
          $radio_option = str_replace($txt_to_replace,$txtreplace,$radio_option);

        }

        $optalign = isset($d_itm_prop[$data_id]['optalign'])?$d_itm_prop[$data_id]['optalign']:"H";
        $sHtml .= $radio_option;

        if(isset($d_itm_prop[$data_id]['hidesomechoice'])){
          $hidesomechoice = $d_itm_prop[$data_id]['hidesomechoice'];
          $arrhidechoice = explode(",",$hidesomechoice);
          foreach($arrhidechoice as $hidechoice){
            $d_choice_hide .= "$(\"input[name='$data_id'][value='$hidechoice']\").parent().remove();";
          }//foreach
        }

      }//radio
      else if($data_type == "textarea"){
        $ta_row ="rows='".(isset($d_itm_prop[$data_id]['row'])?$d_itm_prop[$data_id]['row']:"3")."'";
        $ta_col ="cols='".(isset($d_itm_prop[$data_id]['col'])?$d_itm_prop[$data_id]['col']:"50")."'";
        $sHtml .= "<textarea  data-id='$data_id' data-odata='$odata' data-isrequire='$is_require' class='save-data v-text $color_require $special_data_class' width='100%' $ta_row $ta_col $placeholder name='$data_id'>$data_result</textarea>";
      }//textarea
      else if($data_type == "fileimage"){
        $fileimage = ($data_result !='')?"<a href='filedata/$data_result?t=".time()."' target='_blank'><img src='filedata/$data_result?t=".time()."' style='max-width:150px;' /></a>":"";

        $sHtml .= "<div id='fileimage$data_id' class='mb-1'>$fileimage <br>(ชนิดไฟล์ jpg|jpeg|png|gif|bmp ขนาดไฟล์ไม่เกิน 5MB | File size limit 5MB)</div>";
        $sHtml .= "<input type='file' data-filetype='fileimage' data-id='$data_id' data-odata='$odata' data-isrequire='$is_require' class='save-data-file $color_require $special_data_class' width='100%' $placeholder name='$data_id'>";
      }//fileimage
      else if($data_type == "filepdf"){
        $filepdf = ($data_result !='')?"<a href='filedata/$data_result?t=".time()."' target='_blank'><i class='fa fa-file-pdf fa-lg'></i> View PDF: $data_result </a>":"";

        $sHtml .= "<div id='filepdf$data_id' class='mb-1'>$filepdf <br>(ชนิดไฟล์ pdf ขนาดไฟล์ไม่เกิน 5MB | File size limit 5MB)</div>";
        $sHtml .= "<input type='file'  data-filetype='filepdf' data-id='$data_id' data-odata='$odata' data-isrequire='$is_require' class='save-data-file $color_require $special_data_class' width='100%' $placeholder name='$data_id'>";
      }//filepdf

      else if($data_type == "logform"){

        $log_qs_txt = ""; $formheight="";
        if(isset($d_itm_prop[$data_id]['logformid'])){
          $log_qs_txt .= "&formid=".$d_itm_prop[$data_id]['logformid'];
        }
        if(isset($d_itm_prop[$data_id]['onlyvisitid'])){
          $log_qs_txt .= "&visitid=$sVisitid";
        }
        if(isset($d_itm_prop[$data_id]['formheight'])){
          $formheight=$d_itm_prop[$data_id]['formheight'];
        }
        $log_qs_txt .= "&projid=$sProjid";

        $sHtml .= "<div id='$data_id' class='q_label bg-mdark1 ptxt-white ptxt-b px-1 py-2 mt-2'>$data_value <button class='btn-import-logdata' data-id='$data_id' data-formid='".$d_itm_prop[$data_id]['logformid']."'>Import Data</button></div>";
        $sHtml .= "<iframe id='iframe_$data_id' class='mb-1' src='ext_index2.php?file=p_form_view_log&uid=".$uid.$log_qs_txt."' style='width:100%;height:".$formheight."px;'></iframe>";

      }
      else if($data_type == "iframe"){
        $pagepath = ""; $formheight="";
        if(isset($d_itm_prop[$data_id]['pagepath'])){
          $pagepath .= $d_itm_prop[$data_id]['pagepath'];
          if(strpos($pagepath,'?') > -1) $pagepath .= '&';
          else $pagepath .= '?';

          $pagepath .= "uid=$uid&collect_date=$collect_date&collect_time=$collect_time&projid=$sProjid&visitid=$sVisitid&formid=$form_id&lang=$sLang";
        }
        if(isset($d_itm_prop[$data_id]['formheight'])){
          $formheight=$d_itm_prop[$data_id]['formheight'];
        }

        $sHtml .= "<div id='$data_id' class='q_label bg-mdark1 ptxt-white ptxt-b px-1 py-2 mt-2'>$data_value</div>";
        $sHtml .= "<iframe class='mb-1' src='$pagepath' style='width:100%;height:".$formheight."px;'></iframe>";
      }
      else if($data_type == "includepage"){
        $pagepath = "";
        if(isset($d_itm_prop[$data_id]['pagepath'])){
          $pagepath .= $d_itm_prop[$data_id]['pagepath'];
          if(strpos($pagepath,'?') > -1) $pagepath .= '&';
          else $pagepath .= '?';

          $pagepath .= "uid=$uid&collect_date=$collect_date&collect_time=$collect_time&projid=$sProjid&visitid=$sVisitid&formid=$form_id&clinic_id=$sClinicid&lang=$sLang";
        }

        if($pagepath != ""){
          $sHtml .= "<div id='$data_id' class='q_label bg-mdark1 ptxt-white ptxt-b px-1 py-2 mt-2'>$data_value </div>
          <div class='div-includepage mb-1' data-pagepath='$pagepath' style='width:100%;'></div>";
        }
      }

      $sHtml .= "<span class='lang th'>$suffix</span><span class='lang en'>$suffix_en</span> ";
      $sHtml .= "</div> ";
    }// data_itm

  }// while
}
else{
  $msg_error .= $stmt->error;
  error_log("p_form_view: ".$msg_error);
}
$stmt->close();



// data rule
$txt_arr_putafter = "";
$txt_arr_showif = "";
$txt_arr_hideif = "";

$query = "SELECT  da.data_id, da.data_parent_id, da.data_parent_value,  dl.data_type, da.action_type
FROM p_form_list_data as PLD, p_form_list_data_action as da
LEFT JOIN p_data_list as dl ON(da.data_id=dl.data_id)
WHERE da.form_id=? AND PLD.form_id=da.form_id AND PLD.data_id=da.data_id
ORDER BY PLD.data_seq
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$form_id);
//echo "$form_id / query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_id, $data_parent_id, $data_parent_value, $data_type, $action_type);
  while ($stmt->fetch()) {
      if($action_type == 'require_if'){
        $txt_arr_showif .= "'".$data_id.":".$data_parent_id.":".$data_parent_value."',";
      }else if($action_type == 'hide_if'){
        $txt_arr_hideif .= "'".$data_id.":".$data_parent_id.":".$data_parent_value."',";
      }else if($action_type == 'put_after'){
        $txt_arr_putafter .= "'".$data_id.":".$data_parent_id.":".$data_parent_value."',";
      }
  }//while
  $txt_arr_showif = ($txt_arr_showif != '')?substr($txt_arr_showif,0,strlen($txt_arr_showif)-1):"";
  $txt_arr_hideif = ($txt_arr_hideif != '')?substr($txt_arr_hideif,0,strlen($txt_arr_hideif)-1):"";
  $txt_arr_putafter = ($txt_arr_putafter != '')?substr($txt_arr_putafter,0,strlen($txt_arr_putafter)-1):"";
//echo "<br>$txt_arr_putafter / $txt_arr_showif / $txt_arr_hideif";
}
$stmt->close();



$div_form_data_import_txt = "";
if($collect_time == '00:00:00'){

  $query = "  SELECT  distinct PDR.collect_time
  FROM p_form_list_data as PLD, p_data_result as PDR
  WHERE PDR.data_id = PLD.data_id AND PDR.collect_time <> '00:00:00' AND
  PDR.uid=? AND PDR.collect_date=? AND PLD.form_id=?
  ORDER BY PDR.collect_time DESC
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('sss',$uid,$collect_date,$form_id);
  if($stmt->execute()){
    $stmt->bind_result($form_collect_time);
    while ($stmt->fetch()) {
        $div_form_data_import_txt .= "<option value='$form_collect_time'>SD: $form_collect_time</option>";
    }//while
  }
  $stmt->close();
  if($div_form_data_import_txt != ""){
    $div_form_data_import_txt = "
      <span class='mr-1 px-1 py-1 ptxt-white bg-mdark2 '>Import Form:
        <select class='sel_import_formdata px-1'>
          $div_form_data_import_txt
        </select><button class='pbtn px-1 bg-mdark1 ptxt-white btn-import-formdata' title='Import Data'><i class='fa fa-download '></i></button>
        <i class='fa fa-spinner spinner' style='display:none;'></i>
      </span>
    ";
  }

}//$collect_time == '00:00:00'

$div_form_lab_data_import_txt = "";
if($sLaborderid !=""){

    $query = "  SELECT LO.collect_time, LO.proj_id, LO.proj_visit
    FROM p_lab_order as LO
    WHERE LO.uid=? AND LO.collect_date=? AND timepoint_id=''
    ORDER BY LO.collect_time desc
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ss',$uid,$collect_date);
    if($stmt->execute()){
      $stmt->bind_result($lab_coltime, $lab_projid, $lab_visitid);
      while ($stmt->fetch()) {
          $div_form_lab_data_import_txt .= "<option value='$lab_coltime'>Proj:$lab_projid $lab_coltime [$lab_visitid]</option>";
      }//while
    }
    $stmt->close();
    if($div_form_lab_data_import_txt != ""){
      $div_form_lab_data_import_txt = "
        <span class='mx-4 px-1 pt-2 pbg-grey ptxt-s10 form-title'>Import Lab:
          <select class='sel_import_labdata px-1'>
            $div_form_lab_data_import_txt
          </select><button class='pbtn ptxt-bg-orange px-1  btn-import-labdata' title='Import Lab Data'><i class='fa fa-download '></i></button>
          <i class='fa fa-spinner spinner' style='display:none;'></i>
        </span>
      ";
    }


}
$mysqli->close();

if($form_protocol_version != ""){
  $form_protocol_version  = "<span class='px-1 ptxt-white bg-mdark2 form-title'>V: $form_protocol_version</span>";
}

$txt_row_main = "
<div class='div-form-view fl-wrap-col bg-msoft2'
  data-lang='$sLang' 
  data-uid='$uid' 
  data-formid='$form_id' 
  data-projid='$sProjid' 
  data-visitid='$sVisitid' 
  data-coldate='$collect_date' 
  data-coltime='$collect_time' 
  data-sid='$s_id' 
  data-visitstatus='$sVisitstatus' 
  data-nextformid='$sNFormId' 
  data-nextformlist='$qsNextForm'>
  <div class='fl-wrap-col fl-auto'>
    <div class='fl-wrap-row fl-fix ph25'>
      <div class='fl-wrap-col fl-fix fl-mid pw50 bg-mdark1 ptxt-white pbtn btn_form_link' >
          <i class='fa fa-link fa-lg' title='link to this form'></i>
      </div>
      <div class='fl-wrap-col fl-fill fl-mid' >
        <span class='px-1 form-title bg-msoft1' ><span class='lang th ptxt-b'>ฟอร์ม: $form_name_th</span><span class='lang en ptxt-b'>Form: $form_name_en</span> $form_protocol_version
        <span class='px-1 ptxt-white bg-sdark1'>$uid</span><span class='px-1 ptxt-white bg-sdark2'> $collect_date $collect_time</span>
        <button class='pbtn pbtn-blue btn-hist-log' style='display:none;'><i class='fa fa-history fa-sm'></i> History Log</button>

        $div_form_data_import_txt $div_form_lab_data_import_txt
      </div>
      <div class='fl-wrap-col fl-fix al-right pw100 ' >
          ภาษา|Lang
      </div>
      <div class='fl-wrap-col fl-fix pw100 ' >
          <select id='sel_form_lang' class='ml-1 ptxt-8' style='width:80px;'><option value='th'>ไทย</option><option value='en'>English</option></select>
      </div>
    </div>
    <div class='fl-fix fl-mid ph20' >
      <span class='px-1 ptxt-white ptxt-s8 form-title'>Form ID: $form_id</span>
    </div>
    <div class='fl-wrap-row fl-mid-left px-2 py-1 ptxt-s10 bg-mdark4 border pis-pdf' style='display:none;'>
      <div class='fl-fix w-270 ph20 v-mid ptxt-b ptxt-s14 pis-pdf-head'>PIS PDF</div>
      <div class='fl-fill ph20 fl-mid-left ptxt-b ptxt-s14'>
        <button class='pbtn pbtn-blue ptxt-s8 btn_open_pdf_pis'>FILE PIS PDF</button>
      </div>
    </div>
    <div class='fl-wrap-row fl-mid-left px-2 py-1 ptxt-s10 bg-mdark4 border imact-consent-pdf' style='display:none;'>
      <div class='fl-fix w-270 ph20 v-mid ptxt-b ptxt-s14 imact-consent-pdf-head'>PIS PDF</div>
      <div class='fl-fill ph20 fl-mid-left ptxt-b ptxt-s14'>
        <button class='pbtn pbtn-blue ptxt-s8 btn_open_pdf_pis_imact'>FILE CONSENT PDF</button>
      </div>
    </div>
    <div class='fl-fill py-4 px-2'>
      $sHtml
    </div>

    <div class='fl-fix h-50 fl-mid div-btn-save'>
      <button class='pbtn pbtn-ok btn-save-data' style='display:none;'>บันทึกข้อมูล | SAVE DATA</button>
      <button class='pbtn pbtn-warning btn-save-data-activate' style='display:none;'>
        ท่านกำลังดูข้อมูลที่จัดเก็บในวันที่ $collect_date หากต้องการแก้ไขให้กดปุ่มนี้ | This data is collected on $collect_date, click here to edit data.
      </button>
      <i class='fas fa-spinner fa-spin spinner' style='display:none;'></i>
    </div>
  </div>
</div>";



?>


<style>
.ptxt{
  font-family: Arial, Helvetica, sans-serif!important;
}

.d-pad{
  padding: 5px 5px;
}

.d-row{
  padding-top:10px;
}
.d-row:hover{
/*	filter:brightness(80%); */
/*  font-weight: bold; */
  color:black;
  background-color: yellow;
}
</style>


<? echo $txt_row_main; ?>


<script>
  
    
$("#hcv_serv_chk").on("change",function(){	
  // alert("Service HCV");	
    if($(this).prop("checked")) {	
      //alert("Service HCV");	
      $("#form_hcv").prop( "disabled", false);	
    }else{	
      $("#form_hcv").prop( "disabled", true);	
    }	
})	
  
$("#form_hcv").on("click",function(){	
    window.open("./HCV/?UID="+$('.div-form-view').attr('data-uid'),"_blank");	
});

//***************************************************

var rowid = 0;
$('.showid').hide();
var currentDate = new Date();
<?
  echo "
  var aShowif = [".$txt_arr_showif."];
  var aHideif = [".$txt_arr_hideif."];
  var aPutafter = [".$txt_arr_putafter."];
  $d_choice_hide

  ";
?>

$(document).ready(function(){
  // PIS PDF
  var check_found_parameter = $("[name=screen_consent]").val();
  var check_found_parameter_clymax = $("[name=screen_consent_clymax]").val();

  if(check_found_parameter !== undefined){
    // Change choice
    $("[name=screen_consent]").off("change");
    $("[name=screen_consent]").on("change", function(){
      var val_condition = $("[name=screen_consent]:checked").val();
      if(val_condition == "Y"){
        $(".pis-pdf").show();
        $(".pis-pdf-head").html("<div class='fl-fill ph20 v-mid ptxt-b ptxt-s14'>PIS PDF มีอายุมากกว่าหรือเท่ากับ 18 ปี</div>");
      }
      else if(val_condition == "N"){
        $(".pis-pdf").show();
        $(".pis-pdf-head").html("<div class='fl-fill ph20 v-mid ptxt-b ptxt-s14'>PIS PDF มีอายุน้อยกว่า 18 ปี</div>");
      }
    });

    // Open PDF PIS
		$(".btn_open_pdf_pis").off("click");
		$(".btn_open_pdf_pis").on("click", function(){
      // OLD <> 18
      var condition_open = $("[name=screen_consent]:checked").val();
      var form_id = $(".div-form-view").attr("data-formid");
      if(form_id != "CLYMAX_CONSENT_V3"){
        if(condition_open == "Y"){
          var gen_url_view = "pdf_pis_project/6_IHRI002_PIS_Client_v3_0_09May22.pdf";
        }
        else{
          var gen_url_view = "pdf_pis_project/5_IHRI002_PIS_Young_client_v3_0_09May22.pdf";
        }
      }

			window.open(gen_url_view,'_blank');
		});

    $("[name=screen_consent]").change();
  }

  if(check_found_parameter_clymax !== undefined){
    $("[name=screen_consent_clymax]").off("change");
    $("[name=screen_consent_clymax]").on("change", function(){
      var val_condition = $("[name=screen_consent_clymax]:checked").val();
      if(val_condition == "Y"){
        $(".pis-pdf").show();
        $(".pis-pdf-head").html("<div class='fl-fill ph20 v-mid ptxt-b ptxt-s14'>PIS PDF มีอายุมากกว่าหรือเท่ากับ 18 ปี</div>");
      }
      else if(val_condition == "N"){
        $(".pis-pdf").show();
        $(".pis-pdf-head").html("<div class='fl-fill ph20 v-mid ptxt-b ptxt-s14'>PIS PDF มีอายุน้อยกว่า 18 ปี</div>");
      }
    });

    $(".btn_open_pdf_pis").off("click");
		$(".btn_open_pdf_pis").on("click", function(){
        // OLD <> 18
      var condition_open = $("[name=screen_consent_clymax]:checked").val();
      var form_id = $(".div-form-view").attr("data-formid");

      if(form_id == "CLYMAX_CONSENT_V3"){
        if(condition_open == "Y"){
          var gen_url_view = "pdf_pis_project/IHRI021_PIS_Young_MSM_v_3_0_23Jan2023.pdf";
        }
        else{
          var gen_url_view = "pdf_pis_project/IHRI021_PIS_Young_MSM_age_below_18_v_3_0_2023.pdf";
        }

        window.open(gen_url_view,'_blank');
      }
    });
      
    $("[name=screen_cscreen_consent_clymaxonsent]").change();
  }

  // Consent Online IMACT view
  var check_imact_consent = $("[name=consent_name_1st]").val();
  var form_id = $(".div-form-view").attr("data-formid");
  if(form_id == "IMACT_CONSENT_V3_2"){
    if(check_imact_consent !== undefined){
      $(".imact-consent-pdf").show();
      $(".imact-consent-pdf-head").html("<div class='fl-fill ph20 v-mid ptxt-b ptxt-s14'>PIS PDF เอกสารชี้แจงข้อมูล</div>");

      // Open PDF PIS
      $(".btn_open_pdf_pis_imact").off("click");
      $(".btn_open_pdf_pis_imact").on("click", function(){
        var gen_url_view = "pdf_pis_project/IHRI004_PIS_V3_2_230123.pdf";
        window.open(gen_url_view,'_blank');
      });
    }
  }

  var check_clymax_consent = $("[name=consent_name_cm_1st]").val();
  if(form_id == "CLYMAX_CONSENT_V3_G2"){
    if(check_clymax_consent !== undefined){
      $(".imact-consent-pdf").show();
      $(".imact-consent-pdf-head").html("<div class='fl-fill ph20 v-mid ptxt-b ptxt-s14'>PIS เอกสารชี้แจงข้อมูลสมาชิกครอบครัว</div>");

      // Open PDF PIS
      $(".btn_open_pdf_pis_imact").off("click");
      $(".btn_open_pdf_pis_imact").on("click", function(){
        var gen_url_view = "pdf_pis_project/IHRI021_PIS_Family_relationship_v_3_0_23Jan23.pdf";
        window.open(gen_url_view,'_blank');
      });
    }
  }

  else if(form_id == "CLYMAX_CONSENT_V3_G3"){
    if(check_clymax_consent !== undefined){
      $(".imact-consent-pdf").show();
      $(".imact-consent-pdf-head").html("<div class='fl-fill ph20 v-mid ptxt-b ptxt-s14'>PIS เอกสารชี้แจงข้อมูลเจ้าหน้าที่</div>");

      // Open PDF PIS
      $(".btn_open_pdf_pis_imact").off("click");
      $(".btn_open_pdf_pis_imact").on("click", function(){
        var gen_url_view = "pdf_pis_project/IHRI021_PIS_Clinic_staff_v_3_0_23Jan2023.pdf";
        window.open(gen_url_view,'_blank');
      });
    }
  }

  // initialize data ////////
  <? echo $saveButton_JS; ?>
  
  // reload tab
  // let sUID = $('.div-form-view').attr('data-uid');
  // let sColdate = $('.div-form-view').attr('data-coldate');
  // let sColtime = $('.sel_import_labdata').val();
  // var url_load_tap = "qa_inc_pribta_main.php?q=&uid="+sUID+"&coldate="+sColdate+"&coltime="+sColtime;
  // $("#qa_data_defult").load(url_load_tap);

  $(".div-form-view .div-includepage").each(function(ix,objx){
    $(objx).load($(objx).attr('data-pagepath'));
  });

  if($(".div-form-view").attr("data-lang") != ''){
    $("#sel_form_lang").val($(".div-form-view").attr("data-lang"));
  }
  else $("#sel_form_lang").val('th');


    $(".div-form-view #sel_form_lang").off("change");
    $(".div-form-view #sel_form_lang").on("change",function(){

      let lang = $(this).val();
      $('.div-form-view .lang').hide();
      $('.div-form-view .'+lang).show();

      //dropdownlist change language
      $( ".div-form-view .ddl").each(function() {
          $(this).text($(this).attr('data-val'+lang));
      });


    });

    $("#sel_form_lang").change();
  

  // btn_form_link
  $(".div-form-view .btn_form_link").off("click");
  $(".div-form-view .btn_form_link").on("click",function(){

    let sUID = $('.div-form-view').attr('data-uid');
    let sFormid = $('.div-form-view').attr('data-formid');
    let sProjid = $('.div-form-view').attr('data-projid');
    let sVisitid = $('.div-form-view').attr('data-visitid');
    let sColdate = $('.div-form-view').attr('data-coldate');
    let sColtime = $('.div-form-view').attr('data-coltime');

    let sUrl = "p_form_view_link_qrcode.php?uid="+sUID+"&coldate="+sColdate+"&coltime="+sColtime+"&formid="+sFormid+"&projid="+sProjid+"&visitid="+sVisitid;
    showDialog(sUrl,"Form Link | Form ID: "+sFormid+ " [UID:"+sUID+" | Date:"+sColdate+" "+sColtime+"]","90%","99%","",function(sResult){
        if(sResult != ""){
        }
    },false,function(){
    });
  });

  // history log
  $(".div-form-view .btn-hist-log").off("click");
  $(".div-form-view .btn-hist-log").on("click",function(){
    let sUID = $('.div-form-view').attr('data-uid');
    let sFormid = $('.div-form-view').attr('data-formid');
    let sColdate = $('.div-form-view').attr('data-coldate');
    let sColtime = $('.div-form-view').attr('data-coltime');

    let sUrl = "p_form_history_log.php?uid="+sUID+"&coldate="+sColdate+"&coltime="+sColtime+"&formid="+sFormid;
    showDialog(sUrl,"Data History | Form ID: "+sFormid+ " [UID:"+sUID+" | Date:"+sColdate+" "+sColtime+"]","90%","99%","",function(sResult){
  			if(sResult != ""){
  			}
  	},false,function(){
  	});
  });



  $(".div-form-view .btn-import-formdata").off("click");
  $(".div-form-view .btn-import-formdata").on("click",function(){
    btnclick = $(this);

    let sUID = $('.div-form-view').attr('data-uid');
    let sFormid = $('.div-form-view').attr('data-formid');
    let sColdate = $('.div-form-view').attr('data-coldate');
    let sColtime = $('.sel-import-formdata').attr('data-coltime');
    let sProjid = $('.div-form-view').attr('data-projid');

    var aData = {
        u_mode:"get_import_formdata",
        uid:sUID,
        collect_date:sColdate,
        collect_time:sColtime,
        formid:sFormid,
        projid:sProjid
        };

    startLoad(btnclick, btnclick.next(".spinner"));
    callAjax("p_form_view_a.php",aData,function(rtnObj,aData){
        endLoad(btnclick, btnclick.next(".spinner"));
        if(rtnObj.res == 1){
          $.notify("Import Data","success");
          for (const [key, value] of Object.entries(rtnObj.datalist)) {
            /*
           console.log("key2: "+key);
           console.log("key3: "+$(".save-data[name='"+key+"']").attr('name'));
*/
           var dataObj = $(".save-data[name='"+key+"']") ;
           if (typeof $(".save-data[name='"+key+"']").attr('name') === "undefined") {
               dataObj = $(".save-data[data-id='"+key+"']");
           }

           let data_val = getWDataCompValue(dataObj);
          // let data_val = getWDataCompValue($(".save-data[name='"+key+"']"));

        //    console.log(key+": "+value+"/"+data_val+"/"+dataObj.attr('data-odata'));

            if(data_val == ''){
              if(dataObj.prop("tagName")){
                if(dataObj.prop("tagName").toUpperCase() == "INPUT"){
                  let data_type = dataObj.attr("type").toUpperCase();

                  if(data_type=="RADIO"){
                    $(".save-data[name='"+key+"'][value='"+value+"']").prop("checked",true);
                  }
                  else if(data_type=="CHECKBOX"){
                    dataObj.prop("checked",true);
                  }
                  else{
                    dataObj.val(value);
                  }
                }

                else{ // txt area etc.
                 dataObj.val(value);
                }
              }
              $('.data-item[id="'+key+'"]').addClass('bg-ssoft2');
              //console.log('importdata: '+key);
            }// odata = ''

          } // for
          checkRequireIf();
          checkHideIf();
        }
        else{
          $.notify("Fail to import data", "error");
        }
    });// call ajax

  });


    $(".div-form-view .btn-import-labdata").off("click");
    $(".div-form-view .btn-import-labdata").on("click",function(){

      btnclick = $(this);

      let sUID = $('.div-form-view').attr('data-uid');
      let sFormid = $('.div-form-view').attr('data-formid');
      let sProjid = $('.div-form-view').attr('data-projid');
      let sVisitid = $('.div-form-view').attr('data-visitid');
      let sColdate = $('.div-form-view').attr('data-coldate');
      let sColtime = $('.sel_import_labdata').val();

      var aData = {
          u_mode:"get_import_labdata",
          uid:sUID,
          collect_date:sColdate,
          collect_time:sColtime,
          projid:sProjid
          };

      startLoad(btnclick, btnclick.next(".spinner"));
      callAjax("p_form_view_a.php",aData,function(rtnObj,aData){
          endLoad(btnclick, btnclick.next(".spinner"));
          if(rtnObj.res == 1){
            $.notify("Import Lab Data","success");
            for (const [key, value] of Object.entries(rtnObj.datalist)) {

/*
             console.log("key2: "+key);
             console.log("key3: "+$(".save-data[name='"+key+"']").attr('name'));
*/
             var dataObj = $(".save-data[name='"+key+"']") ;
             if (typeof $(".save-data[name='"+key+"']").attr('name') === "undefined") {
                 dataObj = $(".save-data[data-id='"+key+"']");
             }

             if (typeof $(".save-data[name='"+key+"_test']").attr('name') !== "undefined") {
                // dataObj = $(".save-data[name='"+key+"_test']");
                 $(".save-data[name='"+key+"_test'][value='Y']").prop("checked",true);
             }


             if(typeof dataObj.attr('name') === "undefined"){ }
             else{
               let data_val = getWDataCompValue(dataObj);
              // let data_val = getWDataCompValue($(".save-data[name='"+key+"']"));
            //    console.log(key+": "+value+"/"+data_val+"/"+dataObj.attr('data-odata'));
                if(data_val == ''){
                  if(dataObj.prop("tagName")){
                    if(dataObj.prop("tagName").toUpperCase() == "INPUT"){
                      let data_type = dataObj.attr("type").toUpperCase();

                      if(data_type=="RADIO"){
                        $(".save-data[name='"+key+"'][value='"+value+"']").prop("checked",true);
                      }
                      else if(data_type=="CHECKBOX"){
                        dataObj.prop("checked",true);
                      }
                      else{
                        dataObj.val(value);
                      }
                    }

                    else{ // txt area etc.
                     dataObj.val(value);
                    }
                  }
                  $('.data-item[id="'+key+'"]').addClass('bg-ssoft2');
                  //console.log('importdata: '+key);
                }// odata = ''
             }

            } // for
            checkRequireIf();
            checkHideIf();
          }
          else{
            if(rtnObj.msg_error != '') $.notify(rtnObj.msg_error, "error");
            $.notify("Fail to import data", "error");
          }
      });// call ajax
  });


    $(".div-form-view .btn-import-logdata").off("click");
    $(".div-form-view .btn-import-logdata").on("click",function(){

      btnclick = $(this);

      let sUID = $('.div-form-view').attr('data-uid');
      let sProjid = $('.div-form-view').attr('data-projid');
      let sVisitid = $('.div-form-view').attr('data-visitid');
      let sColdate = $('.div-form-view').attr('data-coldate');

      let sFormid = $(this).attr('data-formid');
      let sDataid = $(this).attr('data-id');
      var aData = {
          u_mode:"get_import_labdata_logform",
          uid:sUID,
          collect_date:sColdate,
          visitid:sVisitid,
          projid:sProjid,
          formid:sFormid
          };

      startLoad(btnclick, btnclick.next(".spinner"));
      callAjax("p_form_view_a.php",aData,function(rtnObj,aData){
          endLoad(btnclick, btnclick.next(".spinner"));
          if(rtnObj.res == 1){
             $('#iframe_'+sDataid).attr('src', $('#iframe_'+sDataid).attr('src'));
             $.notify("Import Data", "info");
          }
          else{
            if(rtnObj.msg_error != '') $.notify(rtnObj.msg_error, "error");
            $.notify("Fail to import data", "error");

          }
      });// call ajax
    });



  // change to th date
  $(".div-form-view .v-date-th").each(function(ix,objx){
    $(objx).val(changeEn2ThDate($(objx).val()));
  });

  $.each(aPutafter,function(ix,objx){
    let sObj = objx.split(":");
    //  console.log(ix+" - "+sObj[0]+":"+sObj[1]+":"+sObj[2]); // data_id:data_parent_id:data_parent_value
    if(sObj[2]==""){ // parent_value
    }else{
      $("input[data-id='"+sObj[1]+"'][value='"+sObj[2]+"']").parent().append($(".data-item[id='"+sObj[0]+"']"));
    }
  });

  checkRequireIf();
  checkHideIf();

  let sFormid = $('.div-form-view').attr('data-formid');
  if(sFormid == "MENTAL_HEALTH_PHQ9"){
    $("[type=radio]").each(function(){
      if($(this).attr("data-id").indexOf("depression_8_") >= 0){
          $(this).attr("class", "save-data v-radio sum-score");
      }
    });
  }

  if(sFormid == "SD_ASSESSMENT_P"){
    $("[type=radio]").each(function(){
      if($(this).attr("data-id").indexOf("stigma_") >= 0){
          $(this).attr("class", "save-data v-radio sum-score");
      }
    });
  }
  //*************************



  $(".div-form-view").on("click",".v-checkbox",function(){
    checkRequireIf();
    checkHideIf();
  });


  $(".div-form-view").on("change",".v-radio",function(){
    //console.log("click "+$(this).attr('name')+"/"+$(this).val());

    // Sum value auto: MENTAL_HEALTH_PHQ2_PHQ9
    let sFormid = $('.div-form-view').attr('data-formid');
    if(sFormid == "MENTAL_HEALTH_PHQ2_PHQ9"){
      sumscoreauto(this);
    }
    if(sFormid == "MENTAL_HEALTH_PHQ9"){
      sumscoreauto_form2(this);
    }
    if(sFormid == "SD_ASSESSMENT_P"){
      sumscoreauto_form3(this);
    }

    checkRequireIf();
    checkHideIf();
  });

  $(".div-form-view").on("dblclick",".v-radio",function(){
    //console.log('dblclick: '+$(this).attr("name"));
    if($(this).is(':checked')){
      let name = $(this).attr("name");
      $('INPUT[name="'+name+'"]').prop('checked', false);
      checkRequireIf();
      checkHideIf();
    }
  });

  $(".div-form-view").on("change",".v-dropdown",function(){
    checkRequireIf();
    checkHideIf();
  });



  $(".div-form-view .btn-save-data").off("click");
  $(".div-form-view .btn-save-data").on("click",function(){
    saveFormData($(this));
  });

  $(".div-form-view .save-data-file").off("change");
  $(".div-form-view .save-data-file").on("change",function(){
    //console.log("file type "+$(this).attr('data-filetype'));
     if(validateFileType($(this))){
     }
     else{
       $(this).notify('ชนิดไฟล์หรือขนาดไฟล์ไม่ถูกต้อง | File type OR File size is invalid' ,'error');
     }
  });



  $(".v-date").mask("9999-99-99",{placeholder:"yyyy-mm-dd"});

  $('.div-form-view').on('click', '.v-date', function(){
    if($(this).hasClass('hasDatepicker')){
    }
    else{
      let sObj = $(this).datepicker({
        dateFormat:"yy-mm-dd",
        changeYear:true,
        changeMonth:true
      });
      $(sObj).datepicker("show");
    }
  });
  $('.div-form-view').on('focusout', '.v-date', function(){
    if($(this).val().trim() != 'yyyy-mm-dd'){
      if($(this).hasClass('v-date-partial')){
        checkDateEnComp($(this), true);
      }
      else{
        checkDateEnComp($(this), false);
      }
      /*
      if(!validateDate($(this).val())){
        if($(this).hasClass('v-date-partial')){
        }
        else{
          $(this).notify("วันที่ไม่ถูกต้อง | Invalid Date "+$(this).val(), "error");
        }
      }
      */

    }
  });


  $(".v-date-th").mask("99/99/9999",{placeholder:"dd/mm/yyyy"});
/*
  $('.div-form-view').on('click', '.v-date-th', function(){
    if($(this).hasClass('hasDatepicker')){
    }
    else{
      $.datepicker.setDefaults( $.datepicker.regional[ "th" ] );
      var currentDate = new Date();
      if($(this).val() != '' && $(this).val() != 'dd/mm/yyyy') currentDate = getJS_ThDate($(this).val());
      currentDate.setYear(currentDate.getFullYear() + 543);
      $(this).datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: '+473:+544',
        dateFormat: 'dd/mm/yy',
        onSelect: function(date) {
          $(this).addClass('filled');
        }
      });
      $(this).datepicker("setDate",currentDate );
      $(this).datepicker("show");

    }//else
  });
  */

  $('.div-form-view').on('focusout', '.v-date-th', function(){
    if($(this).val().trim() != 'dd/mm/yyyy'){
      if($(this).hasClass('v-date-partial')){
        checkDateThComp($(this), true);
      }
      else{
        checkDateThComp($(this), false);
      }

      /*
      if(!validateDateTH($(this).val())){
        if($(this).hasClass('v-date-partial')){
        }
        else{
          $(this).notify("วันที่ไม่ถูกต้อง (พ.ศ.)| Invalid Thai Date "+$(this).val(), "error");
        }
      }
      */


    }
  });


  $('.div-form-view .v-number').off('keydown');
  $('.div-form-view .v-number').on('keydown', function() {

          if ((event.keyCode >= 48 && event.keyCode <= 57) ||
          (event.keyCode >= 96 && event.keyCode <= 105) ||
          event.keyCode == 8 || event.keyCode == 9 ||
          event.keyCode == 37 || event.keyCode == 39 ||
          event.keyCode == 46 || event.keyCode == 110 ||
          event.keyCode == 107 || event.keyCode == 109 ||
          event.keyCode == 173 || event.keyCode == 61 ||
          event.keyCode == 188 ||event.keyCode == 190 ||
          ((event.keyCode == 65 || event.keyCode == 86 || event.keyCode == 67) && (event.ctrlKey === true || event.metaKey === true))
          ) {
          } else {
              event.preventDefault();
          }

  });

  $('.div-form-view .v-number').off('focusout');
  $('.div-form-view .v-number').on('focusout', function() {
    if($(this).val() == ''){
      $(this).notify('กรุณากรอกตัวเลข | Please insert number.');
      $(this).val('');
    }
  });




}); //End DOcument ready



function checkRequireIf(){ // hide data and clear value in hidden input
  aShowif.forEach(function(objx) {
    let sObj = objx.split(":");
    if (sObj[0].indexOf("depression_8_") >= 0){
      $("[name="+sObj[0]+"]").attr("class", "save-data v-radio sum-score");
    }
    //console.log("enter: "+sObj[1]+"/"+$(".save-data[data-id='"+sObj[1]+"']").prop("tagName"));
    //  console.log(" - "+sObj[0]+":"+sObj[1]+":"+sObj[2]+" | cur val: "+cur_parent_value); // data_id:data_parent_id:data_parent_value

    let cur_parent_value = getWDataCompValue($(".save-data[data-id='"+sObj[1]+"']"));
  //  console.log(" - "+sObj[0]+":"+sObj[1]+":"+sObj[2]+" | cur val: "+cur_parent_value); // data_id:data_parent_id:data_parent_value
    if(cur_parent_value == sObj[2]){ //trigger rule  , show data child
      //console.log(" showdatachild "+sObj[0]+"/"+sObj[1]+"/"+sObj[2]);

      $("#"+sObj[0]).show();


    }else{ // hide and clear data child
    //  console.log(" hide "+sObj[0]+":"+sObj[1]+":"+sObj[2]+" | cur val: "+cur_parent_value);
      $("#"+sObj[0]).hide();
      if($("#"+sObj[0]).hasClass('data-item')){
      //  console.log(" hide "+sObj[0]+":"+sObj[1]+":"+sObj[2]+" | cur val: "+cur_parent_value);
        if($(".save-data[data-id='"+sObj[0]+"']").prop("tagName")){
          if($(".save-data[data-id='"+sObj[0]+"']").prop("tagName").toUpperCase() == "INPUT"){
            let data_type = $(".save-data[data-id='"+sObj[0]+"']").attr("type").toUpperCase();
            if(data_type=="RADIO" || data_type=="CHECKBOX"){
              $(".save-data[data-id='"+sObj[0]+"']").prop("checked",false);
            }else{
              $(".save-data[data-id='"+sObj[0]+"']").val("");
            }
          }

          else{ // txt area etc.
            $(".save-data[data-id='"+sObj[0]+"']").val("");
          }
        }
        else if($(".save-data-file[data-id='"+sObj[0]+"']").prop("tagName")){
          //console.log('data-save-file : '+sObj[0]+" / "+$(".save-data-file[data-id='"+sObj[0]+"']").prop("tagName"));
        }


      }
    }//else
  });

}//checkRequireIf

function checkHideIf(){ // hide data but not clear value in hidden input
  aHideif.forEach(function(objx) {
    let sObj = objx.split(":");
    //console.log(" - "+sObj[0]+":"+sObj[1]+":"+sObj[2]); // data_id:data_parent_id:data_parent_value
    let cur_parent_value = getWDataCompValue($(".save-data[data-id='"+sObj[1]+"']"));
    //console.log(" 99999 "+cur_parent_value+"/"+sObj[2]); // data_id:data_parent_id:data_parent_value
    if(cur_parent_value == sObj[2]){ //trigger rule  , show data child
      $("#"+sObj[0]).show();
    }else{ // hide and clear data child
    //  console.log("hide : "+sObj[0]);
      $("#"+sObj[0]).hide();
      /*
      if($("#"+sObj[0]).hasClass('data-item')){
        let data_type = $(".save-data[name='"+sObj[0]+"']").attr("type").toUpperCase();
        if(data_type=="RADIO" || data_type=="CHECKBOX"){
          $(".save-data[name='"+sObj[0]+"']").prop("checked",false);
        }else{
          $(".save-data[name='"+sObj[0]+"']").val("");
        }
      }
      */
    }//else
  });

}//checkHideIf




function saveFormData(btnsave){
  //console.log("enter save data");
  // remove all warning component
  $(".div-form-view .bg-warning").each(function(ix,objx){
     $(objx).removeClass('bg-warning');
  });

  let sUID = $('.div-form-view').attr('data-uid');
  let sFormid = $('.div-form-view').attr('data-formid');
  let sColdate = $('.div-form-view').attr('data-coldate');
  let sColtime = $('.div-form-view').attr('data-coltime');
  let sSid = $('.div-form-view').attr('data-sid');
  let sProjid = $('.div-form-view').attr('data-projid');
  let flag_valid = 1;
  let flag_require = 0;
  let flag_update_file_upload = 0;
  let error_msg = [];


  let flag_form_done = 1; // 1:complete formdone, 2:incomplete formdone
  //console.log("savedata: "+sUID+"/"+sFormid+"/"+sColdate+"/"+sColtime+"/"+sSid);
   let lst_data_obj = [];

   let lst_file_name = [];
   let lst_file_obj = [];

   let compErr;
   let scrollto = 0;



      $(".div-form-view .save-data-file").each(function(ix,objx){
         if($(objx).attr('data-isrequire') == '1' && $(objx).is(':visible')){
           if($(objx).attr('data-odata') != 'Y' && $(objx).val() == ''){
            //  console.log("filename req incomplete: "+$(objx).attr('data-id'));
              compErr = $(objx);
              $(objx).parent().addClass('bg-warning');
              alert("File upload required: "+$(objx).attr('data-id'));
              error_msg.push('File upload required!');
              flag_require = 1;
              flag_form_done = 2;
              return false;
           }
    //console.log("filename req: "+$(objx).attr('data-id'));
         }

         if($(objx).val() != ''){
      //     console.log("filename wait upload: "+$(objx).val().split('\\').pop());
           flag_update_file_upload = 1;
           if($(objx).hasClass('uploadpending')){
           }
           else{
             $(objx).addClass('uploadpending');
           }
         }
    //console.log("filename: "+$(objx).val().split('\\').pop());
      }); //each save-data-file

      if(flag_require == 1){ // require data in missing data component
          compErr.notify('กรุณาแนบไฟล์ที่เกี่ยวข้อง | Please attach related file.', 'info');
          $("body,html").animate(
           {scrollTop: compErr.offset().top-30},1000 //speed
           );
      //  return;
      }



   $(".div-form-view .save-data").each(function(ix,objx){
     let sVal = getWDataCompValue(objx);
     let sOData = getWODataComp(objx);
     objAlert=undefined;
    // console.log("dataid: "+$(objx).attr('data-id')+" / "+$(objx).prop("type").toLowerCase()+"/"+$(objx).attr('data-isrequire')+"/"+$(objx).is(':visible'));
     if($(objx).attr('data-isrequire') == '1' && $(objx).is(':visible')){
      //   console.log("dataid require: "+$(objx).attr('data-id')+" / val: "+sVal);
       if($(objx).prop("type").toLowerCase()=="checkbox"){
         let q_label_name = $(objx).attr("name");
         if($('input[name="'+q_label_name+'"]:checked').length){

         }else objAlert = objx;

       }else if(sVal=='') objAlert = objx;

     }//isrequire

     if(flag_form_done != 2) {
       if(objAlert!=undefined){
          $(objAlert).focus();
        //  $(objAlert).();
          $(objAlert).parent().notify('กรุณากรอกข้อมูล | Please insert data', 'info');
          $(objx).parent().addClass('bg-warning');
          alert("Data Required: "+$(objAlert).attr('data-id'));
          error_msg.push("Data Required: "+$(objAlert).attr('data-id'));
          flag_form_done = 2 ;
       }
     }



      //console.log("enter here 01 "+$(objx).attr('data-id'));
     if(sVal != sOData){ //check data changed
      // console.log("dataid: "+$(objx).attr('data-id')+"/"+sVal+"::"+sOData);
       if(!checkValidData(objx)){
         //console.log("invalid dataid: "+$(objx).attr('data-id')+"/"+sVal+"::"+sOData);
         $.notify("ข้อมูลไม่ถูกต้อง | Invalid Data. (data id: "+$(objx).attr('data-id')+" | value: "+sVal+")","error");

         $(objx).focus();
         $(objx).notify("ข้อมูลไม่ถูกต้องกรุณาตรวจสอบ | Invalid data! Please check.","warn");
         $(objx).parent().addClass('bg-warning');
         error_msg.push("ข้อมูลไม่ถูกต้อง | Invalid Data. (data id: "+$(objx).attr('data-id')+" | value: "+sVal+")");
         flag_valid = 0;
         compErr=$(objx);
         return false;
       }
       else{
         let data_item = {};
         data_item[$(objx).attr("data-id")] = sVal;
         $(objx).addClass("data-update");
         lst_data_obj.push(data_item);
         flag_update = true;
       }
     }//check data changed


   }); //each .save-data

   if(flag_require == 1){ // require data in missing data component
      // alert("Data required | ข้อมูลไม่ครบ");
      flag_form_done = 2;
      //  return;
   }



   if(flag_valid == 0){
      $.notify("ข้อมูลไม่ถูกต้อง | Invalid Data.","error");
      return;
   }


   if(error_msg.length > 0){
     //let err_html = "<div>";
     for (const element of error_msg) {
       //console.log("error: "+element);
       $.notify('Warning: '+element, 'warning');
      // err_html += "<div>"+element+"</div>";
     }
     //err_html += "</div>";
     //showPopup(err_html,"Warning",'500','500','0');
     flag_form_done = 2;
   }


   <?
     echo $fnc_save_formdone;
   ?>


     if(lst_data_obj.length == 0 && flag_update_file_upload == 0){

       let sNextForm = $('.div-form-view').attr('data-nextformid');
       if(sNextForm != ''){
         if(flag_form_done == 2){}
         else{
           checkNextForm();
         }

       }
       else{
         $.notify("ข้อมูลไม่เปลี่ยนแปลง | No data changed.","info");
       }

       //return;
     }
     else if(lst_data_obj.length > 0){
       //console.log('flag: '+flag_valid+'/'+flag_require);
          var aData = {
            u_mode:"form_data_update",
            uid:sUID,
            collect_date:sColdate,
            collect_time:sColtime,
            lst_data:lst_data_obj,
            s_id:sSid,
            formid:sFormid,
            form_done:flag_form_done,
            projid:sProjid
          };

           startLoad(btnsave, btnsave.next(".spinner"));
           callAjax("p_form_view_a.php",aData,function(rtnObj,aData){
               endLoad(btnsave, btnsave.next(".spinner"));
               if(rtnObj.res == 1){
                 $.notify("DATA SAVED","success");

                 $('.div-form-view .btn-save-data-activate').show();
                 $('.data-item').removeClass('bg-ssoft2');
                 btnsave.hide();

                 $(".data-update").each(function(ix,objx){
                   let sVal = getWDataCompValue(objx);
                   setWODataComp(objx, sVal);
                  // console.log("set odata: "+$(objx).attr("name"));
                 });
                 $(".save-data").removeClass("data-update");
                 if(flag_update_file_upload == 1){
                   saveFileUpload(sUID, sColdate, sColtime, sSid, sProjid);
                 }
                 else{
                   //console.log("aftersave");
                   checkNextForm();
                 }
               }
               else{
                 $.notify("Fail to save data", "error");
               }
           });// call ajax
          // console.log("enterxxx");
     }
     else if(flag_update_file_upload == 1){
       saveFileUpload(sUID, sColdate, sColtime, sSid, sProjid);
     }

} // saveFormData


function saveFileUpload(sUID,sColdate,sColtime, sProjid){
  let flag_found = 0;
  let flag_valid_file = true;
  let dataid = '';
  let fileupload;
  let filetype = 'fileimage';
  let compFile;

  $('.div-form-view .uploadpending').each(function(ix,objx){
        flag_found = 1;
        compFile = $(objx);
        fileupload = $(objx)[0].files[0];
        dataid = $(objx).attr('data-id');
        filetype = $(objx).attr('data-filetype');

        flag_valid_file = validateFileType($(objx));
        //console.log("file: "+dataid+'/'+filetype);
      //  console.log("filexxx: "+$(objx).attr('data-id')+'/'+$(objx).val()+"::"+$(objx).attr('class'));
        return false; //exit loop
  }); // each

  if(flag_found == 1){

    if(flag_valid_file){
      var fd = new FormData();

      fd.append('u_mode', 'file_data_update');
      fd.append('uid', sUID);
      fd.append('collect_date', sColdate);
      fd.append('collect_time', sColtime);
      fd.append('dataid', dataid);
      fd.append('file', fileupload);
      fd.append('filetype', filetype);
      fd.append('projid', sProjid);

      // startLoad(btnsave, btnsave.next(".spinner"));
       callAjaxForm("p_form_file_a.php",fd,function(rtnObj,aData){
      //     endLoad(btnsave, btnsave.next(".spinner"));
           if(rtnObj.res == 1){
             $.notify("File upload ["+dataid+"]","success");

             compFile.removeClass("uploadpending");
             compFile.val('');
             showfileobj = "";
             if(filetype == 'fileimage')
              showfileobj = '<img src="'+rtnObj.filename+'?t='+new Date().getTime()+'" style="max-width:150px;">';
            //  $('#'+filetype+dataid).html('<a href="'+rtnObj.filename+'?t='+new Date().getTime()+'" target="_blank"><img src="'+rtnObj.filename+'?t='+new Date().getTime()+'" style="max-width:150px;"></a>');
             else if(filetype == 'filepdf')
              showfileobj = '<i class="fa fa-file-pdf fa-lg"></i> View PDF:'+rtnObj.filename;

             if(showfileobj != '')
             $('#'+filetype+dataid).html('<a href="'+rtnObj.filename+'?t='+new Date().getTime()+'" target="_blank">'+showfileobj+'</a>');

             saveFileUpload(sUID,sColdate,sColtime);
           }
           else{
             $.notify("Fail to save data", "error");
             $.notify("Error: "+rtnObj.msg_error, "error");
             $("body,html").animate(
              {scrollTop: compFile.offset().top-30},500 //speed
              );
              compFile.notify(rtnObj.msg_error, 'error');
           }
       });// call ajax
    }
    else{
      alert("File invalid | ไฟล์ไม่ถูกต้อง");
    }

  }// found uploadpending
  else{
    console.log("enter03");
    checkNextForm();
  }

}



function checkNextForm(){
  //Jeng's Code
  //  var sFormList = '<? echo($qsNextForm); ?>';
  //  var sNextForm = '<? echo($sNFormId); ?>';
  console.log("checknext");

  let sFormList = $('.div-form-view').attr('data-nextformlist');
  let sNextForm = $('.div-form-view').attr('data-nextformid');

  let sUID = $('.div-form-view').attr('data-uid');
  let sColdate = $('.div-form-view').attr('data-coldate');
  let sColtime = $('.div-form-view').attr('data-coltime');
  let sSid = $('.div-form-view').attr('data-sid');

  if(sNextForm!=""){
  //  ext_index2.php?file=p_form_view&form_id=DEMO_PRIBTA&lang=th&uid=P21-00586&collect_date=2021-07-15&collect_time=09:42:27&next_form_id=BRA_ASSIST_PRIBTA,THANKS_CLINIC
   let sUrl="ext_index.php?file=p_form_view&lang=th&form_id="+sNextForm+"&uid="+sUID+"&coldate="+sColdate+"&coltime="+sColtime+"&next_form_id="+sFormList+"&s_id="+sSid;
   //$("#div_form_view_data").parent().load(sUrl);
   window.location.href=sUrl;
    return;
  }


  //JENG
}

function sumscoreauto(obj){
  var check_radio = $(obj).attr("data-id");
  if(check_radio.indexOf("depression_8_") >= 0){
    var total_score = 0;
    var count_all_elecment = 0;
    var count_check_val = 0;

    $(".div-form-view .sum-score").each(function(){
      count_all_elecment++;
    });

    $(".div-form-view .sum-score").filter(":checked").each(function(){
        total_score += parseInt($(this).val());
        count_check_val++;
    });

    //console.log(count_all_elecment/4+"/"+count_check_val);

    if(parseInt(count_all_elecment/4) == parseInt(count_check_val)){
      $(".div-form-view [name=total_score]").val(total_score);
    }
  }
}

function sumscoreauto_form2(obj){
  var check_radio = $(obj).attr("data-id");
  if(check_radio.indexOf("depression_8_") >= 0 && check_radio.indexOf("depression_8_10") < 0){
    var total_score = 0;
    var count_all_elecment = 0;
    var count_check_val = 0;

    $(".div-form-view .sum-score").each(function(){
      if($(this).attr("data-id") != "depression_8_10")
        count_all_elecment++;
    });

    $(".div-form-view .sum-score").filter(":checked").each(function(){
      if($(this).attr("data-id") != "depression_8_10"){
        total_score += parseInt($(this).val());
        count_check_val++;
      }
    });

    // console.log(count_all_elecment/4+"/"+count_check_val+":"+check_radio.indexOf("depression_8_10"));

    if(parseInt(count_all_elecment/4) == parseInt(count_check_val)){
      $(".div-form-view [name=total_score]").val(total_score);
    }
  }
}

function sumscoreauto_form3(obj){
  var check_radio = $(obj).attr("data-id");
  if(check_radio.indexOf("stigma_") >= 0){
    var total_score = 0;
    var count_all_elecment = 0;
    var count_check_val = 0;

    $(".div-form-view .sum-score").each(function(){
      count_all_elecment++;
    });

    $(".div-form-view .sum-score").filter(":checked").each(function(){
      total_score += parseInt($(this).val());
      count_check_val++;
    });

    console.log(count_all_elecment/4+"/"+count_check_val);

    if(parseInt(count_all_elecment/4) == parseInt(count_check_val)){
      $(".div-form-view [name=stigma_total_score]").val(total_score);
    }
  }
}

</script>
