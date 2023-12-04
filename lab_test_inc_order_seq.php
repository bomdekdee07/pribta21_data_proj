<?
include_once("in_php_function.php");
include("in_db_conn.php");

$query = "SELECT lab_group_name,PLT.lab_id,PLT.lab_group_id,lab_name,lab_unit,lab_seq FROM p_lab_test PLT
	LEFT JOIN p_lab_test_group PLTG
	ON PLTG.lab_group_id = PLT.lab_group_id 
	WHERE lab_method_id != ''
	ORDER BY PLT.lab_group_id,lab_seq";

$stmt = $mysqli->prepare($query);

$sHtml = "";
if($stmt->execute()){
	$stmt->bind_result($lab_group_name,$lab_id,$lab_group_id,$lab_name,$lab_unit,$lab_seq);
	while ($stmt->fetch()) {
		$sHtml .= "
		<div class='fl-wrap-row lab-row h-30 f-border row-hover row-color' data-labid='$lab_id' data-labgroupid='$lab_group_id' >
			<div class='fl-fix w-50 fl-mid'>...</div>
			<div class='fl-fix w-150 fl-mid'><input class='lab-seq w-fill h-25' value='$lab_seq' data-odata='$lab_seq' /></div>
			<div class='fl-fill'>$lab_group_name</div>
			<div class='fl-fix w-150'>$lab_id</div>
			<div class='fl-fill'>$lab_name</div>
			<div class='fl-fix w-100'>$lab_unit</div>
		</div>";
	}	
}

$mysqli->close();

?>
<div id='divLTIOS' class='fl-wrap-col'>
	<div class='fl-wrap-row h-30'>
		<div class='fl-wrap-row h-30 f-border row-header bg-head-1'>
			<div class='fl-fix w-50 fl-mid'>...</div>
			<div class='fl-fix w-150 fl-mid'>Seq#</div>
			<div class='fl-fill'>Group Name</div>
			<div class='fl-fix w-150'>Lab ID</div>
			<div class='fl-fill'>Lab Name</div>
			<div class='fl-fix w-100'>Unit</div>
		</div>

	</div>
	<div class='fl-wrap-col fl-auto'>
		<? echo($sHtml); ?>
	</div>
	<div class='fl-wrap-row h-30 fl-mid'>
		<div id='btnSaveLabSeq' class='fabtn f-border fl-fix w-150 fl-mid'>Save and Refresh</div>
	</div>
</div>


<script>
	$(function(){
		$("#divLTIOS #btnSaveLabSeq").off("click");
		$("#divLTIOS #btnSaveLabSeq").on("click",function(){
			aLabList = new Array();
			$("#divLTIOS .lab-seq").each(function(ix,objx){
				if($(objx).attr('data-odata') != $(objx).val()){
					labId = $(objx).closest(".lab-row").attr('data-labid');
					labGrpId = $(objx).closest(".lab-row").attr('data-labgroupid');
					labSeq = $(objx).val();
					aLabList.push(labId+","+labGrpId+","+labSeq);
				}
			});

			if(aLabList.length==0){
				$.notify("No data changed");
				return;
			}


			sURL = "lab_a.php";
			aData={u_mode:"update_lab_seq",itemlist:aLabList};
	        callAjax(sURL,aData,function(jRes,rData){
				if(jRes.res=="1"){
					$.notify("Data Saved.");
					$("#divLTIOS").parent().load("lab_test_inc_order_seq.php");
					//$("#divLTIOS .lab_seq").attr("data-odata",$("#divLTIOS .lab_seq").val() );
				}else{
					$.notify(jRes.msg);
				}
	        });
		});
	});
</script>