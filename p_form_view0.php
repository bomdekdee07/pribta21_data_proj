<?

include_once("in_db_conn.php");
include_once("in_php_function.php");
include_once("in_php_pop99.php");


$uid = getQS("uid");
$collect_date = getQS("coldate");
$collect_time = getQS("coltime");
$form_id = getQS("form_id");
$visit_id = getQS("visit_id");
//$s_id = getQS("s_id");
$sLang = getQS("lang");

$sProjid = getQS("projid");
$allow_data = 0;
$fnc_save_formdone = "";


// check permission
include_once("in_session.php");
if(isset($_SESSION["s_id"])){
  $allow_view = 1;
  $sID = $_SESSION["s_id"];
  $query ="SELECT * FROM p_staff_auth
  WHERE s_id =? AND proj_id=? ";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ss',$sID, $sProjid);
//echo "$sID, $sProjid / $query";
  if($stmt->execute()){
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()) {
      $allow_data = $row['allow_data'];
    }
  }
  $stmt->close();
}




$saveButton_JS = "";
$s_id = getSS("s_id");
if(date("Y-m-d") == $collect_date){
  $saveButton_JS = "$('.btn-save-data').show();";
}
//echo "date: ".date("Y-m-d");
if($s_id == ''){ // patient case
  $s_id = getQS("s_id");

}
else{ // staff

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
  }

  $saveButton_JS .= "$('.btn-hist-log').show();";


  // allow to save incomplete form data *********************
  if($flag_allow_data){
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
      $fnc_save_formdone = "
      if(flag_form_done == 2){
        $.notify('ข้อมูลไม่ครบ | Incomplete Data.','error');
        return;
      }
      ";
    }
    //*****************

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

if($sLang == "") $sLang="th"; // thai default language


$form_row_height = '50';
$d_choice_hide = "";

// data itm properties
$d_itm_prop = array();
$query = "SELECT  data_id, attr_id, attr_val, form_name_th
FROM p_form_list_data_attribute FLDA, p_form_list as FL
WHERE FLDA.form_id=? AND FLDA.form_id=FL.form_id
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$form_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_id, $attr_id, $attr_val, $form_name);
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
$query = "SELECT  FLD.data_id, FLD.data_type,
DSL.data_value, DSL.data_name_th, DSL.data_name_en, FLD.is_require
FROM p_data_sub_list DSL, p_form_list_data FLD
WHERE FLD.form_id=? AND DSL.data_id=FLD.data_id
ORDER BY DSL.data_id, DSL.data_seq
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$form_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_id, $data_type, $data_value, $data_name_th, $data_name_en, $is_require);
  while ($stmt->fetch()) {
      if($data_type == "dropdown"){
        if(!isset($d_sub_item[$data_id] ))  $d_sub_item[$data_id] = "";
        $d_sub_item[$data_id] .= "<option class='lang_th' value='$data_value' >$data_name_th </option>";
    //    $d_sub_item[$data_id] .= "<option class='lang_en' value='$data_value' style:'display:none;'>$data_name_en </option>";

      }
      else if($data_type == "radio"){
        if(!isset($d_sub_item[$data_id] ))  $d_sub_item[$data_id] = "";

        $optalign = isset($d_itm_prop[$data_id]['optalign'] )?$d_itm_prop[$data_id]['optalign']:"H";
        $optalign = ($optalign == "H")?"style='margin-right:5px;'":"";

        $d_sub_item[$data_id] .= "<label class='pbtn' $optalign><input type='radio' class='save-data v-radio'  name='$data_id' data-id='$data_id' data-isrequire='$is_require' data-odata='' value='$data_value' /> $data_name_th </label><br>";

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
";
//echo "$uid, $form_id / $query";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('ssss',$uid, $collect_date, $collect_time, $form_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_id, $data_result);
  while ($stmt->fetch()) {
    $arr_data_result[$data_id] = $data_result;
  }//while
}
$stmt->close();





$sHtml = "";
$query = "SELECT  FLD.data_seq, FLD.data_id, FLD.data_type, FLD.data_value,FLD.is_require,
DL.data_question_th as data_desc, DL.data_name_th as data_name, DL.data_prefix_th as prefix, DL.data_suffix_th as suffix

FROM p_form_list_data FLD
LEFT JOIN  p_data_list DL ON (FLD.data_id=DL.data_id)
WHERE FLD.form_id=?
ORDER BY FLD.data_seq
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$form_id);
//echo "query : $query";
if($stmt->execute()){


  $stmt->bind_result($data_seq, $data_id,  $data_type, $data_value, $is_require,
  $data_desc, $data_name,  $prefix, $suffix
  );
  $width = "";
  $cur_q_label = "";
  while ($stmt->fetch()) {
    if($data_type == "html"){ // html
      $sHtml .= "<div id='$data_id' class='$data_type'>$data_value</div>";
    }
    else if($data_type == "q_label"){ // topic title
      $cur_q_label = $data_id;
      if($data_value == '')
      $sHtml .= "<div id='$data_id'  class='q_label bg-mdark1 mt-2 mb-1'><hr></div>";

      else
      $sHtml .= "<div id='$data_id'  class='q_label bg-mdark1 ptxt-white ptxt-b px-1 py-2 mt-2 mb-1'>$data_value</div>";
    }
    else { // data comp item
      $width = isset($d_itm_prop[$data_id]['width'])?$d_itm_prop[$data_id]['width']:"";
      $width = ($width !="")?"width:".$width."px;":"";

      $placeholder =isset($d_itm_prop[$data_id]['placeholder'])?$d_itm_prop[$data_id]['placeholder']:"";
      $placeholder = "placeholder='".(($placeholder !="")?$placeholder:$data_desc)."'";


      $special_data_class = "";
      $color_require = ($is_require == '1')?"pbg-yellow":"";
      if(isset($d_itm_prop[$data_id]['hideprefixsuffix'])){
        if($d_itm_prop[$data_id]['hideprefixsuffix'] == '1'){
          $prefix=""; $suffix="";
        }
      }

      /*
            $odata = isset($arr_data_result[$data_id])?$arr_data_result[$data_id]:"";
            $data_result = isset($arr_data_latest_result[$data_id])?$arr_data_latest_result[$data_id]:$odata;
      */
/*
            $odata = isset($arr_data_result[$data_id])?$arr_data_result[$data_id]:"";
            $data_result = $odata;

            if(($data_result == "") && isset($arr_data_latest_result[$data_id])){
              $data_result = $arr_data_latest_result[$data_id];
            }
*/
      $odata=""; $data_result="";
      if(isset($arr_data_result[$data_id])){
        $odata = $arr_data_result[$data_id];
        $data_result = $odata;
      }
      else{ // no value in selected visit

        $data_result = (isset($arr_data_latest_result[$data_id])?$arr_data_latest_result[$data_id]:"") ;
        echo "<br>latest data: $data_id: $data_result";
      }

      $sHtml .= "<span class='showid'>$data_id</span><div id='$data_id' class='data-item pl-2'>";
      $sHtml .= "$prefix ";
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
        $is_check = ($data_result == '1')?"checked":"";
        $sHtml .= "<label class='pbtn'><INPUT type='checkbox' name='$cur_q_label' data-id='$data_id' data-odata='$odata' data-isrequire='$is_require' class='save-data v-checkbox $special_data_class' $is_check> $data_name </label>";
      }
      else if($data_type == "dropdown"){
        $select_option = isset($d_sub_item[$data_id])?$d_sub_item[$data_id]:"";
        if($data_result != "") {
          $txt_to_replace = "'$data_result'";
          $txtreplace = $txt_to_replace." selected ";
          $select_option = str_replace($txt_to_replace,$txtreplace,$select_option);
        }

        $sHtml .= "<SELECT  name='$data_id' data-odata='$odata' data-id='$data_id' data-isrequire='$is_require' class='save-data v-dropdown $color_require $special_data_class' style='$width'>";
        $sHtml .= "<option  value='' >เลือก [$data_desc]</option>";
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
        /*
        <iframe src='<? echo($sUrl); ?>' style='width:100%;height: 99%'>
        </iframe>
        */
        $log_qs_txt = ""; $formheight="";
        if(isset($d_itm_prop[$data_id]['logformid'])){
          $log_qs_txt .= "&formid=".$d_itm_prop[$data_id]['logformid'];
        }
        if(isset($d_itm_prop[$data_id]['onlyvisitid'])){
          $log_qs_txt .= "&visitid=$visit_id";
        }
        if(isset($d_itm_prop[$data_id]['formheight'])){
          $formheight=$d_itm_prop[$data_id]['formheight'];
        }

        $sHtml .= "<div id='$data_id' class='q_label bg-mdark1 ptxt-white ptxt-b px-1 py-2 mt-2'>$data_value</div>";
        $sHtml .= "<iframe class='mb-1' src='ext_index2.php?file=p_form_view_log&uid=".$uid.$log_qs_txt."' style='width:100%;height:".$formheight."px;'></iframe>";

      }


      $sHtml .= "$suffix ";
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
        $div_form_data_import_txt .= "<option value='$form_collect_time'>$form_id: $form_collect_time</option>";
    }//while
  }
  $stmt->close();
  if($div_form_data_import_txt != ""){
    $div_form_data_import_txt = "
      <span class='mx-4 px-1 pt-2 ptxt-white bg-mdark2 form-title'>Import Data:
        <select class='sel_import_formdata px-1'>
          $div_form_data_import_txt
        </select><button class='pbtn px-1 bg-mdark1 ptxt-white btn-import-formdata' title='Import Data'><i class='fa fa-download '></i></button>
        <i class='fa fa-spinner spinner' style='display:none;'></i>
      </span>
    ";
  }

}






$mysqli->close();


$txt_row_main = "
<div class='div-form-view fl-wrap-col bg-msoft2 '
 data-uid='$uid' data-formid='$form_id' data-coldate='$collect_date' data-coltime='$collect_time' data-sid='$s_id' data-nextformid='$sNFormId' data-nextformlist='$qsNextForm'>
  <div class='fl-wrap-col fl-auto'>
    <div class='fl-fix fl-mid ph20' >

       <span class='px-1 ptxt-white pbg-blue form-title'>Form ID: $form_id</span><span class='px-1 ptxt-white bg-sdark1'>$uid</span><span class='px-1 ptxt-white bg-sdark2'> $collect_date $collect_time</span>
       <button class='px-1 pbtn pbtn-blue btn-hist-log' style='display:none;'><i class='fa fa-history fa-lg'></i> History Log</button>

       $div_form_data_import_txt
    </div>
    <div class='fl-fill py-4 px-2'>
      $sHtml
    </div>

    <div class='fl-fix h-50 fl-mid'>
      <button class='pbtn pbtn-ok btn-save-data' style='display:none;'>บันทึกข้อมูล | SAVE DATA</button>
      <button class='pbtn pbtn-warning btn-save-data-activate' style='display:none;'>
        ท่านกำลังดูข้อมูลที่จัดเก็บในวันที่ $collect_date หากต้องการแก้ไขให้กดปุ่มนี้ | This data is collected on $collect_date, click here to edit data.
      </button>
      <i class='fas fa-spinner fa-spin spinner' style='display:none;'></i>
    </div>
  </div>

</div>

";



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

var rowid = 0;
$('.showid').hide();
var currentDate = new Date();
<?
  echo "var sLang = '".$sLang."';
  var aShowif = [".$txt_arr_showif."];
  var aHideif = [".$txt_arr_hideif."];
  var aPutafter = [".$txt_arr_putafter."];
  $d_choice_hide

  ";
?>

$(document).ready(function(){



  // initialize data ////////
  <? echo $saveButton_JS; ?>

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

    var aData = {
        u_mode:"get_import_formdata",
        uid:sUID,
        collect_date:sColdate,
        collect_time:sColtime,
        formid:sFormid
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
            //  console.log("enter data_id: "+key);
               dataObj = $(".save-data[data-id='"+key+"']");
           }




            let data_val = getWDataCompValue(dataObj);

        //    console.log(key+": "+value+"/"+data_val+"/"+dataObj.attr('data-odata'));

            if(data_val == ''){
              if(dataObj.prop("tagName")){
                if(dataObj.prop("tagName").toUpperCase() == "INPUT"){
                  let data_type = dataObj.attr("type").toUpperCase();

                  if(data_type=="RADIO"){
                    $(".save-data[name='"+key+"'][value='"+value+"']").prop("checked",true);
                  }
                  if(data_type=="CHECKBOX"){
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

  // change to th date
  $(".div-form-view .v-date-th").each(function(ix,objx){
    $(objx).val(changeEn2ThDate($(objx).val()));
  });

  $.each(aPutafter,function(ix,objx){
    let sObj = objx.split(":");
    //  console.log(ix+" - "+sObj[0]+":"+sObj[1]+":"+sObj[2]); // data_id:data_parent_id:data_parent_value
    if(sObj[2]==""){ // parent_value
    }else{
      console.log(ix+" - "+sObj[0]+":"+sObj[1]+":"+sObj[2]); // data_id:data_parent_id:data_parent_value
      $("input[data-id='"+sObj[1]+"'][value='"+sObj[2]+"']").parent().append($(".data-item[id='"+sObj[0]+"']"));
    }
  });

  checkRequireIf();
  checkHideIf();
  //*************************



  $(".div-form-view").on("click",".v-checkbox",function(){
    checkRequireIf();
    checkHideIf();
  });


  $(".div-form-view").on("change",".v-radio",function(){
    //console.log("click "+$(this).attr('name')+"/"+$(this).val());
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


/*
  $('.div-form-view').on('keydown', '.v-number', function() {

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
*/

}); //End DOcument ready



function checkRequireIf(){ // hide data and clear value in hidden input
  aShowif.forEach(function(objx) {
    let sObj = objx.split(":");
    let cur_parent_value = getWDataCompValue($(".save-data[data-id='"+sObj[1]+"']"));
    console.log(" - "+sObj[0]+":"+sObj[1]+":"+sObj[2]+" | cur val: "+cur_parent_value); // data_id:data_parent_id:data_parent_value
    if(cur_parent_value == sObj[2]){ //trigger rule  , show data child
      //console.log(" showdatachild "+sObj[0]+"/"+sObj[1]+"/"+sObj[2]);
      $("#"+sObj[0]).show();

    }else{ // hide and clear data child
      console.log(" hide1 "+sObj[0]+":"+sObj[1]+":"+sObj[2]+" | cur val: "+cur_parent_value);
      $("#"+sObj[0]).hide();
      if($("#"+sObj[0]).hasClass('data-item')){
        console.log(" hide2 "+sObj[0]+":"+sObj[1]+":"+sObj[2]+" | cur val: "+cur_parent_value);
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
        //    $(".save-data[data-id='"+sObj[0]+"']").val("");
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
      if($(".save-data[data-id='"+sObj[2]+"']").is(':visible'))
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
  console.log("enter save data");
  // remove all warning component
  $(".div-form-view .bg-warning").each(function(ix,objx){
     $(objx).removeClass('bg-warning');
  });

  let sUID = $('.div-form-view').attr('data-uid');
  let sFormid = $('.div-form-view').attr('data-formid');
  let sColdate = $('.div-form-view').attr('data-coldate');
  let sColtime = $('.div-form-view').attr('data-coltime');
  let sSid = $('.div-form-view').attr('data-sid');
  let flag_valid = 1;
  let flag_require = 0;
  let flag_update_file_upload = 0;


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
          flag_form_done = 2 ;
       }
     }



      //console.log("enter here 01 "+$(objx).attr('data-id'));
     if(sVal != sOData){ //check data changed
      // console.log("dataid: "+$(objx).attr('data-id')+"/"+sVal+"::"+sOData);
       if(!checkValidData(objx)){
         $(objx).focus();
         $(objx).notify("ข้อมูลไม่ถูกต้องกรุณาตรวจสอบ | Invalid data! Please check.","warn");
         $(objx).parent().addClass('bg-warning');
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
        return;
   }



   if(flag_valid == 0){
      $.notify("ข้อมูลไม่ถูกต้อง | Invalid Data.","error");
      return;
   }

   <?
     echo $fnc_save_formdone;
   ?>


     if(lst_data_obj.length == 0 && flag_update_file_upload == 0){

       let sNextForm = $('.div-form-view').attr('data-nextformid');
       if(sNextForm != ''){
         console.log("enter01");
         checkNextForm();
       }
       else{
         $.notify("ข้อมูลไม่เปลี่ยนแปลง | No data changed.","info");

       }

       //return;
     }
     else if(lst_data_obj.length > 0){
           var aData = {
               u_mode:"form_data_update",
               uid:sUID,
               collect_date:sColdate,
               collect_time:sColtime,
               lst_data:lst_data_obj,
               s_id:sSid,
               formid:sFormid,
               form_done:flag_form_done
               };

           startLoad(btnsave, btnsave.next(".spinner"));
           callAjax("p_form_view_a.php",aData,function(rtnObj,aData){
               endLoad(btnsave, btnsave.next(".spinner"));
               if(rtnObj.res == 1){
                 $.notify("DATA SAVED","success");
                 $('.div-form-view .btn-save-data-activate').show();
                 btnsave.hide();

                 $(".data-update").each(function(ix,objx){
                   let sVal = getWDataCompValue(objx);
                   setWODataComp(objx, sVal);
                  // console.log("set odata: "+$(objx).attr("name"));
                 });
                 $(".save-data").removeClass("data-update");
                 if(flag_update_file_upload == 1){
                   saveFileUpload(sUID, sColdate, sColtime, sSid);
                 }
                 else{
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
       saveFileUpload(sUID, sColdate, sColtime, sSid);
     }

} // saveFormData


function saveFileUpload(sUID,sColdate,sColtime){
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


</script>
