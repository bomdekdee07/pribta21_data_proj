<?

include_once("in_php_function.php");
include("in_db_conn.php");

$sUID = getQS("uid");
$sProjid = getQS("projid");
$codeProjshortcut = "

";

?>
<link rel="stylesheet" href="assets/css/themeclinic1.css">
<style>

.proj-main .q-row:hover{
	filter:brightness(80%);
	cursor: pointer;
}

.main-left-bar{
/*	background-color:#88C4FF; */
	background-color:#EEE;

}
.main-right-bar{
	background-color:#D2FFFF;
}

.proj-search-schedule{
	background-color:#D2FFFF;
}
.proj-search-uid{
	background-color:#89F4FA;
}

.proj-main .btn-main-menu{
	padding:10px;
	min-width:100px;
	max-width:150px;
	border: 2px solid white;
	min-height:50px;
	max-height:100px;
	margin:2px;
	margin: 2px 8px;
	border-radius: 8px;
	font-weight: bold;

  color: white;
	background-color:#007FFF;
	align-items: center;
}

.proj-main .btn-main-menu:hover{
	filter:brightness(80%);
	cursor: pointer;
}

.proj-main .toggle-bar{
/*	background-color:#00468C; */
  background-color:#D00000;
}
.proj-main .toggle-bar i{
	color:white;
}

.proj-main .proj-head{
	padding-left: 20px;
	margin: 0px;
	max-height:55px;
	color:#007B7B;
	background-color:#00D9D9;
	align-items: center;
}
.proj-main .proj-head-txt{
	font-size:20px;
	font-weight: bold;
}

</style>


<div class='fl-wrap-row proj-main' >
	<div class='fl-wrap-col left-bar main-left-bar w-xl'>

   <div class="fl-fix btn-main-menu" data-id='proj_list' style="margin-top:20px;">
      <i class="fas fa-clipboard-list fa-lg"></i> Project List
	 </div>
	 <div class="fl-fix btn-main-menu" data-id='proj_regis_uid'>
      <i class="fas fa-user-circle fa-lg"></i> Register UID
	 </div>


   <div class='fl-wrap-row pt-2'>
		 <div class='fl-wrap-col proj-main-search-uid' style='max-width:190px '>

 			<div class='fl-wrap-row row60'>
 			 <div class='fl-wrap-col fl-fill pl-2'>
 				 <div class='proj-label'>Search By UID/UIC/PID:</div>
 			   <input type='text' class='inpproj' id='sMainSearchUID' >
 			 </div>
 			 <div class='fl-wrap-col pl-1 w-ss' >
 				 <div class='proj-label-btn'>.</div>
 					<i class='fas fa-search fa-lg p-btnsearch' id='btnMainSearchUID' title='Search'></i>
 			 </div>

 			</div>
			<div class='fl-wrap-row '>
				<div class='fl-wrap-col fl-auto proj-search-uid-main' >

				</div>
				<div class='fl-wrap-col proj-search-uid-main-load' style="display:none;">
					<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
				</div>
			</div>



 		</div>
	 </div>


		<? // include("project_main_inc_menu_left.php"); ?>
	</div>
	<div class='fl-fix toggle-bar'>
		<i class="fas fa-caret-left"></i>
	</div>
	<div class='fl-wrap-col right-bar main-right-bar'>
		<div class="fl-wrap-col p-proj-page" id="proj_list_page">
			<? include("project_inc_proj_list.php"); ?>
		</div>
		<div class="fl-wrap-col p-proj-page" id="proj_detail_page" style="display:none;"></div>
		<div class="fl-wrap-col p-proj-page" id="proj_detail_uid_visit_page" style="display:none;"></div>
		<div class="fl-wrap-col p-proj-page" id="proj_regis_uid_page" style="display:none;"></div>

	</div>
	<div class='fl-wrap-col right-bar-load' style='display:none'>
		<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
	</div>
</div>

<script>
var cur_proj_choice = "proj_list";
$(document).ready(function(){
	//$(document).on('keydown', '.input-decimal', function() {

  $(".proj-main .btn-main-menu").unbind("click");
	$(".proj-main").on("click",".btn-main-menu",function(){
      choice=$(this).attr("data-id");
			//console.log("choice: "+choice);
			if(choice == "proj_list"){
				loadLinkProj("proj_list", "project_inc_proj_list.php");
				cur_proj_choice = "proj_list";
			}
			else if(choice == "proj_regis_uid"){
				loadLinkProj("proj_regis_uid", "proj_regis_main.php");
				cur_proj_choice = "proj_regis_uid";
			}
	});

	$(".proj-main").on("click","#btnMainSearchUID",function(){
			if($("#sMainSearchUID").val().trim().length > 2){
				sUrl="project_inc_search_uid_mainmenu.php?txtsearch="+$("#sMainSearchUID").val().trim();
				loadLink(sUrl, $(".proj-search-uid-main"), $(".proj-search-uid-main-load"));
			}
			else{
				$("#sMainSearchUID").notify("Please insert at least 3 charecters.", "info");
			}

	});
	$(".proj-main").on("keypress","#sMainSearchUID",function(e){
		if(e.which == 13) {
			$(".proj-main, #btnMainSearchUID").click();
		}
	});

	$(document).on("click",".proj-list .proj-list-itm",function(){
		projid=$(this).attr("data-projid");
		sUrl="project_inc_proj_detail.php?projid="+projid;
		loadLinkProj("proj_detail", sUrl);
		cur_proj_choice = "proj_detail";

	});

	$(document).on("click",".proj-current-close",function(){
		$('.p-proj-page').hide();
	//	console.log("cur_proj_choice: "+cur_proj_choice);
		let flag_default_page = 1;
		if(cur_proj_choice != ""){
			if(cur_proj_choice != $(this).attr("data-page")){
				$('#'+cur_proj_choice+"_page").show();
				flag_default_page = 0;
			}
		}
		/*
		else{ // default page to show (dashboard is prefered)
			$("#proj_list_page").show();
		}
		*/
// default page to show (dashboard is prefered)
		if(flag_default_page == 1) $("#proj_list_page").show();


	});

	$(document).on("click",".view-visit",function(){
		if($(this).hasClass('pbtn-cancel')){
			$(this).notify($(this).attr("data-uid")+ ": PID has been cancelled.", "error");
			return;
		}

		viewUIDvisit(
		$(this).attr("data-uid"),
		$(this).attr("data-projid"),
		$(this).attr("data-groupid"));
	});

});


function loadLinkProj(pageChoice, sUrl){
	//console.log(pageChoice+"-loadlink99: "+sUrl);

	$(".proj-main .right-bar").hide();
	$(".proj-main .right-bar-load").show();

	$('#'+pageChoice+"_page").html("");
	$('#'+pageChoice+"_page").load(sUrl,function(){
		$('.p-proj-page').hide();  $('#'+pageChoice+"_page").show();
		$(".proj-main .right-bar").show();
		$(".proj-main .right-bar-load").hide();
	});

	if(pageChoice == "proj_detail") cur_proj_choice = "proj_detail";
}

function loadLink(sUrl, selector_dest, selector_loader){

//	console.log("loadlink99: "+sUrl);
	startLoad(selector_dest,selector_loader);
	selector_dest.html("");
	selector_dest.load(sUrl,function(){
		endLoad(selector_dest,selector_loader);
	});
}

function viewUIDvisit(uid, projid, groupid){
	//console.log("viewUIDvisit:"+uid+"/"+projid+"/"+groupid);
	sUrl="project_inc_uid_visit.php?projid="+projid+"&groupid="+groupid+"&uid="+uid;
	loadLinkProj("proj_detail_uid_visit", sUrl);
}

function createVisitSchedule(sUID, sProjid, sGroupid){
//	console.log('createVisitSchedule '+sUID+'/'+sProjid+'/'+sGroupid);
	var aData = {
		  u_mode:"create_schedule_visit",
			uid:sUID,
			projid: sProjid,
			groupid: sGroupid
	};
	//console.log('createVisitSchedule2 '+sUID+'/'+sProjid+'/'+sGroupid);
	callAjax("project_a_visit.php",aData,function(rtnObj,aData){
//		endLoad(btnclick, btnclick.next(".fa-spinner"));
		if(rtnObj.res == 1){
			$.notify("Create schedule visit for PID: "+aData.uid+" / Proj: "+aData.projid, "success");
		  closeDlg();
			viewUIDvisit(aData.uid, aData.projid, aData.groupid);
		}
		else{
			$.notify("Fail to register new UID.", "error");
		}
	});// call ajax

}

</script>
