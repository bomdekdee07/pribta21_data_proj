<?
	/* Project Thumbnail list  */

	include("in_session.php");
	include_once("in_php_function.php");
	include_once("in_php_pop99.php");

	$sProjid = getQS("projid");

	if(!isset($proj_auth['allow_view'])){
	include_once("project_inc_uid_permission.php");

	//echo "module: ".$module_auth['S02:PROJ_USER_MGT:OS']['allow_view'];
	}

	include("in_db_conn.php");

	$btn_anonymous = "";
		$query =" SELECT P.proj_id, P.proj_name, P.allow_anonymous, COUNT(PUL.uid) as uid_amt
		FROM p_project P
		LEFT JOIN p_project_uid_list PUL
		ON (P.proj_id = PUL.proj_id AND PUL.uid_status  IN (1,2))
		WHERE P.is_enable = 1 AND P.proj_id=?
		";

		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s",$sProjid);

		if($stmt->execute()){
		$stmt->bind_result($proj_id,$proj_name,$allow_anonymous, $uid_amt );
		if ($stmt->fetch()) {
		//echo "project: $proj_id";
		if($allow_anonymous){
			$btn_anonymous = "
			<div class='fl-wrap-col pw80 pbtn pbtn-ok btn-proj-anonymous-enroll' title='Anonymous Enroll'>
				<div class='fl-fix ph20 fl-mid'><i class='fas fa-user-secret fa-lg'></i> </div>
				<div class='fl-fix ph30 fl-mid ptxt-s10 ptxt-b'>Anonymous</div>
			</div>
			<div class='fl-fix fl-mid pw80 pbtn-grey spinner' style='display:none;'>
				<i class='fa fa-spinner fa-spin fa-lg' ></i>
			</div>
			";
		}
		}
		}
	$stmt->close();


	$ddlDataGroupFilter = "<SELECT id='ddl_data_group' class='w-fill'><option value=''>No filter</option>";
	$query =" SELECT group_id, group_name FROM i_project_filter_list WHERE proj_id=? order by seq_no";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s",$sProjid);
	if($stmt->execute()){
		$stmt->bind_result($data_group_id,$data_group_name );
		while ($stmt->fetch()) {
		$ddlDataGroupFilter .= "<option value='$data_group_id'>$data_group_name</option>";
		}
	}
	$stmt->close();
	$ddlDataGroupFilter .= "</SELECT>";

	// List clinic filter
	$list_html_clinic_filter = "<select id='clinic_filter_project' class='w-fill'><option value=''>No filter</option>";
	$bind_param = "s";
	$array_val = array($sProjid);

	$query = "SELECT distinct id_clinic.clinic_id,
		name_clinic.clinic_name
	from p_project_uid_list id_clinic
	join p_clinic name_clinic on(name_clinic.clinic_id = id_clinic.clinic_id)
	where id_clinic.uid_status in ('1', '2')
	and id_clinic.proj_id = ?;";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($bind_param, ...$array_val);

	if($stmt->execute()){
		$result = $stmt->get_result();
		while($row = $result->fetch_assoc()){
			$list_html_clinic_filter .= "<option value=".$row["clinic_id"].">".$row["clinic_name"]."</option>";
		}
		$list_html_clinic_filter .= "</select>";
	}
	$stmt->close();
	$mysqli->close();


	$dateFrom = date('Y-m-d');
	$dateTo = addDayToDate($dateFrom, 7);

	$proj_additional_button = ""; // additional button
	// outsource management
	if(isset($_SESSION['MODULE']['PROJ_USER_MGT']['OS']['view'])){// button outsource user management
		$proj_additional_button = "
		<div class='fl-wrap-col pw80 pbtn pbtn-ok btn-proj-mnu btn-outsource_user_mgt'
			data-page='outsource_user_mgt_main' data-title='Outsource User Management'
			title='Outsource User Management'>
			<div class='fl-fix ph20 fl-mid'><i class='fas fa-user-astronaut fa-lg'></i> </div>
			<div class='fl-fix ph30 fl-mid ptxt-s10 ptxt-b'>Outsource</div>
		</div>
		";
	}
	/*
	if(isset($_SESSION['MODULE']['PROJ_SETTING']['1']['view'])){// button Project Setting
		$proj_additional_button .= "
		<div class='fl-wrap-col pw80 pbtn bg-sdark2 ptxt-white btn-proj-mnu'
			data-page='proj_setting_main'  data-title='Project Setting'
			title='Project Setting'>
			<div class='fl-fix ph20 fl-mid'><i class='fas fa-cog fa-lg'></i> </div>
			<div class='fl-fix ph30 fl-mid ptxt-s10 ptxt-b'>Setting</div>
		</div>
		";
	}
	*/
	if(isset($proj_auth['allow_admin'])){

		$proj_additional_button .= " 
		<div class='fl-wrap-col pw80 pbtn bg-sdark2 ptxt-white btn-proj-mnu'
			data-page='proj_setting_main'  data-title='Project Setting'
			title='Project Setting'>
			<div class='fl-fix ph20 fl-mid'><i class='fas fa-cog fa-lg'></i> </div>
			<div class='fl-fix ph30 fl-mid ptxt-s10 ptxt-b'>Setting</div>
		</div>
		";

	}
	//echo $class_auth."<br>";

	//print_r($_SESSION['MODULE']);
	/*
	echo '<pre>';
	var_dump($_SESSION);
	echo '</pre>';
	*/
?>


<style>
.pw80{
  min-width:80px;
  max-width:80px;
  padding:5px;
}
.div-p-date{
  min-width:100px;
  max-width:100px;
  padding:5px;
}
.div-p-data{
  padding:5px;
}
</style>



<div class='fl-wrap-row ph50 bg-mdark1 <? echo $class_auth ?>' id="div_proj_detail" data-projid='<? echo $sProjid;?>' data-groupid='' data-anonymous='<? echo $allow_anonymous;?>'>
	<div class='fl-fix px-2 v-mid ptxt-s16 ptxt-b ptxt-white pw300' >
	 <?
	   if($proj_id != "" || $proj_id !== null ){
			// echo "<i class='fas fa-clipboard-list fa-lg'></i> ";
			 echo "[$proj_id] $proj_name";
			// echo "<div><center>(TTL UID: $uid_amt)</center></div> ";
		 }
		 else{
			 echo "NO Project Found!";
		 }

	 ?>
 </div>
 <div class='fl-fix fl-fill fl-mid'>
	 <SELECT id='ddProjGroupList' style='width:70%'>
		 <option value=''>All Group</option>
		 <? include("project_opt_group_list.php"); // projid ?>
	</SELECT>
 </div>


 <!--
 <div class='fl-fix pw150'>
     <div class='proj-label-btn'>.</div>
		 <button id='btnExportProj' class='pbtn' disabled>
			<i class='fas fa-file-export fa-sm'></i>
			Data Export
		 </button>
 </div>
-->


<? echo $proj_additional_button.$btn_anonymous; ?>

<div class='fl-wrap-col  pw80 pbtn pbtn-orange btn-proj-custom-pid' title='Custom PID'>
    <div class='fl-fix ph20 fl-mid'><i class='fas fa-id-badge fa-lg'></i> </div>
    <div class='fl-fix ph30 fl-mid ptxt-s10 ptxt-b'>Custom PID</div>
</div>
<div class='fl-fix fl-mid pw80 pbtn pbtn-blue btn-proj-export' title='Project Data Export'>
  <div class='fl-fix ph20 fl-mid'><i class='fas fa-file-export fa-lg'></i> </div>
  <div class='fl-fix ph30 fl-mid ptxt-s10 ptxt-b'>Data Export</div>
</div>
<div class='fl-fix fl-mid pw80 pbtn ptxt-white bg-mdark2 btn-proj-export-partner' title='Project Partner Export'>
  <div class='fl-fix ph20 fl-mid'><i class='fas fa-file-export fa-lg'></i> </div>
  <div class='fl-fix ph30 fl-mid ptxt-s10 ptxt-b'>Partner Export</div>
</div>
 <div class='fl-fix fl-mid pw80 pbtn pbtn-warning btn-proj-permission' title='Project Authorization management'>
		 <i class='fas fa-id-badge fa-2x'></i>
 </div>


</div>
<div class='fl-wrap-row'>
	<!-- Schedule Search -->
	<div class='fl-wrap-col proj-search-schedule' >

		<div class='fl-wrap-row ph50 pb-1 mt-1' id='div_search_schedule' data-projid='<? echo $sProjid; ?>' data-groupid=''>
			<div class='fl-wrap-col pl-2 pw30 bg-msoft3'>
			<div class='fl-fill fl-mid'>
				<input type='checkbox' class='chk-schedule-date' checked>
			</div>
			</div>

			<div class='fl-wrap-col pl-2 pw120 bg-msoft3'>
				<div class='fl-fix ph15 ptxt-s10 ptxt-b'>
					Schedule From:
				</div>
				<div class='fl-fix ph20'>
					<input type='text' class='proj-schedule-date inpproj pw100 ph25' id='sDateFrom' value='<? echo $dateFrom;?>'>
				</div>
			</div>

			<div class='fl-wrap-col pl-2 pw150 bg-msoft3'>
				<div class='fl-fix ph15 ptxt-s10 ptxt-b'>
					Schedule To:
				</div>
				<div class='fl-fix ph20'>
					<input type='text' class='proj-schedule-date inpproj pw100 ph25' id='sDateTo' value='<? echo $dateTo;?>'>
				</div>
			</div>

			<div class='fl-wrap-col pl-2 pw200'>
				<div class='fl-fix ph15 ptxt-s10 ptxt-b'>
				Filter By:
				</div>
				<div class='fl-fix ph20 ptxt-s14'>
				<?
					echo $ddlDataGroupFilter;
				?>
				</div>
			</div>
			<div class='fl-wrap-col pl-2 pw200'>
				<div class='fl-fix ph15 ptxt-s10 ptxt-b'>
				Clinic By:
				</div>
				<div class='fl-fix ph20 ptxt-s14'>
				<?
					echo $list_html_clinic_filter;
				?>
				</div>
			</div>
			<div class='fl-wrap-col pl-1 pw150 fl-mid pbtn pbtn-blue ptxt-s12' id='btnSearchSchedule'  title='Search | ค้นหา'>
					<i class='fas fa-search fa-sm'></i> ค้นหา | Search
			</div>
		</div>

		<div class='fl-wrap-row ' >
			<div class='fl-wrap-col px-2'>
			<div class='fl-wrap-row bg-mdark1 ph25 ptxt-s10 ptxt-b ptxt-white' style="max-height:25px;">

				<div class='fl-fix fl-mid pw80'>PID|Group</div>
				<div class='fl-fix fl-mid pw80'>UID|UIC</div>
					<div class='fl-fix fl-mid pw50'>Clinic</div>

				<div class='fl-fill fl-mid pw50'>Visit ID</div>
				<div class='fl-fix fl-mid pw150'>Schedule</div>
				<div class='fl-fix fl-mid pw80'>Visit</div>
			</div>
			<div class='fl-wrap-col fl-auto proj-search-schedule-data'>
				<?
			/*
						$_GET["date_from"]=$dateFrom;
						$_GET["date_to"]=$dateTo;
						include_once("project_inc_search_schedule.php");
			*/
						?>
			</div>
				<div class='fl-wrap-col proj-search-schedule-load' style="display:none;">
					<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
				</div>
			</div>
		</div>
	</div>

    <!-- UID PID Search -->
		<div class='fl-wrap-col proj-search-uid pw300' style="min-width:250px;max-width:250px;">

			<div class='fl-wrap-row ph50' style="max-height:50px;padding-bottom:5px;">

				<div class='fl-wrap-col pl-2 pw200'>
 				 <div class='fl-fix ph15 ptxt-s10 ptxt-b'>
 					 Search By UID/UIC/PID:
 				 </div>
 				 <div class='fl-fix ph20'>
					 	<input type='text' class='inpproj' id='sSearchUID' >
 				 </div>
 			 </div>
			 <div class='fl-wrap-col pl-1 pw50' >
				 <div class='fl-fix ph15'>
				 </div>
				 <div id='btnSearchUID'  class='fl-fix fl-mid ph25 pbtn pbtn-blue' title='Search | ค้นหา'>
						<i class='fas fa-search fa-sm'></i>
				 </div>
			 </div>


			</div>

      <div class='fl-wrap-row'>
				<div class='fl-wrap-col px-2 '>
					<div class='fl-wrap-row  bg-mdark2 ph25 ptxt-s10 ptxt-b ptxt-white ' style="max-height:25px;">
						<div class='fl-fix pw80 fl-mid'>PID|Group</div>
						<div class='fl-fix pw80 fl-mid'>UID|UIC</div>
						<div class='fl-fix pw50 fl-mid'>Clinic</div>

					</div>
					<div class='fl-wrap-col fl-auto proj-search-uid-data'>
						<center>-- Please search data by UID/PID/UIC --</center>
							<?
							/*
							$_GET["txtsearch"]='';
							include_once("project_inc_search_schedule.php");
							*/
							?>
					</div>
					<div class='fl-wrap-col proj-search-uid-load' style="display:none;">
						<div class='fl-fill' style='text-align:center;margin-top:100px;'><i class='fa fa-spinner fa-spin  fa-5x'></i></div>
					</div>

				</div>
			</div>

		</div>


</div>


<script>
$(document).ready(function(){
//console.log("class : "+$('#div_proj_detail').attr("class"));
if(!$('#div_proj_detail').hasClass('allow_admin')) $('.btn-proj-permission').remove();
if(!$('#div_proj_detail').hasClass('allow_export')) $('.btn-proj-export').remove();
if(!$('#div_proj_detail').hasClass('allow_export')) $('.btn-proj-export-partner').remove();
//if(!$('#div_proj_detail').hasClass('allow_enroll')) $('.btn-proj-anonymous-enroll').remove();
if(!$('#div_proj_detail').hasClass('allow_enroll')) $('.btn-proj-custom-pid').remove();


$("#div_proj_detail").on("change","#ddProjGroupList",function(){
   let groupid = $(this).val();
	 $("#div_search_schedule").attr("data-groupid",groupid);
	 searchProjSchedule();
	 if($("#sSearchUID").val().trim().length > 2) searchProjUID();

});

$(".proj-search-schedule #btnSearchSchedule").off("click");
$(".proj-search-schedule #btnSearchSchedule").on("click",function(){
//$(".proj-search-schedule").on("click","#btnSearchSchedule",function(){
	searchProjSchedule();
});

$(".chk-schedule-date").on("click",function(){
  //console.log("chk-schedule-date ");
  if($(this).prop("checked"))
   $(".proj-schedule-date").prop('disabled', false);
  else
   $(".proj-schedule-date").prop('disabled', true);
});


/*
$(".proj-search-schedule").on("click","#btnSearchSchedule",function(){
	searchProjSchedule();
});
*/

$(".proj-search-uid").on("click","#btnSearchUID",function(){
	searchProjUID();
});

$(".proj-search-uid").on("keypress","#sSearchUID",function(e){
	if(e.which == 13) {
			searchProjUID();
	}
});


$("#div_proj_detail").on("click",".btn-proj-permission",function(){
	let sProjId = $('#div_proj_detail').attr('data-projid');
	let sUrl = "setting_main_project_auth.php?projid="+sProjId;
	//console.log("permission: "+sUrl);
	showDialog(sUrl,"Project Authorization management ["+sProjId+"]","600","1024","",function(sResult){
			 if(sResult =='1'){

			 }
	},false,function(){
		//Load Done Function
		$.notify("Please select Staff", "info");
	});
});


$("#div_proj_detail").on("click",".btn-proj-export",function(){
	let sProjId = $('#div_proj_detail').attr('data-projid');
	let sUrl = "p_data_export.php?projid="+sProjId;
  let screen_width = screen.width;

	showDialog(sUrl,"Data Export ["+sProjId+"]","500",screen_width.toString(),"",function(sResult){
			 if(sResult =='1'){

			 }
	},false,function(){
		//Load Done Function
		//$.notify("Please select Staff", "info");
	});
});

$("#div_proj_detail").on("click",".btn-proj-export-partner",function(){
	let sProjId = $('#div_proj_detail').attr('data-projid');
	let sUrl = "p_export_patient_relate.php?projid="+sProjId;

	window.open(sUrl, '_blank');
});




$("#div_proj_detail").on("click",".btn-proj-custom-pid",function(){
	let sProjId = $('#div_proj_detail').attr('data-projid');
  let is_anonymous = $('#div_proj_detail').attr('data-anonymous');
	let sUrl = "proj_regis_custom_pid.php?projid="+sProjId+"&is_anonymous="+is_anonymous;

	showDialog(sUrl,"Custom PID ["+sProjId+"]","350","305","",function(sResult){
    if(sResult != ""){
     var result = sResult.split(":");
     $.notify("Enroll to project success (PID: "+result[0]+" / Proj: "+result[1]+")", "success");
     createVisitSchedule(result[0], result[1], result[2]); // uid, proj_id, group_id
    }
	},false,function(){
		//Load Done Function
		//$.notify("Please select Staff", "info");
	});
});


$("#div_proj_detail").on("click",".btn-proj-anonymous-enroll",function(){
  let btnclick = $(this);
  let sProjId = $('#div_proj_detail').attr('data-projid');

  var aData = {
      u_mode:"create_anonymous_uid"
  };

     startLoad(btnclick,btnclick.next(".spinner"));
     callAjax("proj_regis_a.php",aData,function(rtnObj,aData){
           endLoad(btnclick,btnclick.next(".spinner"));
           if(rtnObj.res == 1){
             alert("create uid: "+rtnObj.uid);

             	 let sUrl = "proj_regis_dlg_enroll_proj.php?uid="+rtnObj.uid+"&uic=Anonymous&projid="+sProjId;
             	 showDialog(sUrl,"ลงทะเบียนเข้าโครงการ | Enroll UID to project","250","408","",function(sResult){
                    if(sResult != ""){
             				 var result = sResult.split(":");
             			   $.notify("Enroll to project success (PID: "+result[0]+" / Proj: "+result[1]+")", "success");
             				 createVisitSchedule(result[0], result[1], result[2]); // uid, proj_id, group_id
             			 }
             			 else{
             				  $.notify("ไม่ได้ลงทะเบียน","info");
             			 }

             	 },false,"");



           }
           else{
             $.notify("Fail to create.", "error");
           }
     });// call ajax
});



/*
  $("#div_proj_detail").on("click",".btn-outsource_user_mgt",function(){
    let projid = $("#div_proj_detail").attr("data-projid");
    let sUrl = "outsource_user_mgt_main.php?projid="+projid;

    showDialog(sUrl,"Outsource User Management","90%","90%","",function(sResult){
         if(sResult =='1'){

         }
    },false,function(){
      //Load Done Function
      $.notify("Please select Staff", "info");
    });
  });
*/

  $("#div_proj_detail .btn-proj-mnu").off("click");
  $("#div_proj_detail .btn-proj-mnu").on("click",function(){
    let projid = $("#div_proj_detail").attr("data-projid");
    let title = $(this).attr("data-title")+' ['+projid+']';
    let page = $(this).attr("data-page");
    let sUrl = page+".php?projid="+projid;

    showDialog(sUrl,title,"90%","90%","",function(sResult){
         if(sResult =='1'){

         }
    },false,function(){
      //Load Done Function

    });
  });




});

$(".proj-schedule-date").datepicker({
	dateFormat:"yy-mm-dd",
	changeYear:true,
	changeMonth:true
});

function searchProjSchedule(){
	let projid=$("#div_search_schedule").attr("data-projid");
	let groupid=$("#div_search_schedule").attr("data-groupid");
	let date_from="";
	let date_to="";
	let a=($("#div_proj_detail").hasClass("allow_admin"))?'1':'0';

	let data_group_id = $("#ddl_data_group").val();
	let data_clinic_id = $("#clinic_filter_project").val();

	if($(".chk-schedule-date").prop("checked")){
		date_from=$("#sDateFrom").val();
		date_to=$("#sDateTo").val();
	}

	sUrl="project_inc_search_schedule.php?projid="+projid+"&groupid="+groupid+"&date_from="+date_from+"&date_to="+date_to+"&a="+a+"&data_group_id="+data_group_id+"&data_clinic_id="+data_clinic_id;
  //console.log("sUrl: "+sUrl);
	loadLink(sUrl, $(".proj-search-schedule-data"), $(".proj-search-schedule-load"));
}

function searchProjUID(){
	let txtsearch=$("#sSearchUID").val().trim();
	if(txtsearch.length < 3){
		$("#sSearchUID").notify("Please insert at least 3 charecters!", "info");
		return;
	}
	let projid=$("#div_search_schedule").attr("data-projid");
	let groupid=$("#div_search_schedule").attr("data-groupid");
	let a=($("#div_proj_detail").hasClass("allow_admin"))?'1':'0';

	sUrl="project_inc_search_uid.php?projid="+projid+"&groupid="+groupid+"&txtsearch="+txtsearch+"&a="+a;
//  console.log("sUrl: "+sUrl);
	loadLink(sUrl, $(".proj-search-uid-data"), $(".proj-search-uid-load"));
}



</script>
