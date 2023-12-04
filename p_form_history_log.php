<?
include_once("in_db_conn.php");
include_once("in_php_function.php");
include_once("in_php_pop99.php");

$sUid = getQS("uid");
$sColdate = getQS("coldate");
$sColtime = getQS("coltime");
$sFormid = getQS("formid");
$sProj_id = getQS("proj_id");
//echo "form history: $sFormid, $sUid, $sColdate, $sColtime";
$arr_log_result = array();
$arr_log_data_col = array();


$query ="SELECT LDR.*, PS.s_name, PDR.data_result as current_result,
DL.data_name_th
FROM p_form_list_data FLD
JOIN a_log_data_result LDR ON (LDR.data_id=FLD.data_id AND LDR.uid =? AND LDR.collect_date =? AND LDR.collect_time =?)
LEFT JOIN p_staff PS ON PS.s_id=LDR.update_user
LEFT JOIN p_data_list DL ON DL.data_id=LDR.data_id
LEFT JOIN p_data_result PDR ON (PDR.data_id=LDR.data_id AND
PDR.uid=LDR.uid AND PDR.collect_date=LDR.collect_date AND PDR.collect_time=LDR.collect_time  )
WHERE FLD.form_id=?
ORDER BY FLD.data_seq ASC";
// AND PDR.proj_id = ?

$stmt = $mysqli->prepare($query);
$stmt->bind_param('ssss', $sUid, $sColdate, $sColtime, $sFormid);
// $stmt->bind_param('sssss', $sUid, $sColdate, $sColtime, $sFormid, $sProj_id);
//echo "$sID, $sProjid / $query";

if($stmt->execute()){
  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()) {

  //  $arr_log_data_col[$row['data_id']] = $row['current_result'];
    $data_name = $row['data_name_th'];
    if(strlen($data_name) > 150) $data_name = substr($data_name,0,150).'...' ;



    $arr_log_data_col[$row['data_id']] = array('name'=>$data_name, 'result'=>$row['current_result']) ;
    $update_by=$row['update_time']."|".$row['update_user']."|".$row['s_name'];
    if(!isset($arr_log_result[$update_by])) $arr_log_result[$update_by]=array();

    if($row['data_result'] == '') {
      $row['data_result'] = '{Delete}';
    }

    if($row['form_id'] != $sFormid) $row['data_result'] .= ' <b>[Form ID: '.$row['form_id'].']</b>'; 


    $arr_log_result[$update_by][$row['data_id']]= $row['data_result'];

//echo "<br> $update_by :".$row['data_id'];
  }//while
}
$stmt->close();

ksort($arr_log_result);// sorting by update date
$txt_head_update_by = ""; $col_number=0;
foreach($arr_log_result as $update_by=>$data_log ){
  //echo "<br> $update_by: ";
  $arr_update_by = explode("|",$update_by);
  $txt_head_update_by .= "
  <div class='fl-fix fl-mid pw250 border-left-1 bg-mdark2 logcol logcol$col_number'>
       ".$arr_update_by[1]." ".$arr_update_by[2]."<br>".$arr_update_by[0]." <button class='btn-logcol-hide' data-col='$col_number'>Hide</button>
  </div>
  ";

  $col_number++;
}//foreach

$txt_data_col = ""; $txt_data_col_result = "";
foreach($arr_log_data_col as $data_id=>$itm_data ){
   $data_name = $itm_data['name'];
   $data_result = $itm_data['result'];


   $col_number=0;
   $txt_data_col .= "
     <div class='fl-fix ph30 row-color ptxt-b border-left-1 al-left'>
         $data_id <br><span class='ptxt-s8 ptxt-blue'>$data_name</span>
     </div>


   ";

   $txt_data_col_result .= "<div class='fl-wrap-row row-color h-30'>";
   foreach($arr_log_result as $update_by=>$data_log ){
      if(isset($arr_log_result[$update_by][$data_id])){
        $txt_data_col_result .= "
        <div class='fl-fix fl-mid border-left-1 pbtn logdata logcol logcol$col_number'>
            ".$arr_log_result[$update_by][$data_id]."
        </div>
        ";
      }
      else{
        $txt_data_col_result .= "
        <div class='fl-fix fl-mid border-left-1 logcol logcol$col_number'></div>
        ";
      }
      $col_number++;
   }// //foreach update by


         $txt_data_col_result .= "
         <div class='fl-fix pw200 fl-mid pbtn logdata border-left-1 ptxt-b'>
            $data_result
         </div>
         ";

    $txt_data_col_result .= "</div>";



}//foreach data col

echo "
<div id='divHistLog' class='fl-wrap-col div-hist-log fix-header-wrap'>
  <div class='fl-wrap-row ph40 bg-mdark1 ptxt-white ptxt-b ptxt-s10'>
    <div class='fl-fix fl-mid pw200 border-left-1'>
       Data ID
    </div>




    <div class='fl-wrap-row  fl-scroll fix-header-head'>

      $txt_head_update_by
      <div class='fl-wrap-col fl-fix pw200 fl-mid  bg-sdark1  border-left-1'>
         Current Result
      </div>

    </div>

  </div>
  <div class='fl-wrap-row bg-msoft2 ptxt-s10'>
    <div class='fl-wrap-col pw200 fix-header-col'>
         $txt_data_col;
    </div>
    <div class='fl-wrap-col fix-header-body'>
         $txt_data_col_result
    </div>
  </div>
</div>
";

?>

<script>
$(document).ready(function(){

    $("#divHistLog").flFixHeader();

    $(".div-hist-log .btn-logcol-hide").hide();

    $(".div-hist-log .btn-logcol-hide").off("click");
    $(".div-hist-log .btn-logcol-hide").on("click",function(){
        $('.logcol'+$(this).attr('data-col')).hide();
    });
    /*
    $(".div-hist-log .btn-logcol-showall").off("click");
    $(".div-hist-log .btn-logcol-showall").on("click",function(){
        $('.logcol').show();
    });
    */

    $(".div-hist-log .logdata").off("click");
    $(".div-hist-log .logdata").on("click",function(){
        if($(this).html() != ''){
          alert($(this).html());
        }
    });

});
</script>
