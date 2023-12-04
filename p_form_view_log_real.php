<?


include_once("in_db_conn.php");
include_once("in_php_function.php");

$uid = getQS("uid");
$form_id = getQS("formid");
$visit_id = getQS("visitid");
$s_id = getQS("s_id");
$sProjid = getQS("projid");

$sLang = getQS("lang");
if($sLang == "") $sLang="th"; // thai default language

$form_name_th="";$form_name_en="";$form_protocol_version="";
$d_choice_hide = "";

$allow_data = 0;
$data_val_init = "";

// check permission
include_once("in_session.php");
if(isset($_SESSION["s_id"])){
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

//echo "allowdata: $allow_data";

$query_visit_add = ""; $query_rowid_order = " DESC";
if($visit_id != ""){
  $query_visit_add = " AND DLR.visit_id = ? ";
  $query_rowid_order = "";
}

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




$form_head_row_height = '50';
$form_row_height = '50';


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


  $form_head_row_height = isset($d_itm_prop['']['headrowheight'])?$d_itm_prop['']['headrowheight']:'50' ;
  $form_row_height = isset($d_itm_prop['']['rowheight'])?$d_itm_prop['']['rowheight']:'50' ;
  //echo "rowheight: $form_row_height";
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
LEFT JOIN p_data_list DL ON (FLD.data_id=DL.data_id)
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
        $d_sub_item[$data_id] .= "<option value='$data_value' $data_id=''>$data_name_th </option>";
    //    $d_sub_item[$data_id] .= "<option class='lang_en' value='$data_value' style:'display:none;'>$data_name_en </option>";

      }
      /*
      else if($data_type == "radio"){
        if(!isset($d_sub_item[$data_id] ))  $d_sub_item[$data_id] = "";

        $optalign = isset($d_itm_prop[$data_id]['optalign'] )?$d_itm_prop[$data_id]['optalign']:"H";
        $optalign = ($optalign == "H")?"style='margin-right:5px;'":"";

        $d_sub_item[$data_id] .= "<label $optalign><input type='radio' class='save-data v-radio' id='$data_id-$data_value' name='$data_id' data-id='$data_id' data-odata=''  value='$data_value' /> $data_name_th </label><br>";

      }
      */
      else if($data_type == "radio"){
        if(!isset($d_sub_item[$data_id] ))  $d_sub_item[$data_id] = "";
        $radio_css = ""; $radio_html = "<br>";
        $optalign = isset($d_itm_prop[$data_id]['optalign'] )?$d_itm_prop[$data_id]['optalign']:"H";
        if($optalign == 'H') {
          $radio_css = "style='margin-right:10px;'"; $radio_html = "";
        }
        $d_sub_item[$data_id] .= "<label class='pbtn' $radio_css><input type='radio' class='save-data v-radio'  name='$data_id' data-id='$data_id' data-isrequire='$is_require' data-odata='' value='$data_value' /> <span class='lang th'>$data_name_th</span> </label>$radio_html";
      }
  }// while
}
else{
$msg_error .= $stmt->error;
}
$stmt->close();




$cur_colhead = "";
$d_itm = array(); // data item in form

$txt_row_head = "";
$txt_row_data = "";
$txt_col_data = "";

$query = "SELECT  FLD.data_seq, FLD.data_id, FLD.data_type, FLD.data_value,FLD.data_value_en,FLD.is_require,
DL.data_question_th as data_desc, DL.data_prefix_th as prefix, DL.data_suffix_th as suffix,
DL.data_question_en as data_desc_en, DL.data_prefix_en as prefix_en, DL.data_suffix_en as suffix_en
FROM p_form_list_data FLD
LEFT JOIN  p_data_list DL ON (FLD.data_id=DL.data_id)
WHERE FLD.form_id=?
ORDER BY FLD.data_seq
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$form_id);
//echo "$form_id / query : $query";
if($stmt->execute()){


  $stmt->bind_result($data_seq, $data_id,  $data_type, $data_value,$data_value_en, $is_require,
  $data_desc, $prefix, $suffix, $data_desc_en, $prefix_en, $suffix_en
  );
  $width = "";
  while ($stmt->fetch()) {

      $width = isset($d_itm_prop[$data_id]['width'])?$d_itm_prop[$data_id]['width']:"";
      $width = ($width !="")?"width:".$width."px;":"";

      $placeholder =isset($d_itm_prop[$data_id]['placeholder'])?$d_itm_prop[$data_id]['placeholder']:"";
      $placeholder = "placeholder='".(($placeholder !="")?$placeholder:$data_desc)."'";
//echo"<br>$data_type : $data_id";
      if($data_type == "colhead"){ // col header
        if($cur_colhead != $data_id){
          $txt_row_data = str_replace("[$cur_colhead]",$txt_col_data,$txt_row_data);
          $cur_colhead = $data_id;
          $txt_col_data = "";
        }

        $style_width = "";
        $class_fl = "";

        if($width != ""){
          $style_width = "style='min-".$width."max-".$width."' ";
          $class_fl = "fl-fix";
        }
        else{
          $class_fl = "fl-fill";
        }

        $txt_row_head .= "
        <div class='$class_fl d-pad v-mid' $style_width >
          $data_value
        </div>";

        $txt_row_data .= "
        <div class='$class_fl d-pad data-item' $style_width >
          [$cur_colhead]

        </div>";
      }
      else{ // data itm
        $special_data_class = "";
        $color_require = ($is_require == '1')?"pbg-yellow":"";
        if(isset($d_itm_prop[$data_id]['hideprefixsuffix'])){
          if($d_itm_prop[$data_id]['hideprefixsuffix'] == '1'){
            $prefix=""; $suffix="";
          }
        }




        $txt_col_data .= "<div class='fl-float col-data data-item' style='min-height=20px;' id='$data_id'> ";
        $txt_col_data .= "$prefix ";
        if($data_type == "text" || $data_type == "date" || $data_type == "number"){

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

          $txt_col_data .= "<INPUT type='text' name='$data_id' data-id='$data_id' data-odata='' data-isrequire='$is_require' class='save-data $color_require v-$data_type $special_data_class' style='$width' $placeholder>";
        }


        else if($data_type == "checkbox"){

          $txt_col_data .= "<INPUT type='checkbox' name='$data_id' data-id='$data_id' data-odata='' data-isrequire='$is_require' class='save-data v-checkbox $special_data_class'> $data_desc ";
        }
        else if($data_type == "dropdown"){
          $txt_col_data .= "<SELECT  name='$data_id' data-odata='' data-id='$data_id' data-isrequire='$is_require' class='save-data v-dropdown $color_require $special_data_class' style='$width'>";
          $txt_col_data .= "<option  value='' >เลือก [$data_desc]</option>";
          $txt_col_data .= isset($d_sub_item[$data_id])?$d_sub_item[$data_id]:"";
          $txt_col_data .= "</SELECT>";


          if(isset($d_itm_prop[$data_id]['hidesomechoice'])){
            $hidesomechoice = $d_itm_prop[$data_id]['hidesomechoice'];
            $arrhidechoice = explode(",",$hidesomechoice);
            foreach($arrhidechoice as $hidechoice){
              $d_choice_hide .= "$(\"select[name='$data_id'] option[value='$hidechoice']\").hide();";
            }//foreach
          }


        }
        else if($data_type == "radio"){
          $optalign = isset($d_itm_prop[$data_id]['optalign'])?$d_itm_prop[$data_id]['optalign']:"H";
          if(isset($d_sub_item[$data_id])){
            $txt_col_data .= $d_sub_item[$data_id];
          }

          if(isset($d_itm_prop[$data_id]['hidesomechoice'])){
            $hidesomechoice = $d_itm_prop[$data_id]['hidesomechoice'];
            $arrhidechoice = explode(",",$hidesomechoice);
            foreach($arrhidechoice as $hidechoice){
              $d_choice_hide .= "$(\"input[name='$data_id'][value='$hidechoice']\").parent().remove();";
            }//foreach
          }

        }//radio
        else if($data_type == "textarea"){
          $ta_row ="rows='".(isset($d_itm_prop[$data_id]['row'])?$d_itm_prop[$data_id]['row']:"2")."'";
          $ta_col ="cols='".(isset($d_itm_prop[$data_id]['col'])?$d_itm_prop[$data_id]['col']:"4")."'";

          $txt_col_data .= "<textarea  data-id='$data_id' data-odata='' data-isrequire='$is_require' class='save-data v-text $color_require $special_data_class' width='100%' $ta_row $ta_col $placeholder name='$data_id'></textarea>";
        }//textarea

        $txt_col_data .= " $suffix";
        $txt_col_data .= "</div> ";
      }//data itm


  }// while
  $txt_row_data = str_replace("[$cur_colhead]",$txt_col_data,$txt_row_data);
}
else{
  $msg_error .= $stmt->error;
  error_log("mnu_form_view_log: ".$msg_error);
}
$stmt->close();



$txt_row_head = "
<div class='fl-fix pw30 fl-mid'>
 #
</div>
<div class='fl-fix pw40 fl-mid div-tp' style='display:none;'>
 Timepoint
</div>


$txt_row_head
<div class='fl-fill'>

</div>
<div class='fl-fix pw100 fl-mid ptxt-b pbtn pbtn-ok btn-add-log'>
  + เพิ่ม | ADD
</div>
<div class='fl-fix pw100 fl-mid spinner' style='display:none;'>
  <i class='fas fa-spinner fa-spin fa-lg'></i>
</div>
";


$txt_row_data = "
<div class='fl-fix pw30'>
  <div class='fl-fix fl-mid ph20 pbtn pbtn-blue ptxt-s12 d-pad rowinfo'>
    ADDNew
  </div>
  <div class='fl-fix ph15'>
    <button class='pbtn pbtn-cancel btn-del-log'>x</button>
    <i class='fas fa-spinner fa-spin fa-lg spinner' style='display:none'></i>
  </div>
</div>
<div class='fl-fix pw40 bg-mdark3 div-tp' style='display:none'>
    <div class='fl-fix fl-mid ph20'>
      <input type='text' class='al-center bg-msoft1 tp-input' data-odata='' size='4' placeholder='TP'>
      <i class='fas fa-spinner fa-spin fa-lg spinner' style='display:none;'></i>
    </div>
    <div class='fl-fix fl-mid ph20'>
      <input type='text' class='al-center bg-mdark3 ptxt-white visit-input' size='4' placeholder='Visit' disabled>
    </div>

</div>
$txt_row_data
<div class='fl-fill'>

</div>
<div class='fl-fix pw100 px-1'>
  <div class='fl-wrap-col ph25'>
    <div class='fl-fix fl-mid ph20 ptxt-b pround5 pbtn pbtn-blue btn-save-row' style='display:none'>
         SAVE DATA
    </div>
    <div class='fl-fix ph25 spinner' style='display:none'>
      <i class='fas fa-spinner fa-spin fa-lg'></i>
    </div>

    <div class='fl-fix ph15'>
      <span class='ptxt-s8 lastupdate btn-hist-log bg-msoft3'>[addnew]</span>
    </div>
  </div>

</div>

";




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


//retrive dataID
$arr_data_result = array();
$query = "SELECT DR.data_id, DL.data_type,  DR.data_result, DLR.row_id
FROM p_data_log_row DLR, p_data_result DR, p_data_list as DL, p_form_list_data FLD
WHERE DLR.uid=? AND DLR.form_id=? $query_visit_add AND DR.data_id=DL.data_id
AND DLR.form_id = FLD.form_id AND FLD.data_id=DR.data_id
AND DR.uid=DLR.uid AND DR.collect_date= DLR.collect_date AND DR.collect_time= DLR.collect_time
ORDER BY DLR.row_id
";
// echo "$uid, $form_id, $visit_id / $query";
$stmt = $mysqli->prepare($query);
if($query_visit_add == '')
$stmt->bind_param('ss',$uid, $form_id);
else
$stmt->bind_param('sss',$uid, $form_id, $visit_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_id, $data_type, $data_result,  $row_id);
  while ($stmt->fetch()) {
    if(!isset($arr_data_result[$row_id])) $arr_data_result[$row_id] = array();
    array_push($arr_data_result[$row_id], "$data_type:$data_id:$data_result");
    // $arr_data_result[$row_id][]="$data_type:$data_id:$data_result";
    // echo $data_result."/".$data_id."<br>";
  }
  // print_r($arr_data_result);
}

$txt_row_init = "";
$txtrow = "";
$arr_radio_value = array();
$query = "SELECT DLR.row_id,DLR.collect_date, DLR.collect_time, DLR.visit_id, DLR.timepoint_id,
(select max(DR.lastupdate) from p_data_result DR where
DR.uid=DLR.uid AND DR.collect_date=DLR.collect_date AND DR.collect_time=DLR.collect_time
AND DR.data_id IN(select data_id from p_form_list_data where form_id=? )
) as lastupdate
FROM p_data_log_row DLR
WHERE DLR.uid=? AND DLR.form_id=? $query_visit_add
ORDER BY DLR.row_id $query_rowid_order
";

// echo "$form_id, $uid, $form_id, $visit_id / $query";
$stmt = $mysqli->prepare($query);
if($query_visit_add == '')
$stmt->bind_param('sss',$form_id, $uid, $form_id);
else
$stmt->bind_param('ssss',$form_id, $uid, $form_id, $visit_id);
//echo "$uid, $form_id / query : $query";
$check_js = "";
if($stmt->execute()){
  $stmt->bind_result($row_id, $row_coldate, $row_coltime, $row_visitid, $row_timepoint, $lastupdate);

  while ($stmt->fetch()) {
    $txtrow = $txt_row_data;
    if(isset($arr_data_result[$row_id])){

      foreach($arr_data_result[$row_id] as $data_itm){
        // echo $data_itm.":".$row_id."<br>";
        $arr_data_itm = explode(':',$data_itm, 3); // 0=data_type, 1=data_id, 2=data_result
        // echo "<br>row: ".$arr_data_itm[1]."/".$arr_data_itm[0]."/".$arr_data_itm[2];
        //$arr_data_itm[2] = str_replace("'", "",$arr_data_itm[2] );
        $arr_data_itm[2] = htmlentities($arr_data_itm[2], ENT_QUOTES);
        $txt_to_replace = ""; $txt_replace="";
        if($arr_data_itm[0] == "text" || $arr_data_itm[0] == "date" || $arr_data_itm[0] == "number"){

          $txt_to_replace = "name='".$arr_data_itm[1]."'";
          $txtreplace = "name='".$arr_data_itm[1]."' VALUE='".$arr_data_itm[2]."' ";
        }
        else if($arr_data_itm[0] == "dropdown"){
          //$txt_to_replace = "value='".$arr_data_itm[2]."'";
        //  echo "<br>".$arr_data_itm[1]."  val:".$arr_data_itm[2];
          $arr_data_itm[2] = urldecode($arr_data_itm[2]);
          $txt_to_replace = strtolower("'".$arr_data_itm[2]."' ".$arr_data_itm[1]);
          $txtreplace = strtolower("'".$arr_data_itm[2]."' selected ".$arr_data_itm[1]);

          $txt_to_replace = "'".htmlspecialchars_decode($arr_data_itm[2])."' ".$arr_data_itm[1];
          $txtreplace = $txt_to_replace." selected ";
        //  echo "<br>$txt_to_replace / $txtreplace";

    //      $data_val_init .= "$('.d-row[data-rowid=\"$row_id\"]').find('.save-data[data-id=\"".$arr_data_itm[1]."\"]').val(getShowText2('".urlencode($arr_data_itm[2])."'));";
        //  $data_val_init .= "$('.d-row[data-rowid=\"$row_id\"]').find('.save-data[data-id=\"".$arr_data_itm[1]."\"]').val('".$arr_data_itm[2]."');";
        }
        else if($arr_data_itm[0] == "radio"){
          $check_js .= '$("[name='.$row_id.$arr_data_itm[1].'][value='.$arr_data_itm[2].']").prop("checked", true);';
          // echo $check_js;
          $arr_radio_value[$row_id.$arr_data_itm[1]] = htmlspecialchars_decode($arr_data_itm[2]);
          $txtrow = str_replace(" id='".$arr_data_itm[1]," id='".$row_id.$arr_data_itm[1], $txtrow);
          $txt_to_replace = "name='".$arr_data_itm[1]."'";
          $txtreplace = "name='".$row_id.$arr_data_itm[1]."'";
        }
        else if($arr_data_itm[0] == "checkbox"){
          $check = ($arr_data_itm[2] == '1')?'checked':'';
          $txt_to_replace = "name='".$arr_data_itm[1]."'";
          $txtreplace = "name='".$arr_data_itm[1]."' $check";
        }
        else if($arr_data_itm[0] == "textarea"){
          $txt_to_replace = "name='".$arr_data_itm[1]."'>";
          $txtreplace = "name='".$arr_data_itm[1]."'>".$arr_data_itm[2];
        }
        //$aa = strpos($txt_row_data,$txt_to_replace );
       //echo "replace : $txt_to_replace / $txtreplace -- $aa";
       if($txt_to_replace != '')
       $txtrow = str_replace($txt_to_replace,$txtreplace,$txtrow);

      }// foreach

    }

    if($lastupdate !== NULL)
    $txtrow = str_replace("[addnew]","[$lastupdate]",$txtrow);

    $txtrow = str_replace("ADDNew",$row_id,$txtrow);
    $txtrow = str_replace("tp-input' data-odata=''","tp-input' value='$row_timepoint' data-odata='$row_timepoint'",$txtrow);
    $txtrow = str_replace("visit-input'","visit-input' value='$row_visitid' ",$txtrow);



    //$txt_row_init .= "<div class='fl-wrap-row bg-msoft2 ptxt-s10 d-row r$row_id' data-rowid='$row_id' style='min-height:".$form_row_height."px; max-height:".$form_row_height."px;'>$txtrow</div>";
    $txt_row_init .= "<div class='fl-wrap-row bg-msoft2 ptxt-s10 d-row r$row_id' data-rowid='$row_id' data-coldate='$row_coldate' data-coltime='$row_coltime' style='min-height:".$form_row_height."px; max-height:".$form_row_height."px;'>$txtrow</div>";

  }//while
}








$stmt->close();
$mysqli->close();

$txt_radio_value_init = ""; // initial radio value
foreach($arr_radio_value as $key => $value){
  // $txt_radio_value_init .= "$('#$key-$value').prop('checked', true);";
  $txt_radio_value_init .= "$('#$key').attr('data-odata', '$value');";
  //echo "<br> key/value: $key-$value";
}//




$txt_row_main = "";
if($txt_col_data != ""){
  $visit_txt = ($visit_id != '')?"[Visit: $visit_id]":"";
  $form_protocol_version = ($form_protocol_version != '')?"V. $form_protocol_version":"";

  $txt_row_main = "
  <div class='fl-wrap-row h-20 fl-mid ptxt-b ptxt-white ptxt-14 bg-mdark1'>$form_name_th $form_protocol_version $visit_txt</div>
  <div class='fl-wrap-col div-log-main' data-formid='$form_id'  data-visitid='$visit_id' data-uid='$uid' data-row_height='$form_row_height' >
    <div class='fl-wrap-row fl-auto bg-mdark2 ph50 ptxt-white ptxt-s12' style='min-height:".$form_head_row_height."px; max-height:".$form_head_row_height."px;'>
     $txt_row_head
    </div>
    <div class='fl-wrap-col fl-auto div-log-info'>
      <div class='fl-wrap-row ph10 div-log-add-row' style='display:none;'>
        $txt_row_data
      </div>
      $txt_row_init
    </div>
    <div class='fl-fix bg-mdark2 ph30 ptxt-white  v-mid'>
       <div class='fl-fix pw200 ptxt-s20'>
           Total: <span class='ptxt ptxt-white ptxt-b ttl-row' > 0 </span>
       </div>
       <div class='fl-fill fl-mid ptxt-s14'>
           <button class='pbtn pbtn-blue btn-save-all' style='display:none;'>** SAVE ALL DATA CHANGED **</button>
       </div>
    </div>
  </div>
  ";
}
else{
  $txt_row_main = "
  <div class='fl-wrap-col fl-mid ptxt-b ptxt-white ptxt-14 bg-mdark1'>Form is invalid! | แบบฟอร์มไม่ถูกต้อง (Form ID: $form_id)</div>";
}




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
  border-bottom:1px solid grey;
}
.d-row:hover{
/*	filter:brightness(80%); */
/*  font-weight: bold; */
  color:black;
  background-color: #EEE;
}
</style>

<div id='divPFVL' class='fl-wrap-col al-left'>
  <? echo $txt_row_main; ?>
</div>

<script>
var rowid = 0;
<?
  echo "var aShowif = [".$txt_arr_showif."];
  var aHideif = [".$txt_arr_hideif."];
  var aPutafter = [".$txt_arr_putafter."];
  $d_choice_hide
  ";
?>
$(document).ready(function(){
// initialize data ////////
<? echo $txt_radio_value_init;
   echo $check_js;
   echo $data_val_init;
   if($allow_data != '1'){
     if($uid != 'TEST')
     echo "$('.btn-add-log').remove();";
   }

?>



$('.div-log-main .d-row').each(function(ix,objx){
  let rowID = $(objx).attr('data-rowid');
   checkLogRequireIf(rowID);
});

// if there is visit id, show timepoint
if($('.div-log-main').attr('data-visitid') != ''){
  $('.div-tp').show();
}


// history log


$(".div-log-main .btn-hist-log").off("click");
$(".div-log-main .btn-hist-log").on("click",function(){
  let sUID = $('.div-log-main').attr('data-uid');
  let sFormid = $('.div-log-main').attr('data-formid');
  //console.log( $(this).parent().parent().parent().parent().attr("class"));
  //console.log( $(this).closest(".d-row").attr("data-coldate"));
  let sColdate = $(this).closest(".d-row").attr("data-coldate");
  let sColtime = $(this).closest(".d-row").attr("data-coltime");

  let sUrl = "p_form_history_log.php?uid="+sUID+"&coldate="+sColdate+"&coltime="+sColtime+"&formid="+sFormid;
  //console.log("sUrl: "+sUrl);

  showDialog(sUrl,"Data History | Form ID: "+sFormid+ " [UID:"+sUID+" |Date:"+sColdate+" "+sColtime+"]","90%","99%","",function(sResult){
      if(sResult != ""){
      }
  },false,function(){
  });


});

/*
// show onlyvisitid (when visit_id is assigned in querystring, show only selected visit data row)
let log_visit_id = $('.div-log-main').attr('data-visitid');
if(log_visit_id != ''){
  let formid = $('.div-log-main').attr('data-formid');
  $('.div-log-main .d-row').hide();
  $('.div-log-info .save-data[name="'+formid+'_visit"]').each(function(ix,objx){
     if($(objx).val() == log_visit_id){
       $(objx).parent().parent().parent().show();
     }
  });
}
*/

$(".div-log-info .tp-input").off("focusout");
$(".div-log-info .tp-input").on("focusout",function(){

   let current_comp = $(this);
   let sRowid = $(this).closest(".d-row").attr("data-rowid");
  // let sRowid = $(this).parent().parent().parent().attr('data-rowid');
   if(current_comp.val() == current_comp.attr('data-odata')){
     return;
   }

   let sVisitid = $('.div-log-main').attr('data-visitid');
   let sFormid = $('.div-log-main').attr('data-formid');
   let sUID = $('.div-log-main').attr('data-uid');

      var aData = {
          u_mode:"update_timepoint",
          uid:sUID,
          formid:sFormid,
          visitid:sVisitid,
          rowid:sRowid,
          timepointid:current_comp.val()
          };
          startLoad(current_comp, current_comp.next(".spinner"));
          callAjax("p_form_view_log_a.php",aData,function(rtnObj,aData){
              endLoad(current_comp, current_comp.next(".spinner"));
              if(rtnObj.res == 1){
                current_comp.notify("Data SAVED", "success");
                current_comp.attr('data-odata', current_comp.val());
              }
              else{
                current_comp.notify("Fail to update", "error");
              }
          });// call ajax


});

// change to th date
$(".div-log-info .v-date-th").each(function(ix,objx){
  $(objx).val(changeEn2ThDate($(objx).val()));

});

  $(".div-log-info .save-data").each(function(ix,objx){
    let sVal = getWDataCompValue(objx);
    let sOData = getWODataComp(objx);
    setWODataComp(objx, sVal);
  });
//  $('.div-log-main .ttl-row').html($('.d-row').length);
  $('.div-log-main .ttl-row').html($('.d-row:visible').length);
//*************************

$(".div-log-main").on("change",".save-data",function(){
  objx = $(this);
  let sVal = getWDataCompValue(objx);
  let sOData = getWODataComp(objx);
  if(sVal != sOData) objx.closest(".d-row").find(".btn-save-row").show();
  else objx.closest(".d-row").find(".btn-save-row").hide();
});



$('.div-log-main .btn-add-log').on("click",function(){
      let btn_add = $(this);
      let sUID = $('.div-log-main').attr('data-uid');
      let sFormid = $('.div-log-main').attr('data-formid');
      let sVisitid = $('.div-log-main').attr('data-visitid');
      let sRowheight = $('.div-log-main').attr('data-row_height');

      var aData = {
          u_mode:"add_row_log",
          uid:sUID,
          formid:sFormid,
          visitid:sVisitid
          };
          startLoad(btn_add, btn_add.next(".spinner"));
          callAjax("p_form_view_log_a.php",aData,function(rtnObj,aData){
              endLoad(btn_add, btn_add.next(".spinner"));
              if(rtnObj.res == 1){
                btn_add.notify("Add", "success");
                let txt_row = "<div class='fl-wrap-row bg-msoft3 ptxt-s10 d-row r"+rtnObj.rowid+"' data-rowid='"+rtnObj.rowid+"' style='min-height:"+sRowheight+"px; max-height:"+sRowheight+"px;'>";
                    txt_row += $('.div-log-add-row').html();
                    txt_row += "</div>";

                $(".div-log-info").prepend(txt_row);
                $(".r"+rtnObj.rowid+" .rowinfo").html(rtnObj.rowid);
                $(".r"+rtnObj.rowid+" .rowinfo").attr('data-rowid',rtnObj.rowid );

                $(".r"+rtnObj.rowid+" .visit-input").val(sVisitid);

                $(".r"+rtnObj.rowid+" .v-radio").each(function(ix,objx){

                  $(objx).attr('name',   rtnObj.rowid+$(objx).attr('name'));
                });

                //$(".r"+rtnObj.rowid+" .v-radio").attr("name", rtnObj.rowid+$(".r"+rtnObj.rowid+" .v-radio").attr("name"));

                $('.div-log-main .ttl-row').html($('.d-row').length);

                $(".v-date").mask("9999-99-99",{placeholder:"yyyy-mm-dd"});
                checkLogRequireIf(rtnObj.rowid);
              }
              else{
                btn_add.notify("Fail to add", "error");
              }
          });// call ajax
  });


  $(".v-date").mask("9999-99-99",{placeholder:"yyyy-mm-dd"});
  $(".div-log-main").on("focusout",".v-date",function(){
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
          $(this).notify("วันที่ไม่ถูกต้อง cccc| Invalid Date "+$(this).val(), "error");
        }
      }
      */
    }
  });

  // date picker
  $('.div-log-main').on('click', '.v-date', function(){
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



    $(".v-date-th").mask("99/99/9999",{placeholder:"dd/mm/yyyy"});
    /*
    $('.div-log-main').on('click', '.v-date-th', function(){
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
    $('.div-log-main').on('focusout', '.v-date-th', function(){
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
            $(this).notify("วันที่ไม่ถูกต้อง | Invalid Date "+$(this).val(), "error");
          }
        }
        */

      }
    });

/*
  $('.div-log-main').on('keydown', '.v-number', function(event) {
              //this.value = this.value.replace(/[^0-9\.]/g,'');
      $(this).val($(this).val().replace(/[^0-9\.]/g,''));
      if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57) && (event.which != 8)) {
        event.preventDefault();
      }
  });
*/



  $('.div-log-main').on('keydown', '.v-number', function() {
          if ((event.keyCode >= 48 && event.keyCode <= 57) ||
          (event.keyCode >= 96 && event.keyCode <= 105) ||
          event.keyCode == 8 || event.keyCode == 9 ||
          event.keyCode == 37 || event.keyCode == 39 ||
          event.keyCode == 46 || event.keyCode == 110 ||
          event.keyCode == 107 || event.keyCode == 109 ||
          event.keyCode == 173 || event.keyCode == 61 ||
          event.keyCode == 188 ||event.keyCode == 190) {
  /*
            if($(this).val().indexOf('.') !== -1){ // decimal
              if(typeof $(this).data("digit") !== "undefined"){
                var digit = $(this).val().substring($(this).val().indexOf('.'), $(this).val().length);

                if(digit.length > parseInt($(this).data("digit"))){
                  event.preventDefault();
                }
              }
            }
*/
          } else {
              event.preventDefault();
          }

  });

 $('.div-log-main .rowinfo').off("click");
  $('.div-log-main').on("click",'.rowinfo',function(){ // save row button
      let rowid = $(this).parents('.d-row').attr("data-rowid");
      let sUid = $('.div-log-main').attr('data-uid');
      let sFormid = $('.div-log-main').attr('data-formid');
      //let screen_width = screen.width;
      //console.log("screen width: "+screen_width);
      let sUrl = "p_form_view_log_new_window.php?uid="+sUid+"&formid="+sFormid+"&rowid="+rowid;
    //  showDialog(sUrl,"#"+rowid+" UID: "+sUid+ " ["+sFormid+"]","800",screen_width,"",function(sResult){
      showDialog(sUrl,"#"+rowid+" UID: "+sUid+ " ["+sFormid+"]","90%","99%","",function(sResult){


          if(sResult == "datachange"){
            sUrlX = "p_form_view_log.php?uid="+sUid+"&formid="+sFormid;
             $("#divPFVL").parent().load(sUrlX);
          }
      },false,function(){
      });
  });

  $('.div-log-main').on("click",'.btn-save-row',function(){ // save row button
      let rowid = $(this).parents('.d-row').attr("data-rowid");
      saveLogData(rowid, $(this));
  });
  $('.div-log-main').on("click",'.btn-save-all',function(){ // save row button
      saveLogData('', $(this));
  });

  $('.div-log-main').on("click",'.btn-del-log',function(){ // delete row
    let btn_del = $(this);
    let sUid = $('.div-log-main').attr('data-uid');
    let sFormid = $('.div-log-main').attr('data-formid');
    let sRowid = btn_del.parents('.d-row').attr("data-rowid");

    if(confirm("ยืนยันต้องการลบข้อมูลแถว "+sRowid+" ?")){
      var aData = {
          u_mode:"delete_row_log",
          uid:sUid,
          formid:sFormid,
          rowid:sRowid
          };

          startLoad(btn_del, btn_del.next(".spinner"));
          callAjax("p_form_view_log_a.php",aData,function(rtnObj,aData){
              endLoad(btn_del, btn_del.next(".spinner"));
              if(rtnObj.res == 1){
                $.notify("DATA Deleted", "error");
                btn_del.remove();
                $(".r"+sRowid+" .col-data").html("");
                $(".r"+sRowid+" .rowinfo").removeClass('pbtn-blue');
                $(".r"+sRowid+" .rowinfo").addClass('pbtn-cancel');
                $(".r"+sRowid+" .lastupdate").html('<center>DATA Deleted</center>');
                $(".r"+sRowid).removeClass('bg-msoft2');
                $(".r"+sRowid).addClass('bg-ssoft1');
                $(".r"+sRowid).removeClass('d-row');

                $('.div-log-main .ttl-row').html($('.d-row').length);
              }
          });// call ajax
    }
  });

  $(".div-log-main").on("dblclick",".v-radio",function(){
    //console.log('dblclick: '+$(this).attr("name"));
    if($(this).is(':checked')){
      let name = $(this).attr("name");
      $('INPUT[name="'+name+'"]').prop('checked', false);

      let rowID = $(this).closest(".d-row").attr("data-rowid");
      checkLogRequireIf(rowID);
    //  checkHideIf();
    }
  });

  $(".div-log-main").on("click",".v-radio",function(){
    let rowID = $(this).closest(".d-row").attr("data-rowid");
    checkLogRequireIf(rowID);
  });

  $(".div-log-main").on("click",".v-checkbox",function(){
    let rowID = $(this).closest(".d-row").attr("data-rowid");
    checkLogRequireIf(rowID);
  });


  $(".div-log-main").on("change",".v-dropdown",function(){
    let rowID = $(this).closest(".d-row").attr("data-rowid");
    checkLogRequireIf(rowID);
    //checkHideIf();
  });


});


function checkDataChanged(){
  let flag = false;
  $(".div-log-main .save-data").each(function(ix,objx){
    let sVal = getWDataCompValue(objx);
    let sOData = getWODataComp(objx);

    if(sVal != sOData){
      flag = true;
      return false;
    }
  });

  if(flag){
    if(confirm('ข้อมูลมีการเปลี่ยนแปลง ท่านต้องการบันทึกหรือไม่')){
      saveLogData('', $('.btn-save-all'));
    }
  }


}

function saveLogData(rowid, btnsave){

  let sUID = $('.div-log-main').attr('data-uid');
  let sFormid = $('.div-log-main').attr('data-formid');
  let flag_valid = 1;

   let lst_data_obj = [];
   let class_row = "";
   if(rowid != ""){ // save specific row
     class_row = ".r"+rowid;
   }

   //console.log("row: "+rowid+" / "+sUID+" / "+sFormid+ "/ class:"+class_row);

   $(class_row+" .save-data").each(function(ix,objx){
     let sVal = getWDataCompValue(objx);
     let sOData = getWODataComp(objx);



/*
     if($(objx).attr('data-isrequire') == '1' && sVal==''){
       flag_valid = 0;
       $(objx).notify("กรุณากรอกข้อมูล","warn");
     }
     */
  //   let sOData = $(objx).attr('data-odata');
     if(sVal != sOData){
       if(!checkValidData(objx)){
         $(objx).notify("กรุณาตรวจสอบข้อมูล","warn");
         flag_valid = 0;
       }
       else{
         // rowid:dataid:datavalue
         let dataObj = $(objx).parents('.d-row').attr('data-rowid');
         dataObj += ":"+$(objx).attr('data-id');
         dataObj += ":"+encodeURIComponent(sVal);
        // dataObj += ":"+getShowText(sVal);
        // dataObj += ":"+encodeURIComponent(getShowText(sVal));

         lst_data_obj.push(dataObj);
         flag_update = true;
       }
      // console.log("save2: "+$(objx).attr('data-id')+" : "+sVal+" / "+sOData);
     }
   });

   if(flag_valid == 0){
     btnsave.notify("ข้อมูลไม่ถูกต้อง | Invalid Data.",{position:"l"});
     return;
   }
   else{ // valid data ready to save
     if(lst_data_obj.length == 0){
       btnsave.notify("ข้อมูลไม่เปลี่ยนแปลง | No data changed.",{position:"l"});
       return;
     }
     else{// ready to save
       lst_data_obj = lst_data_obj.filter(onlyUnique);
       var aData = {
           u_mode:"update_log_data",
           uid:sUID,
           formid:sFormid,
           lst_data:lst_data_obj,
           row_id:rowid
           };

       startLoad(btnsave, btnsave.next(".spinner"));
       callAjax("j_form_view_log_a.php",aData,function(rtnObj,aData){
        // callAjax("p_form_view_log_a.php",aData,function(rtnObj,aData){
           endLoad(btnsave, btnsave.next(".spinner"));
           if(rtnObj.res == 1){
             btnsave.notify("DATA SAVED","success");
             btnsave.closest('.d-row').find('.lastupdate').html(rtnObj.lastupdate);

        /*
             aData.lst_data.forEach(function(item){
               let arr_itm = item.split(":");
              // console.log(arr_itm[0]+'/'+arr_itm[1]+'/'+arr_itm[2]);
               let comp = $(".r"+arr_itm[0]+ " .save-data[name="+arr_itm[1]+"]");
               console.log("dataid: "+comp.attr("data-id")+"/"+arr_itm[1]);
               setWODataComp(comp, arr_itm[2]);
             });

    */
             $(".r"+rowid+" .save-data").each(function(ix,objx){
               let sVal = getWDataCompValue(objx);
               setWODataComp(objx, sVal);
             });
             btnsave.hide();
           }
           else{
             btnsave.notify("Fail to save", "error");
           }
       });// call ajax
     }
   }

   if(lst_data_obj.length == 0){
     btnsave.notify("ข้อมูลไม่เปลี่ยนแปลง | No data changed.",{position:"l"});
     return;
   }
   else if(flag_valid == 0){
     btnsave.notify("ข้อมูลไม่ถูกต้อง | Invalid Data.",{position:"l"});
     return;
   }
}



function checkLogRequireIf(rowID){ // hide data and clear value in hidden input
  //console.log("checkLogRequireIf "+rowID);
   aShowif.forEach(function(objx) {
    let sObj = objx.split(":");
    //console.log("enter: "+sObj[1]+"/"+$(".save-data[data-id='"+sObj[1]+"']").prop("tagName"));
    //  console.log(" - "+sObj[0]+":"+sObj[1]+":"+sObj[2]+" | cur val: "+cur_parent_value); // data_id:data_parent_id:data_parent_value
    let curParentObj = $(".d-row[data-rowid='"+rowID+"']").find(".save-data[data-id='"+sObj[1]+"']");
    let curObj = $(".d-row[data-rowid='"+rowID+"']").find(".save-data[data-id='"+sObj[0]+"']");
    let curObjDiv = $(".d-row[data-rowid='"+rowID+"']").find('#'+sObj[0]);
    let cur_parent_value = getWDataCompValue(curParentObj);
  //  console.log(" - "+sObj[0]+":"+sObj[1]+":"+sObj[2]+" | cur val: "+cur_parent_value); // data_id:data_parent_id:data_parent_value
    if(cur_parent_value == sObj[2]){ //trigger rule  , show data child
      //console.log(" showdatachild "+sObj[0]+"/"+sObj[1]+"/"+sObj[2]);
      curObjDiv.show();
    }else{ // hide and clear data child
      curObjDiv.hide();
      if(curObjDiv.hasClass('data-item')){
        if(curObj.prop("tagName")){
          if(curObj.prop("tagName").toUpperCase() == "INPUT"){
            let data_type = curObj.attr("type").toUpperCase();
            if(data_type=="RADIO" || data_type=="CHECKBOX"){
              curObj.prop("checked",false);
            }
            else if(data_type=="SELECT"){
              curObj.val('');
            }
            else{
              curObj.val("");
            }
          }

          else{ // txt area etc.
            curObj.val("");
          }
        }
        else if(curObj.prop("tagName")){
          //console.log('data-save-file : '+sObj[0]+" / "+$(".save-data-file[data-id='"+sObj[0]+"']").prop("tagName"));
        }


      }
    }//else
  });

}//checkLogRequireIf






</script>
