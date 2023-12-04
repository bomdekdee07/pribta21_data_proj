<?
include_once("in_session.php");
include_once("in_php_function.php");



$sSid=getSS("s_id");
$sSidEdit=getQS("s_id");

if($sSidEdit!="")$sSid=$sSidEdit;
$isAdmin = getSS("sysadmin");

$sHtml="<div id='divUDPE' data-sid='".$sSid."' class='fl-wrap-col' style=''>"; $aRow=array();

if($sSid==""){

}else{
	include("in_db_conn.php");
	$query="SELECT s_name,s_name_en,s_remark,s_email,s_tel,s_status,s_last_access,s_group,s_pwd,license_lab,license_md FROM p_staff WHERE s_id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s',$sSid);
    if($stmt->execute()){
      $result = $stmt->get_result();
      while($row = $result->fetch_assoc()) {
        $aRow = $row;
      }
    }
    $mysqli->close();

	if(count($aRow)>0){
		$sHide = ($isAdmin)?"":" style='display:none' ";

	    $sHtml.="<div class='fl-wrap-row h-40 row-color row-hover'>
	    	<div class='fl-fix w-150 fl-vmid'>Name</div>
	    	<div class='fl-fill fl-vmid'><input class='w-fill' data-keyid='s_name' data-odata='".$aRow["s_name"]."' value='".$aRow["s_name"]."' /> </div>
	    </div>
	    <div class='fl-wrap-row h-40 row-color row-hover'>
	    	<div class='fl-fix w-150 fl-vmid'>Name English</div>
	    	<div class='fl-fill fl-vmid'><input class='w-fill' data-keyid='s_name_en' data-odata='".$aRow["s_name_en"]."' value='".$aRow["s_name_en"]."' /> </div>
	    </div>
	    <div class='fl-wrap-row h-40 row-color row-hover' $sHide>
	    	<div class='fl-fix w-150 fl-vmid'>Email</div>
	    	<div class='fl-fill fl-vmid'><input class='w-fill' data-keyid='s_email' data-odata='".$aRow["s_email"]."' value='".$aRow["s_email"]."' /> </div>
	    </div>";

	    

	    $sHtml.="<div class='fl-wrap-row h-40 row-color row-hover'>
	    	<div class='fl-fix w-150 fl-vmid'>Tel</div>
	    	<div class='fl-fill fl-vmid'><input class='w-fill' data-keyid='s_tel' data-odata='".$aRow["s_tel"]."' value='".$aRow["s_tel"]."' /> </div>
	    	<div class='fl-fix w-150 fl-vmid' $sHide>Status</div>
	    	<div class='fl-fill fl-vmid'  $sHide><input class='w-fill' data-keyid='s_status' data-odata='".$aRow["s_status"]."' value='".$aRow["s_status"]."' /> </div>
	    </div>";

	    $sHtml.="<div class='fl-wrap-row h-80 row-color row-hover'>
	    	<div class='fl-fix w-150 fl-vmid'>Remark</div>
	    	<div class='fl-fill'><textarea class='w-fill h-fill' data-keyid='s_remark' data-odata='".$aRow["s_remark"]."' >".$aRow["s_remark"]."</textarea></div>
	    </div>
	    <div class='fl-wrap-row h-40 row-color row-hover'>
	    	<div class='fl-fix w-150 fl-vmid'>License Lab #</div>
	    	<div class='fl-fill fl-vmid'><input class='w-fill' data-keyid='license_lab' data-odata='".$aRow["license_lab"]."' value='".$aRow["license_lab"]."' /> </div>
	    </div>
	    <div class='fl-wrap-row h-40 row-color row-hover'>
	    	<div class='fl-fix w-150 fl-vmid'>License MD #</div>
	    	<div class='fl-fill fl-vmid'><input class='w-fill' data-keyid='license_md' data-odata='".$aRow["license_md"]."' value='".$aRow["license_md"]."' /> </div>
	    </div>
	    <div class='fl-fill'></div>
	    <div class='fl-wrap-row h-40 row-color row-hover'>
	    	<div class='fl-fill'></div>
	    	<div id='btnSaveProfile' class='fabtn f-border fl-fix w-100 fl-mid'><i class='fa fa-save fa-lg'> Save</i> </div>
	    	<div class='fl-fill'></div>
	    </div><input style='width:1px;height:1px;text-decoration:none' />
	    ";
	}
}
$sHtml.="</div>";

echo($sHtml);
?>

<script type="text/javascript">
	$(document).ready(function(){

		$("#divUDPE #btnSaveProfile").off("click");
		$("#divUDPE #btnSaveProfile").on("click",function(){
			objRow=$(this).closest("#divUDPE");
			sId = $(objRow).attr("data-sid");
			sName = $(objRow).find("input[data-keyid='s_name']").val();
			sNameEn = $(objRow).find("input[data-keyid='s_name_en']").val();
			sEmail = $(objRow).find("input[data-keyid='s_email']").val();
			sTel = $(objRow).find("input[data-keyid='s_tel']").val();
			sStatus = $(objRow).find("input[data-keyid='s_status']").val();
			sLiLab = $(objRow).find("input[data-keyid='license_lab']").val();
			sLiMd = $(objRow).find("input[data-keyid='license_md']").val();
			sRemark = $(objRow).find("textarea[data-keyid='s_remark']").val();

			sUrl = "setting_a_user.php";
			aData={u_mode:"user_update",pid:sId,name:sName,nameen:sNameEn,email:sEmail,phone:sTel,lilab:sLiLab,limd:sLiMd,status:sStatus,remark:sRemark}
			callAjax(sUrl,aData,function(jRes,retAData){
				if(jRes.res=="1"){
					$.notify("Data Saved","success");
					setDlgResult("REFRESH",objRow);
					//$("#divUSetting #divShowResult").html(jRes.msg);
				}else{
					$.notify("No data saved :"+jRes.msg,"warning");
				}
        	});
			
		});
	});
</script>