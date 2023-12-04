<?


$sUID = getQS("uid");
$sProjid = getQS("projid");
$sGroupid = getQS("groupid");


include("in_db_conn.php");

$query = "SELECT  PP.protocol_id, FL.form_id, FL.form_name_th ,
PP.protocol_version
FROM p_project_uid_list PUL
JOIN i_project_protocol PP ON (PUL.proj_id=PP.proj_id AND (PP.group_id=PUL.proj_group_id OR PP.group_id='') )
JOIN i_protocol_form PF ON (PF.protocol_id=PP.protocol_id)
JOIN p_form_list FL ON FL.is_log=1 AND FL.form_id=PF.form_id
WHERE PUL.uid=? AND PUL.proj_id=? AND PUL.proj_group_id=?
and PP.start_date <= now()
and PP.stop_date >= now()
ORDER BY PF.form_seq
";

//error_log("$sUID, $sProjid, $sGroupid / $query");
$stmt = $mysqli->prepare($query);
$stmt->bind_param('sss', $sUID, $sProjid, $sGroupid);

$txt_logform_row = "";
if($stmt->execute()){
	$result = $stmt->get_result();
	while($row = $result->fetch_assoc()) {
		$txt_logform_row .= addRowLogForm($row['form_id'], $row['form_name_th'], $row["protocol_version"]);

	} // while


}
if($txt_logform_row != ''){
	$txt_logform_row = "<div class = 'fl-fix ph30 ptxt-b ptxt-s12 pt-2'><i class='fas fa-angle-right fa-lg'></i> LOG FORM</div>
   <div class = 'fl-fill fl-auto'>$txt_logform_row</div>
	";
}
echo $txt_logform_row;


function addRowLogForm($formid, $formname, $protocol_version){
	$txtrow = "
			<div class='fl-wrap-col pw200 bg-ssoft1  ptxt-s8 p-row'>
					<div class='fl-fix ph25 v-mid px-1 ptxt-b pbtn view-form-log' data-formid='$formid' data-formname='$formname'>
							 $formname
					</div>
					<div class='fl-wrap-row ph10 bg-ssoft2 '>
						<div class='fl-fix v-mid pw100'>
								 V. $protocol_version
						</div>
					</div>
			</div>
	";
  return $txtrow;
}


?>



<script>

$(document).ready(function(){

});

</script>
