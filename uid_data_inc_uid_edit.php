

<div class='fl-wrap-row bg-mdark2  ptxt-white div-uid-edit ph50' >

	<div class='fl-fix pw150 ptxt-s20  ptxt-b fl-mid'>
		 UID EDIT
	</div>
	<div class='fl-fill fl-mid'>
		 UID: <input type='text' class='txt-uid-edit txt-uid' maxlength="9" placeholder='PXX-XXXXX'><button class='btn-get-data px-2 bg-mdark1 pbtn ptxt-white' title='ดึงข้อมูล | Load data'> <i class='fa fa-search-plus fa-2x'></i> ดึงข้อมูล </button>
	</div>
	<div class='fl-fix pw150 ptxt-s14 pbtn pbtn-cancel btn-clear-data'>
		 เคลียร์ข้อมูล | Clear Data
	</div>
</div>
<div class='fl-wrap-row fl-fill div-uid-edit-info ' >
	<div class='fl-wrap-col fl-auto div-uid-edit-data '>

	</div>
	<div class='fl-wrap-col fl-mid div-uid-edit-spinner ' style='display:none;'>
			 <i class='fa fa-spinner fa-spin  fa-5x'></i> Loading
	</div>

</div>






<script>
$(document).ready(function(){
  $(".txt-uid").mask("a99-99999",{placeholder:"P##-#####"});
	$(".div-uid-edit").on("click",".btn-get-data",function(){
		if($(".txt-uid-edit").val().trim() == '') {
			$(".txt-uid-edit").notify('กรุณากรอก UID', 'info');
			return;
		}
		 let sUrl = 'patient_inc_info.php?uid='+$(".txt-uid-edit").val();
		 loadLink(sUrl, $('.div-uid-edit-data'), $('.div-uid-edit-spinner'));
	});
	$(".div-uid-edit").on("click",".btn-clear-data",function(){
		$('.div-uid-edit-data').html('');
    $(".txt-uid-edit").val('');
		$(".txt-uid-edit").focus();

	});



});

function changeMenu_UIDmgt(menu_page){
   let page_load = ""+menu_page

	 startLoad(btnsave, btnsave.next(".spinner"));
}


</script>
