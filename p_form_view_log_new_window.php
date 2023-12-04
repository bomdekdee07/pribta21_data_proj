<?

include_once("in_session.php");
include_once("in_db_conn.php");
include_once("in_php_function.php");

$uid = getQS("uid");
$form_id = getQS("formid");
$sRowid = getQS("rowid");
$s_id = getSS("s_id");

$lang = getQS("lang");



$show_data_id = getQS("show_data_id");

if($lang == "") $lang="th"; // thai default language
$form_row_height = '50';

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
DSL.data_value, DSL.data_name_th, DSL.data_name_en
FROM p_data_sub_list DSL, p_form_list_data FLD
LEFT JOIN p_data_list DL ON (FLD.data_id=DL.data_id)
WHERE FLD.form_id=? AND DSL.data_id=FLD.data_id
ORDER BY DSL.data_id, DSL.data_seq
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$form_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_id, $data_type, $data_value, $data_name_th, $data_name_en);
  while ($stmt->fetch()) {
      if($data_type == "dropdown"){
        if(!isset($d_sub_item[$data_id] ))  $d_sub_item[$data_id] = "";
        $d_sub_item[$data_id] .= "<option class='lang_th' value='$data_value' $data_id=''>$data_name_th </option>";
    //    $d_sub_item[$data_id] .= "<option class='lang_en' value='$data_value' style:'display:none;'>$data_name_en </option>";

      }
      else if($data_type == "radio"){
        if(!isset($d_sub_item[$data_id] ))  $d_sub_item[$data_id] = "";

        $optalign = isset($d_itm_prop[$data_id]['optalign'] )?$d_itm_prop[$data_id]['optalign']:"H";
        $optalign = ($optalign == "H")?"style='margin-right:5px;'":"";

        $d_sub_item[$data_id] .= "<label $optalign><input type='radio' class='save-data v-radio' id='$data_id-$data_value' name='$data_id' data-id='$data_id' data-odata=''  value='$data_value' /> $data_name_th </label><br>";

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

$query = "SELECT  FLD.data_seq, FLD.data_id, FLD.data_type, FLD.data_value,FLD.is_require,
DL.data_question_$lang as data_desc, DL.data_prefix_$lang as prefix, DL.data_suffix_$lang as suffix
FROM p_form_list_data FLD
LEFT JOIN  p_data_list DL ON (FLD.data_id=DL.data_id)
WHERE FLD.form_id=? and DL.data_type <> 'colhead'
ORDER BY FLD.data_seq
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s',$form_id);
//echo "$form_id / query : $query";
if($stmt->execute()){


  $stmt->bind_result($data_seq, $data_id,  $data_type, $data_value, $is_require,
  $data_desc, $prefix, $suffix
  );
  $width = "";
  while ($stmt->fetch()) {
      $txt_col_data = "";

      /*
      $width = isset($d_itm_prop[$data_id]['width'])?$d_itm_prop[$data_id]['width']:"";
      $width = ($width !="")?"width:".$width."px;":"width:90%";
*/
      $width = "width:90%";
      $placeholder =isset($d_itm_prop[$data_id]['placeholder'])?$d_itm_prop[$data_id]['placeholder']:"";
      $placeholder = "placeholder='".(($placeholder !="")?$placeholder:$data_desc)."'";
//echo"<br>$data_type : $data_id";

        $special_data_class = "";
        $color_require = ($is_require == '1')?"pbg-yellow":"";
        if(isset($d_itm_prop[$data_id]['hideprefixsuffix'])){
          if($d_itm_prop[$data_id]['hideprefixsuffix'] == '1'){
            $prefix=""; $suffix="";
          }
        }




        //$txt_col_data .= "<div class='fl-float ph20 col-data'> ";
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
          $txt_col_data .= "<SELECT  name='$data_id' data-odata='' data-id='$data_id' data-isrequire='$is_require' class='save-data $color_require $special_data_class' style='width:100%'>";
          $txt_col_data .= "<option  value='' >เลือก [$data_desc]</option>";
          $txt_col_data .= isset($d_sub_item[$data_id])?$d_sub_item[$data_id]:"";
          $txt_col_data .= "</SELECT>";
        }
        else if($data_type == "radio"){
          $optalign = isset($d_itm_prop[$data_id]['optalign'])?$d_itm_prop[$data_id]['optalign']:"H";
          if(isset($d_sub_item[$data_id])){
            $txt_col_data .= $d_sub_item[$data_id];
          }

        }//radio
        else if($data_type == "textarea"){
          $ta_row ="rows='".(isset($d_itm_prop[$data_id]['row'])?$d_itm_prop[$data_id]['row']:"2")."'";
          $ta_col ="cols='".(isset($d_itm_prop[$data_id]['col'])?$d_itm_prop[$data_id]['col']:"4")."'";

          $txt_col_data .= "<textarea  data-id='$data_id' data-odata='' data-isrequire='$is_require' class='save-data v-text $color_require $special_data_class' width='100%' $ta_row $ta_col $placeholder name='$data_id'></textarea>";
        }//textarea

        $txt_col_data .= " $suffix";

    $txt_row_data .= "
    <div class='fl-wrap-row p-row ' style='min-height:50px;'>
         <div class='fl-fix pw400 bg-msoft3'>$data_desc</div>
         <div class='fl-fill bg-msoft1'>$txt_col_data</div>
    </div>
    ";

     //echo "<br> $data_desc / $txt_col_data";
  }// while
  //$txt_row_data = str_replace("[$cur_colhead]",$txt_col_data,$txt_row_data);
}
else{
  $msg_error .= $stmt->error;
  error_log("mnu_form_view_log: ".$msg_error);
}
$stmt->close();


//retrive dataID
$arr_data_result = array();
$query = "SELECT DR.data_id, DL.data_type,  DR.data_result, DLR.row_id
FROM p_data_log_row DLR, p_data_result DR, p_data_list as DL, p_form_list_data FLD
WHERE DLR.uid=? AND DLR.form_id=? AND DLR.row_id=? AND DR.data_id=DL.data_id
AND DLR.form_id = FLD.form_id AND FLD.data_id=DR.data_id
AND DR.uid=DLR.uid AND DR.collect_date= DLR.collect_date AND DR.collect_time= DLR.collect_time
ORDER BY DLR.row_id
";
//echo "$uid, $form_id / $query";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('sss',$uid, $form_id, $sRowid);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($data_id, $data_type, $data_result,  $row_id);
  while ($stmt->fetch()) {
    if(!isset($arr_data_result[$row_id])) $arr_data_result[$row_id] = array();
    $arr_data_result[$row_id][]="$data_type:$data_id:$data_result";
  }
}
//print_r($arr_data_result);

$txtrow = "";
$query = "SELECT DLR.row_id,
(select max(DR.lastupdate) from p_data_result DR where
DR.uid=DLR.uid AND DR.collect_date=DLR.collect_date AND DR.collect_time=DLR.collect_time
AND DR.data_id IN(select data_id from p_form_list_data where form_id=? )
) as lastupdate
FROM p_data_log_row DLR
WHERE DLR.uid=? AND DLR.form_id=? AND DLR.row_id=?
ORDER BY DLR.row_id DESC
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('ssss',$form_id, $uid, $form_id, $sRowid);
//echo "$uid, $form_id / query : $query";
if($stmt->execute()){
  $stmt->bind_result($row_id, $lastupdate);

  while ($stmt->fetch()) {
    if(isset($arr_data_result[$row_id])){

      foreach($arr_data_result[$row_id] as $data_itm){
        $arr_data_itm = explode(':',$data_itm, 3); // 0=data_type, 1=data_id, 2=data_result
        //echo "<br>row: ".$arr_data_itm[1]."/".$arr_data_itm[0]."/".$arr_data_itm[2];
        $arr_data_itm[2] = htmlentities($arr_data_itm[2], ENT_QUOTES);
        $txt_to_replace = ""; $txt_replace="";
        if($arr_data_itm[0] == "text" || $arr_data_itm[0] == "date" || $arr_data_itm[0] == "number"){
          $txt_to_replace = "name='".$arr_data_itm[1]."'";
          $txtreplace = "name='".$arr_data_itm[1]."' VALUE='".$arr_data_itm[2]."' ";
        }
        else if($arr_data_itm[0] == "dropdown"){
          //$txt_to_replace = "value='".$arr_data_itm[2]."'";
          //echo "<br>".$arr_data_itm[1]."  val:".$arr_data_itm[2];
          $txt_to_replace = "'".$arr_data_itm[2]."' ".$arr_data_itm[1];
          $txtreplace = $txt_to_replace." selected ";
          //echo "<br>$txt_to_replace / $txtreplace";
        }
        else if($arr_data_itm[0] == "radio"){
        //  $txt_row_data = str_replace(" id='".$arr_data_itm[1]," id='".$row_id.$arr_data_itm[1], $txt_row_data);
          $txt_to_replace = " id='".$arr_data_itm[1]."-".$arr_data_itm[2]."'";
          $txtreplace = $txt_to_replace." checked ";
        //  echo "<br>$row_id: ".$arr_data_itm[1]." value: ".$arr_data_itm[2];

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
       $txt_row_data = str_replace($txt_to_replace,$txtreplace,$txt_row_data);

      }// foreach

    }

  }//while
}

$stmt->close();


// all rowid in select option
$sel_rowid_opt = "";
$query = "SELECT DLR.row_id
FROM p_data_log_row DLR
WHERE DLR.uid=? AND DLR.form_id=?
ORDER BY DLR.row_id
";
//echo "$uid, $form_id / $query";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('ss',$uid, $form_id);
//echo "query : $query";
if($stmt->execute()){
  $stmt->bind_result($row_id);
  while ($stmt->fetch()) {
    $sel_rowid_opt .= "<option value='$row_id'>$row_id</option>";
  }
}



$mysqli->close();


if($lastupdate != NULL){
  $lastupdate = "(Last update: $lastupdate)" ;
}


$txt_row_data = "
<div class='fl-wrap-col al-left div-log-info-new-window'  data-rowid='$sRowid' data-uid='$uid' data-formid='$form_id'>
  <div class='fl-fix fl-mid ph30 bg-mdark1 ptxt-white ptxt-s14'>
    <div class='fl-wrap-col fl-fill'>#$sRowid [$uid] $form_id  </div>
    <div class='fl-wrap-col fl-fix pw80'>Goto #:</div>
    <div class='fl-wrap-col fl-fix pw50'> <select class='sel_rowid'>$sel_rowid_opt</select></div>

  </div>
  <div class='fl-fill fl-auto '>
    $txt_row_data
  </div>
  <div class='fl-wrap-row ph50 bg-mdark1 ptxt-white '>
    <div class='fl-wrap-col pw50' ></div>
    <div class='fl-wrap-col fl-fill fl-mid pbtn pbtn-ok ptxt-s14 btn-save-row' >บันทึกข้อมูล / SAVE DATA <span class='lastupdate_save'> $lastupdate </span></div>
    <div class='fl-wrap-col fl-fill fl-mid ptxt-s14 spinner' style='display:none;'>กำลังบันทึกข้อมูล | Data saving.</div>
    <div class='fl-wrap-col pw50' ></div>
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
  border-bottom:1px solid grey;
}
.d-row:hover{
/*	filter:brightness(80%); */
/*  font-weight: bold; */
  color:black;
  background-color: #EEE;
}
</style>


<? echo $txt_row_data; ?>


<script>

setDlgResult('');

$(document).ready(function(){
// initialize data ////////


$(".sel_rowid").val($('.div-log-info-new-window').attr('data-rowid'));
// change to th date
$(".div-log-info-new-window .v-date-th").each(function(ix,objx){
  $(objx).val(changeEn2ThDate($(objx).val()));

});

  $(".div-log-info-new-window .save-data").each(function(ix,objx){
    let sVal = getWDataCompValue(objx);
    let sOData = getWODataComp(objx);
    //console.log("checkdata: "+$(objx).attr("name")+"/"+sVal+"/"+sOData);
    setWODataComp(objx, sVal);
  });

  $('.div-log-info-new-window .ttl-row').html($('.d-row').length);
//*************************
$(".div-log-info-new-window .sel_rowid").off('change');
$(".div-log-info-new-window").on("change",".sel_rowid",function(){
   let rowid =$(this).val();
   let sUID = $('.div-log-info-new-window').attr('data-uid');
   let sFormid = $('.div-log-info-new-window').attr('data-formid');

   let sUrl = "p_form_view_log_new_window.php?uid="+sUID+"&formid="+sFormid+"&rowid="+rowid;
   $('.div-log-info-new-window').parent().load(sUrl);
   //console.log("sel row "+rowid);

});

$(".div-log-info-new-window").on("change",".save-data",function(){
  objx = $(this);
  let sVal = getWDataCompValue(objx);
  let sOData = getWODataComp(objx);
});


$('.div-log-info-new-window .btn-save-row').on("click",function(){ // save row button

    saveLogData($(this));
});


  // date picker
  $('.div-log-info-new-window').on('click', '.v-date', function(){
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
    $('.div-log-info-new-window').on('click', '.v-date-th', function(){
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
    $('.div-log-info-new-window').on('focusout', '.v-date-th', function(){
      if($(this).val().trim() != 'dd/mm/yyyy'){
        if(!validateDateTH($(this).val())){
          if($(this).hasClass('v-date-partial')){
          }
          else{
            $(this).notify("วันที่ไม่ถูกต้อง | Invalid Date "+$(this).val(), "error");
          }
        }
      }
    });




  $('.div-log-info-new-window').on('keydown', '.v-number', function() {
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





  $(".div-log-info-new-window").on("dblclick",".v-radio",function(){
    //console.log('dblclick: '+$(this).attr("name"));
    if($(this).is(':checked')){
      let name = $(this).attr("name");
      $('INPUT[name="'+name+'"]').prop('checked', false);
      checkRequireIf();
      checkHideIf();
    }
  });



  function checkDataChanged(){
    let flag = false;
    $(".div-log-info-new-window .save-data").each(function(ix,objx){
      let sVal = getWDataCompValue(objx);
      let sOData = getWODataComp(objx);

      if(sVal != sOData){
        flag = true;
        return false;
      }
    });

    if(flag){
      if(confirm('ข้อมูลมีการเปลี่ยนแปลง ท่านต้องการบันทึกหรือไม่')){
        saveLogData($('.btn-save-row'));
      }
    }


  }


  function saveLogData(btnsave){

    let sUID = $('.div-log-info-new-window').attr('data-uid');
    let sFormid = $('.div-log-info-new-window').attr('data-formid');
    let sRowid = $('.div-log-info-new-window').attr('data-rowid');
    let flag_valid = 1;

     let lst_data_obj = [];


     $(".div-log-info-new-window .save-data").each(function(ix,objx){
       let sVal = getWDataCompValue(objx);
       let sOData = getWODataComp(objx);
  //console.log("check change data: "+sVal+"/"+sOData);
       if(sVal != sOData){
         if(!checkValidData(objx)){
           $(objx).notify("กรุณาตรวจสอบข้อมูล","warn");
           flag_valid = 0;
         }
         else{
           // rowid:dataid:datavalue
           let dataObj = sRowid;
           dataObj += ":"+$(objx).attr('data-id');
           dataObj += ":"+encodeURIComponent(sVal);

           lst_data_obj.push(dataObj);
           flag_update = true;
         }
        // console.log("save2: "+$(objx).attr('data-id')+" : "+sVal+" / "+sOData);
       }
     });

     if(flag_valid == 0){
        $.notify("ข้อมูลไม่ถูกต้อง | Invalid Data.", "error");
       return;
     }
     else{ // valid data ready to save
       if(lst_data_obj.length == 0){
         $.notify("ข้อมูลไม่เปลี่ยนแปลง | No data changed.", "info");
         return;
       }
       else{// ready to save
         lst_data_obj = lst_data_obj.filter(onlyUnique);
         var aData = {
             u_mode:"update_log_data",
             uid:sUID,
             formid:sFormid,
             lst_data:lst_data_obj,
             row_id:sRowid
             };

         startLoad(btnsave, btnsave.next(".spinner"));
         callAjax("j_form_view_log_a.php",aData,function(rtnObj,aData){
          // callAjax("p_form_view_log_a.php",aData,function(rtnObj,aData){
             endLoad(btnsave, btnsave.next(".spinner"));
             if(rtnObj.res == 1){
               $.notify("DATA SAVED","success");
               $('.lastupdate_save').html("Lastupdate: "+rtnObj.lastupdate);

               setDlgResult('datachange');
               $(".div-log-info-new-window .save-data").each(function(ix,objx){
                 let sVal = getWDataCompValue(objx);
                 setWODataComp(objx, sVal);
               });
             }
             else{
               $.notify("Fail to save", "error");
             }
         });// call ajax
       }
     }

     if(lst_data_obj.length == 0){
       $.notify("ข้อมูลไม่เปลี่ยนแปลง | No data changed.", "info");
       return;
     }
     else if(flag_valid == 0){
       $.notify("ข้อมูลไม่ถูกต้อง | Invalid Data.", "error");
       return;
     }
  }



});



</script>
