<? include("in_session.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>COVID 19 QA System by IHRI</title>
<?
	include("in_head_script.php");
	include_once("in_php_function.php");
	$sPrefix='CV';
	$sProjId='COV19';
?>
</head>
<style>
	.nobody-uid{
		font-size:smaller;
		min-width:100px;
	}
</style>
<body id='pribtaBody' style='min-width: 1024px' >
	<div id='pribta21' class='mainbody fl-wrap-col'>
		<div class='fl-wrap-row main-pchart'>
			<div class='fl-wrap-col left-bar' style='max-width:200px'>
				<div class='fl-wrap-row fl-mid' style='max-height: 30px'>
					<div class='fl-fill' ><span class='fabtn roundcorner' style='padding:5px;background-color:lime;color:green;border:1px solid silver;'><i class=' fa fa-plus-square'>New Case</i></span></div>
				</div>
				<div class='fl-wrap-row fl-mid' style='max-height: 30px'>
					<div class='fl-fill'><input class='fill-box' id='txtSearchId' data-prefix='<? echo($sPrefix); ?>' style='margin:3px' /></div>
					<div class='fl-fix' style='min-width:30px;max-width:30px;'><i id='btnSearch' data-projid='<? echo($sProjId); ?>' class='fabtn fas fa-search'></i><i id='btnSearch-load' class='fa fa-spinner fa-spin' style='display:none'></i></div>
				</div>
				<div id='divPFindResult' class='fl-wrap-row'>

				</div>
				<div id='divPFindResult-loader' class='fl-wrap-row' style='display:none'>
					<i class='fa fa-spinner fa-spin fa-4x'></i>
				</div>
			</div>
			<div class='fl-fix toggle-bar'>
				<i class="fas fa-caret-left"></i>
			</div>
			<div class='fl-wrap-col right-bar'>
				
			</div>
			<div class='fl-wrap-col right-bar-load' style='display:none'>
				<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
			</div>
		</div>
	</div>
</body>
<script>


$(document).ready(function(){
	$("#btnSearch").on("click",function(){
		sTxt = $("#txtSearchId").val().trim();
		if(sTxt=="") return;
		sProjId=$(this).attr("data-projid");
		startLoad($("#btnSearch,#divPFindResult"),$("#btnSearch-load,#divPFindResult-load"));
		$("#divPFindResult").load("nobody_search_result.php?kw="+sTxt+"&projid="+sProjId,function(){
			endLoad($("#btnSearch,#divPFindResult"),$("#btnSearch-load,#divPFindResult-load"));
		});
	});
});


</script>
</html>