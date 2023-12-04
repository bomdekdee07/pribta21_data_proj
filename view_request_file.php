<?
	include_once("in_php_function.php");
	include("in_db_conn.php");

	$sReqId=getQS("request_id");
	$sHtml="";
	$query ="SELECT request_id,file_title,file_name,updated_datetime,updated_by,original_filename,s_name FROM i_stock_request_file ISRF
		LEFT JOIN p_staff PS
		ON PS.s_id = ISRF.updated_by
		WHERE request_id=?";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sReqId);
	if($stmt->execute()){
	  $stmt->bind_result($request_id,$file_title,$file_name,$updated_datetime,$updated_by,$original_filename,$s_name); 
	  while ($stmt->fetch()) {
	  	//<a href='supply_files/".$file_name."' target='_blank'><i class='fas fa-search fa-2x'></i></a>
	  	$sHtml.="<div class='fabtn btnviewreqfile fl-wrap-row h-40 row-color row-hover' data-reqfile='".$file_name."'>
	  		<div class='fl-fix w-30 fl-mid'>
	  			<i class='fas fa-search fa-2x'></i>
	  		</div>
	  		<div class='fl-wrap-col'>
	  			<div class='fl-fill lh-15 fs-small'>$file_title</div>
	  			<div class='fl-fill lh-15 fs-xsmall' title='On : ".$updated_datetime."'>By : $s_name</div>
	  		</div>
	  		
	  	</div>";
	  }
	}
?>

<div id='divVRF' class='fl-wrap-row'>
	<div  class='fl-wrap-col w-200 fl-auto'>
		<? echo($sHtml); ?>
	</div>
	<div id='divViewFile' class='fl-wrap-col fl-mid'>
		<? include("view_file.php"); ?>
	</div>
</div>

<script type="text/javascript">
	$(function(){
		$("#divVRF .btnviewreqfile").off("click");
		$("#divVRF").on("click",".btnviewreqfile",function(){
			$("#divVRF .btn-selected").removeClass("btn-selected");
			$(this).addClass("btn-selected");
			sFile = $(this).attr("data-reqfile");
			$("#divVRF #divViewFile").load("view_file.php?file=supply_files/"+sFile);
		});
	});
</script>