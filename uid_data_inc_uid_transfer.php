

<div class='fl-wrap-row div-uid-transfer' style='height:100%;' >
	<div class='fl-fill bg-ssoft2' >
		<div class='fl-fix fl-mid ph30 bg-sdark2 ptxt-white' >
        <i class='fa fa-male fa-lg'></i>
				UID ต้นทาง : <input type='text' class='txt-uid1 txt-uid' maxlength="9" placeholder='PXX-XXXXX'>
				<div data-id='1' class='btn-get-uid-data px-2 bg-sdark1 pbtn ptxt-white' title='ดึงข้อมูล | Load data'> <i class='fa fa-search-plus fa-2x'></i> ดึงข้อมูล </div>
		</div>


		<div class='fl-wrap-row fl-fill' >
        <div class='fl-fix pw30'></div>
				<div class='fl-fill div-uid1' style='min-width:500px;'>
                <div class='div-uid-transfer1' data-uid=''></div>
				</div>
				<div class='fl-fill fl-mid spinner' style='min-width:500px;display:none;'>
          <i class='fas fa-spinner fa-spin spinner fa-5x'></i> Loading
				</div>
				<div class='fl-fix pw30'></div>
		</div>


	</div>

	<div class='fl-wrap-col fl-mid pw100 pbg-blue ptxt-white pbtn btn-data-transfer' >
		<div class='fl-fill fl-mid'><i class='fa fa-walking fa-5x'></i> </div>
		<div class='fl-fix fl-mid ph200 ' >
			<i class='fa fa-angle-double-right fa-5x'></i>
		</div>
		<div class='fl-fix fl-mid ph30'> ย้ายข้อมูล</div>
		<div class='fl-fix fl-mid ph30'> Transfer Data</div>
		<div class='fl-fill'></div>
	</div>

	<div class='fl-wrap-col pw100 pbg-blue ptxt-white spinner' style='display:none;' >
		<div class='fl-fix ph100 fl-mid'>
			<center>Data transfering</center>
		</div>
		<div class='fl-fill fl-mid'>
			<i class='fas fa-cog fa-spin fa-5x'></i>
		</div>
	</div>

	<div class='fl-fill' >
		<div class='fl-fix fl-mid ph30 bg-mdark2 ptxt-white' >
        <i class='fa fa-walking fa-lg'></i>
				UID ปลายทาง : <input type='text' class='txt-uid2 txt-uid' maxlength="9" placeholder='PXX-XXXXX'>
				<div data-id='2' class='btn-get-uid-data px-2 bg-mdark1 pbtn ptxt-white' title='ดึงข้อมูล | Load data'> <i class='fa fa-search-plus fa-2x'></i> ดึงข้อมูล </div>
		</div>

		<div class='fl-wrap-row fl-fill' >
				<div class='fl-fix pw30'></div>
				<div class='fl-fill div-uid2' style='min-width:500px;'>
								<div class='div-uid-transfer2' data-uid=''></div>
				</div>
				<div class='fl-fill fl-mid spinner' style='min-width:500px;display:none;'>
					<i class='fas fa-spinner fa-spin fa-5x'></i> Loading
				</div>
				<div class='fl-fix pw30'></div>
		</div>

	</div>

</div>




<script>
$(document).ready(function(){
//	$(".txt-uid1").val('P20-11876'); $(".txt-uid2").val('P20-11877');

  $(".txt-uid").mask("a99-99999",{placeholder:"P##-#####"});

	$(".div-uid-transfer").on("click",".btn-get-uid-data",function(){

			 let mode = $(this).attr('data-id');
			 let uid = $('.txt-uid'+mode).val();
			 if(uid.length < 9){
				 $('.txt-uid'+mode).notify('กรุณากรอก UID ให้ถูกต้อง', 'info');
				 return;
			 }
			 let sUrl = 'uid_data_inc_uid_transfer_info.php?uid='+uid+'&type='+mode;
			 //console.log("enter here "+mode+"/"+uid);
			 $('.div-uid'+mode).html('');
			 loadLink(sUrl, $('.div-uid'+mode), $('.div-uid'+mode).next('spinner'));

	});
	$(".div-uid-transfer").on("click",".btn-uid-edit-dlg",function(){
		   let uid = $(this).attr('data-uid');
			 let sUrl = 'patient_inc_info.php?uid='+uid;
			 let screen_width = screen.width;
			 showDialog(sUrl,"UID: "+uid+ " [Edit Information]","500",screen_width.toString(),"",function(sResult){
					 if(sResult != ""){

					 }
			 },false,function(){

			 });

	});
	// edit uic
	$(".div-uid-transfer").on("click",".btn-uic-edit-dlg",function(){
		   let uid = $(this).parent().attr('data-uid');
			 let sUrl = 'uid_data_dlg_uic_edit.php?uid='+uid;
			 showDialog(sUrl,"UID: "+uid+ " [Edit UIC]","120","300","",function(sResult){
					
						   //console.log("new uic : "+sResult+" / "+$('.div-'+uid).html());
               $('.div-'+uid).html(sResult);

			 },false,function(){

			 });

	});

	$(".div-uid-transfer").on("click",".btn-data-transfer",function(){
      let sUid1 = $('.div-uid-transfer1').attr('data-uid');
			let sUid2 = $('.div-uid-transfer2').attr('data-uid');

			if(sUid1 == ''){
				alert('ไม่มีข้อมูลของ UID ต้นทาง'); return;
			}
			else if(sUid2 == ''){
				alert('ไม่มีข้อมูลของ UID ปลายทาง'); return;
			}
			else if(sUid1 == sUid2){
				alert('ไม่ถูกต้อง ข้อมูล UID ต้นทาง และ UID ปลายทาง เป็นเบอร์เดียวกัน '+sUid1+'/'+sUid1); return;
			}


		  let btn_data_transfer = $(this);
			var aData = {
					u_mode:"transfer_uid_data",
					uid1:sUid1,
					uid2:sUid2
					};
			startLoad(btn_data_transfer, btn_data_transfer.next(".spinner"));
			callAjax("uid_data_a.php",aData,function(rtnObj,aData){
					endLoad(btn_data_transfer, btn_data_transfer.next(".spinner"));
					if(rtnObj.res == 1){
						$.notify("DATA Transfered","success");

					 let sUrl = 'uid_data_inc_uid_transfer_info.php?uid='+sUid1+'&type=1';
		 			 $('.div-uid1').html('');
		 			 loadLink(sUrl, $('.div-uid1'), $('.div-uid1').next('spinner'));

					 sUrl = 'uid_data_inc_uid_transfer_info.php?uid='+sUid2+'&type=2';
					 $('.div-uid2').html('');
					 loadLink(sUrl, $('.div-uid2'), $('.div-uid2').next('spinner'));

            console.log(rtnObj.txtinfo);
					}
					else{
						$.notify("Fail to transfer data.", "error");
					}
			});// call ajax



	});






});



</script>
