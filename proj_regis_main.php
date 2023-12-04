<?
/* Project Register Main */

include("in_session.php");
include_once("in_php_function.php");

?>

<div class='fl-wrap-row h-ss bg-info'>
	<div class='fl-wrap-col fl-fill fl-mid proj-head-txt' style="min-width:200px;">
		Register UID

  </div>
  <div class='fl-wrap-col' style="max-width:24px;min-width:24px;">
     <i class='fas fa-window-close fa-lg pt-1  btnclose proj-current-close' data-page="proj_regis_uid" title="Close"></i>
  </div>
</div>

<div id="div_regis_uid_info" class='fl-wrap-row  mt-2'>
	<div class='fl-wrap-col w-xs' >

	</div>
	<div class='fl-wrap-col fl-fill' >
		<div class='fl-wrap-row h-s'>
			<div class='fl-wrap-col w-m px-1'>
				<div class='proj-label h-xs'>ชื่อ: (ไทย)</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id='fname' data-require='1' placeholder="ชื่อ: (ไทย)">
				</div>
			</div>
			<div class='fl-wrap-col w-xl px-1'>
				<div class='proj-label h-xs'>นามสกุล: (ไทย)</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id='sname' data-require='1' placeholder="นามสกุล (ไทย)">
				</div>
			</div>
			<div class='fl-wrap-col w-m px-1'>
				<div class='proj-label h-xs'>Name: (Eng)</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id='en_fname' data-require='0' placeholder="Name (Eng)">
				</div>
			</div>
			<div class='fl-wrap-col w-xl px-1'>
				<div class='proj-label h-xs'>Last Name: (Eng)</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id='en_sname' data-require='0' placeholder="Last Name (Eng)">
				</div>
			</div>
			<div class='fl-wrap-col w-m px-1'>
				<div class='proj-label h-xs head-event'>วันเกิด (ค.ศ.)| DOB:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj v-date' id='date_of_birth' data-require='1' placeholder="yyyy-mm-dd">
				</div>
			</div>

			<div class='fl-wrap-col w-l px-1'>
				<div class='proj-label h-xs'>เลขบัตรประชาชน | Thai Citizen ID:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id="citizen_id" maxlength="13" data-require='0' placeholder="เลขที่บัตรประชาชน">
				</div>
			</div>
			<div class='fl-wrap-col w-l px-1'>
				<div class='proj-label h-xs'>Passport No:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id='passport_id' data-require='0' placeholder="Passport No.">
				</div>
			</div>
			<div class='fl-wrap-col w-m px-1'>
				<div class='proj-label h-xs'>เพศกำเนิด | Sex:</div>
				<div class='h-xs'>
					<select id="sex" class="save-data inpproj" data-require='1'>
						<option value='' >Select|เลือก</option>
				  	<option value='1'>ชาย | Male</option>
				  	<option value='2'>หญิง | Female</option>
					</select>
				</div>
			</div>

			<div class='fl-wrap-col w-s px-1'>
				<div class='proj-label-btn h-xs'>.</div>
				<div class='h-xs'>
					<textarea class='inpproj pfade' id='pasteArea' data-require='0' placeholder="Paste Image Here."></textarea>
				</div>
			</div>

		</div>

		<div class='fl-wrap-row h-s'>
			<div class='fl-wrap-col fl-fill px-1'>
				<div class='proj-label h-xs'>ที่อยู่ | Address่:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id="id_address" data-require='0' placeholder="ที่อยู่ | Address">
				</div>
			</div>
			<div class='fl-wrap-col w-l px-1'>
				<div class='proj-label h-xs'>แขวง | Zone:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id="id_zone" data-require='0' placeholder="แขวง | Zone">
				</div>
			</div>
			<div class='fl-wrap-col w-l px-1'>
				<div class='proj-label h-xs'>เขต | District:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id="id_district" data-require='0' placeholder="เขต | District">
				</div>
			</div>
			<div class='fl-wrap-col w-l px-1'>
				<div class='proj-label h-xs'>จังหวัด | Province:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id="id_province" data-require='0' placeholder="จังหวัด | Province">
				</div>
			</div>
			<div class='fl-wrap-col w-m px-1'>
				<div class='proj-label h-xs'>รหัส|Postcode:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id="id_postal_code" data-require='0' placeholder="รหัสไปรษณีย์">
				</div>
			</div>

		</div>

		<div class='fl-wrap-row h-s'>
			<div class='fl-wrap-col w-l px-1'>
				<div class='proj-label h-xs'>เบอร์โทร | Tel No.:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id="tel_no" data-require='0'  placeholder="เบอร์โทร | Tel No.">
				</div>
			</div>
			<div class='fl-wrap-col w-xl px-1'>
				<div class='proj-label h-xs'>อีเมล์ | Email:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id="email" data-require='0' placeholder="อีเมล์ | Email">
				</div>
			</div>
			<div class='fl-wrap-col w-xl px-1'>
				<div class='proj-label h-xs'>ไลน์ไอดี | Line ID:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj' id="line_id" data-require='0' placeholder="ไลน์ไอดี | Line ID">
				</div>
			</div>

			<div class='fl-wrap-col w-l ml-2 px-1  bg-mdark3'>
				<div class='proj-label h-xs'>UID:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj search-key' id="uid" data-require='0' placeholder="UID">
				</div>
			</div>

			<div class='fl-wrap-col w-l px-1  bg-mdark3'>
				<div class='proj-label h-xs'>UIC:</div>
				<div class='h-xs'>
					<input type='text' class='save-data inpproj search-key' id="uic"  data-require='0' placeholder="UIC">
				</div>
			</div>

			<div class='fl-wrap-col v-mid px-1 pw100'>
				<div class='ph50'>
	        		<button class = 'btn-regis-search pbtn pbtn-blue pw100 ptxt-s12 fl-mid'> ค้นหา|Search</button>
          			<i class='fa fa-spinner fa-spin fa-2x' style="display:none;"></i>
				</div>
			</div>
			<div class='fl-wrap-col v-mid px-1 ' style="min-width:125px;max-width:125px;">
				<div class='ph50'>
							<button class = 'btn-regis-search-clear pbtn pbtn-warning ptxt-s12 fl-mid'> ล้างข้อมูล|Clear </button>
								<i class='fa fa-spinner fa-spin fa-2x' style="display:none;"></i>
				</div>
			</div>
		</div>

		<div class='fl-wrap-row fl-fill h-xs proj-label bg-sdark1 ptxt-white'>
			<div class='fl-fix px-1 w-m'>Enroll</div>
			<div class='fl-fix px-1 w-m'>UID</div>
			<div class='fl-fix px-1 w-m'>UIC</div>
			<div class='fl-fix px-1 fl-fill'>Name</div>
			<div class='fl-fix px-1 w-l'>Tel No.</div>
			<div class='fl-fix px-1 w-l'>Citizen ID</div>
			<div class='fl-fix px-1 w-l'>DOB</div>
			<div class='fl-fix px-1 pw20'>Edit</div>
		</div>
		<div class='fl-wrap-row fl-fill ' style="max-height:320px;min-height:320px;">
			<div class='fl-wrap-col fl-fill fl-auto bg-white div-regis-uid-list' >

			</div>
		</div>
 	</div>
</div>
<div class='fl-wrap-row h-ss fl-mid mt-2 bg-info rowbtn' >
  <div class='fl-wrap-col w-xxl' >
     หากไม่พบรายชื่อในระบบกรุณากด
	</div>
	<div class='fl-wrap-col w-l' >
		 <button class = 'btn-regis-new-uid p-btnvisit'> ลงทะเบียน UID</button>
		 <i class='fa fa-spinner fa-spin fa-2x text-white' style="display:none;"></i>
	</div>
</div>

<script>
$(document).ready(function(){
  $(".v-date").mask("9999-99-99",{placeholder:"yyyy-mm-dd"});
  $("#pasteArea").unbind("paste");
	$("#pasteArea").on("paste",function(event){
	    // use event.originalEvent.clipboard for newer chrome versions
	    var items = (event.clipboardData  || event.originalEvent.clipboardData).items;
	    //console.log(JSON.stringify(items)); // will give you the mime types
	    // find pasted image among pasted items
	    var objImage = null;
	    for (var i = 0; i < items.length; i++) {
	      if (items[i].type.indexOf("image") === 0) {
	        objImage = items[i].getAsFile();
	      }
	    }
	    // load image if there is a pasted image
	    if (objImage !== null) {
	      var imgReader = new FileReader();
	      imgReader.onload = function(objBlob) {
	        //console.log(objBlob.target.result); // data url!
	        //document.getElementById("pastedImage").src = objBlob.target.result;
	        sUrl="patient_a.php";
	        sCitiID = $("#txtCitizenId").val();
	        var aData={u_mode:"upload_image",cid:sCitiID,idimg:objBlob.target.result};
	        callAjax(sUrl,aData,function(jRes,retAdata){
	         if(jRes.res=="1"){
	          $("#pastedImage").attr("src","idimg/"+retAdata.cid+".png");
	         }
	        });
	      };
	      imgReader.readAsDataURL(objImage);
	    }
	    });


	$("#div_regis_uid_info").on("click",".btn-regis-search",function(){  // search uid
		//console.log("enter1")
		let btnclick = $(this);
		let str_search = "";
		let lst_data_obj = {};

		$('#div_regis_uid_info .save-data').each(function(ix,objx){
			//console.log("chk id: "+$(objx).data("id"));
			if($(objx).val().trim() != ""){
				lst_data_obj[$(objx).attr('id')] = $(objx).val().trim();
				str_search += $(objx).val().trim();
			}

		});

		if(str_search != ""){
			var aData = {
					u_mode:"check_uid_data",
					lst_data:	lst_data_obj
			};

				startLoad(btnclick, btnclick.next(".fa-spinner"));
				callAjax("proj_regis_a.php",aData,function(rtnObj,aData){
							endLoad(btnclick, btnclick.next(".fa-spinner"));
						//	console.log("end load");
							if(rtnObj.res == 1){
								$(".div-regis-uid-list").html(rtnObj.txtrow);
								$(".div-regis-uid-list .enroll").attr("title", "ลงทะเบียนเข้าโครงการ");
								$(".div-regis-uid-list .edit").attr("title", "แก้ไขข้อมูล UID");
							}
							else{
								$(this).notify("Fail to check.", "error");
							}
				});// call ajax

		}
		else{
			$(this).notify("กรุณากรอกข้อมูลเพื่อทำการค้นหา.", "info");
		}


	});



	$("#div_regis_uid_info").on("keypress",".search-key",function(e){
		if(e.which == 13) {
			if($(this).val() != '')
			$('.btn-regis-search').click();
			else $(this).notify("กรุณากรอกข้อมูล","info");
		}
	});


	$("#div_regis_uid_info").on("click",".btn-regis-search-clear",function(){  // clear search data
		$('.save-data').val("");
		$(".div-regis-uid-list").html("<center>- No record -</center>");
	});

$(".div-regis-uid-list").on("click",".enroll",function(){ // enroll uid to project
   //console.log("enroll "+$(this).attr("data-id"));
	 let sUID = $(this).parents('.uid-row').attr('data-uid');
	 let sUIC = $(this).parents('.uid-row').attr('data-uic');

	 let sUrl = "proj_regis_dlg_enroll_proj.php?uid="+sUID+"&uic="+sUIC+"&enroll=1";
	 showDialog(sUrl,"ลงทะเบียนเข้าโครงการ | Enroll UID to project","280","408","",function(sResult){
       if(sResult != ""){
				 var result = sResult.split(":");
			   $.notify("Enroll to project success (PID: "+result[0]+" / Proj: "+result[1]+")", "success");
				// createVisitSchedule(result[0], result[1], result[2]); // uid, proj_id, group_id
         viewUIDvisit(result[0], result[1], result[2]);
			 }
			 else{
				  $.notify("ไม่ได้ลงทะเบียน","info");
			 }

	 },false,"");
	//showDialog(dlgPage,"Enroll subject to project","300","350","",closeFunction,"false",doneLoadFunction){
	//showDialog(dlgPage,dlgTitle,dlgHeight,dlgWidth,dlgCss,closeFunction,closeHide,doneLoadFunction){

});

$(".div-regis-uid-list").on("click",".edit",function(){ // edit uid to project
	//console.log("enroll "+$(this).attr("data-id"));
	let sUID = $(this).parents('.uid-row').attr('data-uid');
	let sUrl = "patient_inc_info.php?uid="+sUID;
	showDialog(sUrl,"แก้ไขข้อมูล UID | Edit UID Information","600","1024","",function(sResult){
		if(sResult != ""){}
	},false,"");
});

// Validate format daet yyyy-mm-dd
$("#date_of_birth").off("focusout");
$("#date_of_birth").on("focusout", function(){
	setTimeout(function(){
		var dateOfBirth_val = $("#date_of_birth").val();
		if(isValidDate(dateOfBirth_val) == false){
			$("#date_of_birth").focus();
			$(".head-event").text("Format วันเกิดไม่ถูก");
			$(".head-event").attr("style", "color: red;");
		}
		else{
			$(".head-event").attr("style", "color: #212529;");
			$(".head-event").text("วันเกิด (ค.ศ.)| DOB:");
		}
	}, 100);	
});


$(".btn-regis-new-uid").on("click",function(){  // register new uid

	let btnclick = $(this);
	let lst_data_obj = {};
	let flag_valid = 1;
	$('.save-data').each(function(ix,objx){
		//console.log("chk id: "+$(objx).attr('id'));
		if($(objx).val().trim() != ""){
			lst_data_obj[$(objx).attr('id')] = $(objx).val().trim();
		}
		else{
			if($(objx).attr("data-require") == '1'){
				$(objx).addClass("bg-warning");
				$(objx).notify("กรุณากรอกข้อมูลนี้", "error");
				flag_valid = 0;
			}
		}

	});

	//console.log("citizen_card "+$("#citizen_id").val()+" / "+$("#citizen_id").length);
	if($("#citizen_id").val() != "" && $("#citizen_id").val().length != 13){
		$("#citizen_id").notify("ข้อมูลไม่ครบ 13 หลัก");
		flag_valid = 0;
	}

	if(flag_valid == 1){
		var aData = {
			lst_data:	lst_data_obj
		};

		//	startLoad(btnclick, btnclick.next(".fa-spinner"));
		callAjax("proj_regis_a_new_uid.php",aData,function(rtnObj,aData){
			//	endLoad(btnclick, btnclick.next(".fa-spinner"));
			if(rtnObj.res == 1){
				alert("ลงทะเบียนสำเร็จ : "+rtnObj.uid );
				$(".save-data").removeClass("bg-warning");
				//	$(".btn-regis-search-clear").click();
				$(".search-key #uid").val(rtnObj.uid);
				$(".btn-regis-search").click();
			}
			else{
				$(this).notify("Fail to register new UID.", "error");
			}
		});// call ajax
	}
	else{
		alert("ข้อมูลไม่ครบ กรุณาตรวจสอบ");
	}


});

	$(".v-date").datepicker({
		dateFormat:"yy-mm-dd",
		changeYear:true,
		changeMonth:true
	});

});

function isValidDate(dateString) {
	var regEx = /^\d{4}-\d{2}-\d{2}$/;
	if(!dateString.match(regEx)) return false;  // Invalid format
	var d = new Date(dateString);
	var dNum = d.getTime();
	if(!dNum && dNum !== 0) return false; // NaN value, Invalid date
	return d.toISOString().slice(0,10) === dateString;
}

</script>
