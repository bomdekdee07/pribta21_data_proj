<? include("in_session.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>PRIBTA CLINIC</title>
	<meta charset="UTF-8">
	<script src="assets/js/jquery-3.6.0.min.js"></script>  
	<script src="assets/js/jquery-ui.min.js"></script> 
	<script src="assets/js/notify.min.js"></script>  
	<script src="assets/js/pribta.js?t<? echo("=".time()); ?>"></script>  

	<link rel="stylesheet" href="assets/js/jquery-ui.min.css" />
	<link rel="stylesheet" href="assets/css/pribta.css?t<? echo("=".time()); ?>" />
	<link rel="shortcut icon" href="assets/image/favicon.ico" type="image/x-icon" />
	<link rel="icon" href="assets/image/favicon.ico" type="image/x-icon" />
	<link href="assets/font-awesome/css/all.min.css" rel="stylesheet" />

<?
	include_once("in_php_function.php");
	$sSite = getQS("site");
	$sClinicId=easy_dec($sSite);
	$sIsServer=getQS("server");

	$sWelcome = "<div id='btnToggleServer' class='fl-fill fl-mid' data-clinicid='$sClinicId'>Welcome to $sClinicId</div>"
?>
</head>

<body id='pribtaBody' style='min-width: 1024px' >
	<div id='pribta21' class='mainbody fl-wrap-col'>
		<div class='fl-wrap-row h-50'>
			<div class='fl-fill fl-mid'><? echo($sWelcome); ?></div>
			<div id='divHSetting' class='fl-wrap-row' style='display:none'>
				<div class='fl-fix fl-mid'><input id='chkPrint' class='bigcheckbox' title='Check if this is print server' type='checkbox'  <? echo( (($sIsServer=="1")?"checked":"")); ?> /></div>
			</div>
		</div>
		<?
			if($sClinicId!="") include("queue_button.php");
			else echo("ระบบไม่พบ Clinic บริการที่ท่านเลือก กรุณาติดต่อเจ้าหน้าที่ระบบ Pribta21");
		?>
	</div>
</body>
<script>
var pausePrint = false;

function popup(url,name,windowWidth,windowHeight){   
	myleft=(screen.width)?(screen.width-windowWidth)/2:100;
	mytop=(screen.height)?(screen.height-windowHeight)/2:100;  
	properties = "width="+windowWidth+",height="+windowHeight;
	properties +=",scrollbars=yes, top="+mytop+",left="+myleft;  
	window.open(url,name,properties);
}


$(function() {
	
    //Master Code here *DateFormat,emailFormat, etcs. //Jeng
    $("#btnToggleServer").on("dblclick",function(){
    	$("#divHSetting").toggle();
    });

    function tryPrint(){
    	if($("#chkPrint").is(":checked") && pausePrint==false){
    		pausePrint=true;
    		sClinicId = $("#btnToggleServer").attr('data-clinicid');

    		aData = {u_mode:"q_print_list",clinicid:sClinicId};
			callAjax("queue_a.php",aData,function(rtnObj,aData){
				if(rtnObj.res!="1"){
					pausePrint=false;
				}else if(rtnObj.res=="1"){
					var sList = rtnObj.msg;
					//aQList = sList.split(",");
					sClinicId = $("#btnToggleServer").attr('data-clinicid');
					/*
					aQList.forEach(function(iq, ix){
						setTimeout(print_popup(sClinicId,iq),2000);
						delay(2000);
					});
					*/
					
					popup("queue_print.php?clinicid="+sClinicId+"&coltime="+rtnObj.coltime+"&q="+sList,"Queue",300,450);
					setTimeout(function(){pausePrint=false;},4000);
				}
				//
			});

    	}
    }



    function print_popup(sClinicId,sList){
    	var aQList = sList.split(",");
    	let iC = aQList.length;
    	if(iC > 0){
    		popup("queue_print.php?clinicid="+sClinicId+"&q="+aQList[0],"Queue",300,450);	
			let aNew = "";
			if(aQList.length==1){
				//setTimeout(function(){pausePrint=false;},2000);
			}else{
				aNew = aQList.shift();
				let sList = aNew.toString();
				$.notify(sList);
				setTimeout(function(){print_popup(sClinicId,sList);},4000);
			}
			
    	}else{

    		pausePrint=false;
    	}
    	
    }

    setInterval(tryPrint,5000);



});




</script>
</html>