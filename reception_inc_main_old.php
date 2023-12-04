<? include_once("in_session.php"); 
	include_once("in_php_function.php");
	$sSID = getSS("s_id");
	if($sSID == ""){

		//Show Login Dialog
		echo("Please login and refresh
			<script>
				showLogin();
			</script>
			");

		exit();
	}
	
	$sToday = date("Y-m-d");
?>

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
<div class='fl-wrap-row' style='background-color: white'>
	<div class='fl-wrap-col w-l left-bar' style='min-width:230px'>
		<div id='divQueueHead' class='fl-wrap-row h-30'>
			<div id='btnScheduleList' class='fabtn fl-fix w-30 fl-mid'><i class=' fas fa-calendar-alt'></i></div>
			<div class='fl-fill'>Q DATE: <? echo($sToday); ?></div>
			<div class='fl-fix w-s'><label><input id='show-all-q' class=' bigcheckbox' type='checkbox' />All</label></div>



		</div>
		<div id='divQList' class='fl-wrap-col fl-auto'>
			<? $_GET["hidename"]=0; $_GET["hidecall"]=1; $_GET["v_mode"]="reception"; include("queue_inc_list.php"); ?>
		</div>

	</div>
	<div class='fl-fix toggle-bar'>

	</div>
	<div class='fl-wrap-col right-bar'>
		<div id='divPInfoIdCard' class='' style='display:flex;'>
			<? $_GET["showq"] = "1"; include("patient_info_idcard.php"); ?>
		</div>
		<div id='divUidSearchResult-loader' class='fl-fix h-150 fl-mid' style='display:none'>
			<i class='fa fa-spinner fa-spin fa-4x'></i>
		</div>
		<div id='divUidSearchResult' class='fl-wrap-col fl-auto'>
		</div>

	</div>
</div>


<script>
	$(function(){

		//Refresh Q LIST
		setInterval(refreshQ, 10000);
		function refreshQ(){
			let vMode = "reception";
			
			$("#divQList").load("queue_inc_list.php?hidename=0&v_mode="+vMode,function(){
				if($("#divQueueHead #show-all-q").is(":checked")){
					$("#divQList .row-notin").show();
				}else{
					$("#divQList .row-notin").hide();
				}


			});
		}

		function startLoad(objCont,objLoad){
			$(objCont).hide();
			$(objLoad).show();
		}

		function endLoad(objCont,objLoad){
			$(objCont).show();
			$(objLoad).hide();
		}


		$("#divQueueHead #btnScheduleList").off("click");
		$("#divQueueHead").on("click","#btnScheduleList",function(){

			//sSchDate = $(this).attr("data-schdate");

			sUrl = "appointments_calendar.php";
			showDialog(sUrl,"ทำนัดหมาย","680","1350","",
			function(sResult){
				//CLose function
				if(sResult=="1"){
				}
			},false,function(){
				//Load Done Function
			});
		});

		$("#divQueueHead #show-all-q").off("change");
		$("#divQueueHead #show-all-q").on("change",function(){
			if($(this).is(":checked")){
				$("#divQList .row-notin").show();
			}else{
				$("#divQList .row-notin").hide();
			}
		});

		$("#divQList .btn-q-no").off("click");
		$("#divQList").on("click",".btn-q-no",function(){
			qRow = $(this).closest(".q-row");
			let sUid = $(qRow).attr('data-uid');
			let sQ = $(qRow).attr('data-queue');

			if(sUid=="" && sQ!=""){
				if($("#divPInfoIdCard #txtQueue").val()==""){
					$("#divPInfoIdCard #txtQueue").val(sQ);
					$("#divPInfoIdCard #txtQueue").trigger("change");

				}else{
					$.notify("Please add UID to this queue before continue");
					$("#divPInfoIdCard #btnClearInput").trigger("click");
					$("#divPInfoIdCard #txtQueue").val(sQ);					
				}

			}else{
				let sUrl = "idiot_q_fwd.php?uid="+sUid+"&q="+sQ;
				showDialog(sUrl,"FWD ส่งคิวต่อไปห้องอื่น","600","1024","","",false,function(){

				});				
			}

		});
		$("#divQList .btn-q-info").off("click");
		$("#divQList").on("click",".btn-q-info",function(){
			qRow = $(this).closest(".q-row");
			let sUid = $(qRow).attr('data-uid');
			let sQ = $(qRow).attr('data-queue');

			if(sUid=="" && sQ!=""){
				$.notify("Please add UID to this queue before continue");
				$("#divPInfoIdCard #btnClearInput").trigger("click");					
				$("#divPInfoIdCard #txtQueue").val(sQ);	
			}else{

				startLoad($("#divPInfoIdCard"),$("#divUidSearchResult-loader"));
				sUrl="patient_info_idcard.php?showq=1&uid="+sUid+"&loadq=1&lockq=1&q="+sQ;
				$("#divPInfoIdCard").load(sUrl,function(){
					endLoad($("#divPInfoIdCard"),$("#divUidSearchResult-loader"));
				});			
			}

		});


		$("#divQList .btnorderlab").off("click");
		$("#divQList").on("click",".btnorderlab",function(){
			qRow = $(this).closest(".q-row");
			sUid = $(qRow).attr('data-uid');
			sQ = $(qRow).attr('data-queue');
			sColD=$(qRow).attr('data-coldate');
			sColT=$(qRow).attr('data-coltime');

			sUrl="lab_order_inc_main.php?"+qsTxt(sUid,sColD,sColT)+"&is_pribta=1&is_doctor=1";
			showDialog(sUrl,"Lab Order "+qsTitle(sUid,sColD,sColT),"99%","99%","",
			function(sResult){
				//resetBill(sUid,sColD,sColT);

				if(sResult=="REFRESH"){
					
				}
			},false,function(){
				//Load Done Function
			});
		});

		/*
		$(".btnaddque").off("click");
		$("#divFindRes").on("click",".btnaddque",function(){
			let sUid = $(this).attr('data-uid');
			let sUrl = "queue_dlg_bind.php?uid="+sUid;
			showDialog(sUrl,"Add Queue to Uid","400","800","",loadComplete,false,function(){
			});
		});
		function loadComplete(){
			
		}
		*/

		$("#divUidSearchResult .btnselectuid").off("click");
		$("#divUidSearchResult").on("click",".btnselectuid",function(){

			sUid = $(this).attr('data-uid');
			startLoad($("#divPInfoIdCard"),$("#divUidSearchResult-loader"));

			sUrl="patient_info_idcard.php?showq=1&uid="+sUid;

			$("#divPInfoIdCard").load(sUrl,function(){
				endLoad($("#divPInfoIdCard"),$("#divUidSearchResult-loader"));
			});
		});



		$("#divPInfoIdCard #btnSearchID").off("click");
		$("#divPInfoIdCard").on("click","#btnSearchID",function(){
			sQS = getUidSearchQS();
			$("#divPInfoIdCard .txt-dup").html("");

			if(sQS!==""){
				startLoad($("#divUidSearchResult,#divPInfoIdCard #btnSearchID,#divPInfoIdCard #btnRegisterUid"),$("#divUidSearchResult-loader"));

				sUrl="patient_inc_search_result.php"+sQS;

				$("#divUidSearchResult").load(sUrl,function(){
					$("#divPInfoIdCard").find("#btnRegisterUid").show();

					if($("#divUidSearchResult .btnselectuid").length){
						if($("#divUidSearchResult .btnselectuid").length == 100){
							$.notify("More than 100 rows found.",{className:"success",autoHideDelay: 500});
						}else{
							$.notify($("#divUidSearchResult .btnselectuid").length + " rows found.",{className:"success",autoHideDelay: 500});	
						}
						
					}else{
						
						
					}
					endLoad($("#divUidSearchResult,#divPInfoIdCard #btnSearchID,#divPInfoIdCard #btnRegisterUid"),$("#divUidSearchResult-loader"));
					$("#divUidSearch #fname").focus();
				});

			}else{
				$("#divPInfoIdCard #btnSearchID").notify("Please enter something.");
			}
		});



	});



</script>
</html>