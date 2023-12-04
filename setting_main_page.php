<?
	include("in_session.php");
	include_once("in_php_function.php");
?>
<style>
	.page-list .page-head{
		background-color: white;
	}
	.page-list .p-cmd{
		min-width: 80px;
	}
	.page-list .p-id{
		min-width: 150px;
	}
	.page-list .p-title{
		min-width: 200px;
	}
	.page-list .p-desc{
		
	}
	.page-list .p-link{
		min-width: 200px;
	}
	.page-list .p-enable{
		min-width: 90px;
	}
	.page-list .p-color{
		min-width: 80px;
	}
	.page-list .p-icon{
		min-width: 200px;
		max-width: 200px;
		text-align: left;
		font-size:x-small;
	}
	.page-list .p-icon i{
		margin:0 5px;
	}

	.page-list .p-action{
		min-width: 100px;
		max-width: 100px;
		text-align: center;
	}
	.page-list input{
		width:90%;
	}
	.page-list{
		vertical-align: middle;
	}
	.page-list .page-row{
		max-height: 30px;
		min-height: 30px;
		vertical-align: middle;
		text-align: center;
		align-items: center;
	}
	.page-list .page-row:hover{
		filter:brightness(80%);
	}
	.page-list .page-head{
		max-height: 50px;
		font-size:12px;
	}
	.page-list .page-row:nth-child(even){
		background-color: white;
	}
	.page-list .page-row:nth-child(odd){
		background-color: #b8d1f3;
	}

</style>
<div class='fl-wrap-col page-list'>
	<div class='fl-wrap-row page-head'>
		<div class='fl-fix p-cmd' style='text-align:center;line-height: 50px;vertical-align: middle'>
			<i id='btnCancelData' class='fabtn fas fa-broom fa-2x'></i>
		</div>
		<div class='fl-fix p-id'>
			ID*<br/>
			<input id='txtPageId' data-odata='' maxlength="100" />
		</div>
		<div class='fl-fix p-title'>
			Title<br/>
			<input id='txtPageTitle' data-odata='' maxlength="500" />
		</div>
		<div class='fl-fill p-desc'>
			Desc<br/>
			<input id='txtPageDesc' data-odata=''  maxlength="1000" />
		</div>
		<div class='fl-fix p-link'>
			Link*<br/>
			<input id='txtPageLink' data-odata=''  maxlength="255" />
		</div>
		<div class='fl-fix p-enable'>
			Enable<br/>
			<SELECT id='ddlPageEnable' data-odata=''>
				<option value='1'>Enable</option>
				<option value='0'>Disable</option>
			</SELECT>
		</div>
		<div class='fl-fix p-icon'>
			FA Icon* <span id='colorTester'><i id='icoTester' class='' style=''></i></span><br/>
			<input id='txtPageIcon' data-odata=''  maxlength="100"  />
		</div>
		<div class='fl-fix p-color'>
			Color <br/>
			<input id='txtPageColor' data-odata=''  maxlength="70"  />
		</div>
		<div class='fl-fix p-action'>
			Control<br/>
			<span style='color:green'><i id='btnAddPage' class="fabtn fas fa-plus-square fa-2x" data-mode='page_add'></i><i id='imgAddSpinner' style='display:none' class="fas fa-spinner fa-spin"></i></span>
		</div>
	</div>
	<div class='fl-wrap-col pagerow-body fl-auto' >
		<div class='fl-fill pagerow-list'>
		<? include("list_page.php"); ?>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){
$("#txtPageIcon").unbind("change");
$("#txtPageIcon").on("change",function(){
	if($("#txtPageIcon").val().trim()!=""){
		$("#icoTester").attr("class","");
		$("#icoTester").addClass("fa "+$("#txtPageIcon").val().trim());

	}
});
$("#txtPageColor").unbind("change");
$("#txtPageColor").on("change",function(){
	if($("#txtPageColor").val().trim()!=""){
		$("#colorTester").css("color",$("#txtPageColor").val());

	}
});
$("#btnAddPage").unbind("click");
$("#btnAddPage").on("click",function(){
	sPageId= $("#txtPageId").val().trim();
	sPageTitle= $("#txtPageTitle").val().trim();
	sPageDesc= $("#txtPageDesc").val().trim();
	sPageLink= $("#txtPageLink").val().trim();
	sPageEnable= $("#ddlPageEnable").val();
	sPageIcon= $("#txtPageIcon").val().trim();
	sPageColor= $("#txtPageColor").val().trim();
	sMode = $("#btnAddPage").attr("data-mode");
	var aData = {u_mode:sMode,pid:sPageId,ptitle:sPageTitle,pdesc:sPageDesc,plink:sPageLink,penable:sPageEnable,picon:sPageIcon,pcolor:sPageColor};
	if(sPageId=="" || sPageLink=="" || sPageIcon==""){
		$.notify("Please complete require input.");
		return;
	}

	startLoad($("#btnAddPage,#btnCancelData"),$("#imgAddSpinner"));

	callAjax("setting_a_page.php",aData,function(rtnObj,aData){
		if(rtnObj.res!="1"){
			$.notify("Data is not save. Please try again\r\n"+rtnObj.msg,"error");

		}else if(rtnObj.res=="1"){
			$.notify("Data Saved","success");

			if($("#btnAddPage").attr("data-mode")=="page_update"){
				sPageId = $("#txtPageId").val();
				sTRow = $(".pagerow-body").find(".btneditpage[data-pageid='"+sPageId+"']");
				sObj = $(sTRow).closest(".page-row");

				$(sObj).find(".p-title").find("span").html($("#txtPageTitle").val().trim());
				$(sObj).find(".p-desc").find("span").html($("#txtPageDesc").val().trim());
				$(sObj).find(".p-link").find("span").html($("#txtPageLink").val().trim());
				$(sObj).find(".p-enable").find(".ddlpageenable").val($("#ddlPageEnable").val());
				$(sObj).find(".p-icon").find("span[class='page-fa-icon']").html($("#txtPageIcon").val().trim());
				$(sObj).find(".p-icon").find("i").attr("class","");
				$(sObj).find(".p-icon").find("i").addClass("fa "+$("#txtPageIcon").val().trim());
				$(sObj).find(".p-icon").find("span[class='page-fa-color']").css("color",$("#txtPageColor").val().trim());
				$(sObj).find(".p-color").find("span").html($("#txtPageColor").val().trim());
				$(sObj).find(".p-color").find("span").css("color",$("#txtPageColor").val().trim());

			}else if($("#btnAddPage").attr("data-mode")=="page_add"){
				$(".pagerow-body .pagerow-list").prepend(rtnObj.msg);
			}
			$("#btnCancelData").trigger("click");
		}
		//
		endLoad($("#btnAddPage"),$("#imgAddSpinner"));

	});
});

$("#btnCancelData").unbind("click");
$("#btnCancelData").on("click",function(){
	$(".page-head input").val("");
	$(".page-head input").attr("data-odata","");
	$("#txtPageId").removeAttr("readonly");
	$("#ddlPageEnable").val("1");
	$("#ddlPageEnable").attr("data-odata","");
	$("#btnAddPage").attr("data-mode","page_add");
	$("#icoTester").attr("class","");
});

$(".pagerow-body .btnpagedelete").unbind("click");
$(".pagerow-body").on("click",".btnpagedelete",function(){
	if($(this).hasClass("fa-spin")) return;
	sPageId = $(this).attr('data-pageid');
	if(!confirm("Do you want to delete this page?\r\nยืนยันลบข้อมูล\r\n"+sPageId)) return;
	
	if(sPageId!="" && sPageId!=undefined){
		var aData = {u_mode:"page_delete",pid:sPageId};

		$(this).addClass("fa-spin");
		$(this).closest(".page-row").find(".btneditpage").hide();

		callAjax("setting_a_page.php",aData,function(rtnObj,aData){
			objRow = $(".pagerow-body .btnpagedelete[data-pageid='"+sPageId+"']");

			if(rtnObj.res!="1"){
				$.notify("Data is not removed. Please try again\r\n"+rtnObj.msg,"error");
				$(objRow).removeClass("fa-spin");
				$(objRow).closest(".page-row").find(".btneditpage").show();
			}else if(rtnObj.res=="1"){
				$.notify("Data removed","success");
				$(objRow).closest(".page-row").remove();
			}
		});
	}
});

$(".pagerow-body .btneditpage").unbind("click");
$(".pagerow-body").on("click",".btneditpage",function(){

	if($(this).hasClass("fa-spin")) return;
	sObj = $(this).closest(".page-row");
	sId = $(sObj).find(".p-id").find("span").html();
	sTitle = $(sObj).find(".p-title").find("span").html();
	sDesc = $(sObj).find(".p-desc").find("span").html();
	sLink = $(sObj).find(".p-link").find("span").html();
	sEnable = $(sObj).find(".p-enable").find(".ddlpageenable").val();
	sIcon = $(sObj).find(".p-icon").find("span[class='page-fa-icon']").html();
	sColor = $(sObj).find(".p-color").find('span').html();

	$("#txtPageId").attr("readonly",true);
	$("#txtPageId").val(sId);
	$("#txtPageId").attr("data-odata",sId);
	$("#txtPageTitle").val(sTitle);
	$("#txtPageTitle").attr("data-odata",sTitle);
	$("#txtPageDesc").val(sDesc);
	$("#txtPageDesc").attr("data-odata",sDesc);
	$("#txtPageLink").val(sLink);
	$("#txtPageLink").attr("data-odata",sLink);
	$("#ddlPageEnable").val(sEnable);
	$("#ddlPageEnable").attr("data-odata",sEnable);
	$("#txtPageIcon").val(sIcon);
	$("#txtPageIcon").attr("data-odata",sIcon);
	$("#icoTester").attr("class","");
	$("#icoTester").addClass($(sObj).find(".p-icon").find("i").attr("class"));
	$("#btnAddPage").attr("data-mode","page_update");
	$("#txtPageColor").val(sColor);
	$("#txtPageColor").attr("data-odata",sColor);
	$("#colorTester").css("color",sColor);

});
});
</script>