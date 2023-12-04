<? include("in_session.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>PRIBTA CLINIC</title>
<?
	include("in_head_script.php");
	include_once("in_php_function.php");
?>
</head>
<style>
	.rowodd{
		background-color: silver;
	}
	.roweven{
		background-color: lightgrey;

	}
	.divtr:hover{
		filter:brightness(80%);
	}
	.fixed {
	    position: fixed;
	    top:0; left:0;
	    width: 100%; 
	}
	.highligh{
		filter:brightness(80%);
		color:red;
	}
</style>
<body id='pribtaBody' >
	<div id='pribta21' class='fl-wrap-col' style='overflow: hidden'>
		<div class='fl-wrap-col fl-fill'  style='overflow: hidden'>
			<div class='fl-fix fl-mid' style='background-color: #00c1d0;min-height:60px;border-bottom:1px solid black'>
				<div class='fl-fix' style='min-width:30px'>
					<i id='btnClearTxt' title='Clear' class='fa fa-trash fa-2x'></i>
				</div>
				<div class='fl-fill'>
					<div>UID/UIC</div>
					<div><input class='uidfinder' data-ftype='uid' style='width:80px' id='txtF_Uid' /></div>
				</div>
				<div class='fl-fill'>
					<div>Name</div>
					<div><input class='uidfinder' data-ftype='name'  id='txtF_Name' /></div>
				</div>
				<div class='fl-fill'>
					<div>DOB</div>
					<div>ค.ศ.<input class='uidfinder' data-ftype='dob'  style='width:80px' id='txtF_DOB' placeholder="YYYY-MM-DD" /></div>
				</div>
				<div class='fl-fill'>
					<div>CitizenId/Passport</div>
					<div><input class='uidfinder' data-ftype='id'  id='txtF_Id' /></div>
				</div>
				<div class='fl-fill'>
					<div>Phone</div>
					<div><input class='uidfinder' data-ftype='phone'  style='width:80px'   id='txtF_Phone' /></div>
				</div>
				<div class='fl-fill'>
					<div>Email</div>
					<div><input class='uidfinder' data-ftype='email'  id='txtF_Email' /></div>
				</div>
				<div class='fl-fix fl-mid' style='min-width:100px'>
					<div><button id='btnFindUid' >Find</button></div>
				</div>
			</div>

			<div id='divFindRes' class='fl-fill fl-auto' style='font-size:12px;'>
				
			</div>
			<div id='imgLoader' class='fl-fill' style='display:none'>
				<img src='assets/image/spinner.gif' />
			</div>
		</div>
	</div>
	<div id='divFullLoader' class='modal' style='display:none'></div>
</body>
<script>
	function startFullLoad(){
		$("#divFullLoader").show();
	}
	function stopFullLoad(){
		$("#divFullLoader").hide();
	}
	$(function(){



		$("#txtF_DOB").datepicker({
			dateFormat:"yy-mm-dd",
			changeYear:true,
			changeMonth:true
		});

		$(".uidfinder").unbind("keypress");
		$(".uidfinder").on("keypress",function(e){
			if(e.which==13){
				$("#btnFindUid").trigger("click");
			}
		});

		$("#btnClearTxt").unbind("click");
		$("#btnClearTxt").on("click",function(){
			$(".uidfinder").val("");
		});

		$("#btnFindUid").unbind("click");
		$("#btnFindUid").on("click",function(){
			
			let sQS="";
			$(".uidfinder").each(function(ix,objx){
				if($(objx).val()!=""){
					fType = $(objx).attr("data-ftype");
					sQS+="&"+fType+"="+$(objx).val();
				}
			});

			if(sQS=="") return;
			startLoad($("#divFindRes"),$("#imgLoader"));
			sQS = encodeURI(sQS);
			sUrl = "patient_inc_result.php?u_mode=find_uid"+sQS;
			$("#divFindRes").load(sUrl,function(){

				endLoad($("#divFindRes"),$("#imgLoader"));
				resetRowColor("#divFindRes .divtable");
			});
		});

		$("#divFindRes").on("mouseenter",".divdatarow",function(){
			$("#divQList tbody tr").removeClass("highligh");
			sUid = $(this).find(".btnaddque").attr('data-uid');
			$("#divQList").find("tbody tr[data-uid='"+sUid+"']").addClass("highligh");
			//alert($("#divQList").find("tbody tr[data-uid='"+sUid+"']").attr("data-uid") );
		});


		function resetRowColor(objTable){
			$(objTable).find(".divtr").removeClass("rowodd");
			$(objTable).find(".divtr").removeClass("roweven");
			$(objTable).find(".divtr:odd").addClass("rowodd");
			$(objTable).find(".divtr:even").addClass("roweven");
		}
	});



</script>
</html>