<?

include_once("in_php_function.php");
include("in_db_conn.php");


?>
<link rel="stylesheet" href="assets/css/themeclinic1.css">


<div class='fl-wrap-row div-uid-mgt' >
	<div class='fl-fix left-bar pw150 bg-msoft1' >
		<div class='fl-wrap-row fl-mid ph30 ptxt-s14 ptxt-b ptxt-u' >
				<i class='fa fa-id-card fa-lg'></i> UID Management
		</div>
		<div class='fl-wrap-row fl-mid ph50 bg-mdark3 ptxt-b ptxt-white pbtn div-mnu-page mb-1'
		data-page='uid_data_inc_uid_edit'>
        <i class='fa fa-user-edit fa-lg'></i> UID Edit
		</div>
		<div class='fl-wrap-row fl-mid ph50 bg-mdark3 ptxt-b ptxt-white pbtn div-mnu-page mb-1'
		data-page='uid_data_inc_uid_transfer'>
        <i class='fa fa-people-arrows fa-lg'></i> UID Transfer
		</div>
	</div>
	<div class='fl-fix toggle-bar bg-mdark2'></div>
	<div class='fl-fill bg-msoft3 px-1 py-1 div-uid-mgt-detail' >
		<div class='fl-mid' style='height:100%;'>
			 <i class='fa fa-id-card fa-2x'></i>
       <span class='ptxt-s20 ptxt-b ml-2'>UID Data Management</span>
			 <span class='ptxt-s12 ml-2'>Please select menu</span>
	 </div>
	</div>
	<div class='fl-fill bg-msoft3 px-1 py-1 div-uid-mgt-spinner' style='display:none;'>
		<div class='fl-mid ptxt-s20' style='height:100%;'>
			 <i class='fa fa-spinner fa-spin  fa-5x'></i> Loading
	 </div>
	</div>

</div>



<script>
$(document).ready(function(){
	$(".div-uid-mgt").on("click",".div-mnu-page",function(){

		 //let sUrl = 'project_inc_uid_visit.php?projid=TG&groupid=MTF&uid=P17-05969';
		 let sUrl = $(this).attr('data-page')+'.php';
		 loadLink(sUrl, $('.div-uid-mgt-detail'), $('.div-uid-mgt-spinner'));
	});




});

function loadLink(sUrl, selector_dest, selector_loader){

//	console.log("loadlink99: "+sUrl);
	startLoad(selector_dest,selector_loader);
	selector_dest.html("");
	selector_dest.load(sUrl,function(){
		endLoad(selector_dest,selector_loader);
	});
}

function changeMenu_UIDmgt(){

}


</script>
