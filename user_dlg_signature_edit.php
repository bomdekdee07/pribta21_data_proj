<?
include_once("in_session.php");
include_once("in_php_function.php");
$sSid=getSS("s_id");
$sFile="staff_signature/".$sSid."_TH.png";
$sToday=date("YmdHis");
if(file_exists($sFile)){
	$sFile.="?dt=".$sToday;
}else{
	$sFile="";
}

if($sSid==""){
	echo("Please login.");
	exit();
}

?>
<div id='divUDSE' class='fl-wrap-col' data-sid='<? echo($sSid); ?>'>
	<div class='fl-wrap-row h-30'>
		<div class='fl-fill fl-mid fs-xxsmall'>
			<input id='fileSig' type='file'accept="image/png" />**ขนาดภาพ 400x150 px เท่านั้น
		</div>
		<div id='btnUploadSig' class='fabtn fl-fix w-30 fl-mid savebtn'><i class='fa fa-file-upload fa-lg'></i></div>
		<div class='fl-fix w-40 '></div>
	</div>
	<div class='fl-wrap-row h-30'>
		<div class='fl-fix w-40 fl-mid-left fs-xxsmall'></div>
		<div class='fl-fix w-40 fl-mid-left fs-xxsmall'>
			<label><input type="radio" id="type_leg" name="type_leg" value="TH" checked /> TH</label>
		</div>
		<div class='fl-fix w-100 fl-mid-left fs-xxsmall'>
			<label><input type="radio" id="type_leg" name="type_leg" value="EN" /> EN</label>
		</div>
	</div>
	<div class='fl-wrap-row'  style='background-color: white;overflow: hidden'>
		<div class='fl-fill'>
			<img id='imgDefSig' src='<? echo($sFile); ?>' style='display:none' />
			<canvas id="canvSignature" class='f-border' width="400" height="150"></canvas>
		</div>
		<div class='fl-wrap-col w-40'>
			<div id='btnClearSig' class='fl-fix h-30 fl-mid fabtn' title='Clear Signature' style='color:orange'>
				<i class='fa fa-eraser fa-lg'></i>
			</div>
			<div id='btnReloadSig' class='fl-fix h-30 fl-mid fabtn' title='Reload Signature' style='color:blue'>
				<i class='fa fa-sync-alt fa-lg'></i>
			</div>
			<div class='fl-fill'></div>

		</div>
		
		
	</div>
	<div class='fl-wrap-row h-50 fl-mid'>
		<div class='fl-fill'></div>
		<div id='btnSaveSig' class='fl-fix w-80 fabtn savebtn f-border'>Save</div>
		<div class='fl-fill'></div>
		<div id='btnCloseSig' class='fl-fix w-80 fabtn savebtn f-border'>Close</div>
		<div id='btnSaveSig-loader' class='fl-fix fl-mid'><i class='fa fa-spinner fa-spin fa-lg' 
		style='display:none'></i></div>
		<div class='fl-fill'></div>

	</div>
</div>


<script type="text/javascript">
	var canSig = document.getElementById('canvSignature'),
	context = canSig.getContext('2d');
	context.strokeStyle = "black";
	context.lineJoin = "round";
	context.lineWidth = 2;
	var imgSig = new Image();

	function getPosition(mouseEvent, sigCanvas) {
    	var rect = sigCanvas.getBoundingClientRect();
	    return {
	      X: mouseEvent.clientX - rect.left,
	      Y: mouseEvent.clientY - rect.top
	    };
	}

	function add_default_signature(){
		
		//sWidth = $("#divUDSE #canvSignature").outerWidth();
		//sHeight = $("#divUDSE #canvSignature").outerHeight();
		//console.log(sWidth+":"+sHeight);
		imgSig.src = $("#divUDSE #imgDefSig").attr('src');
		imgSig.onload = function(){
			context.drawImage(imgSig, 0, 0);
		}
	}

	function drawLine(mouseEvent, sigCanvas, context) {
		var position = getPosition(mouseEvent, sigCanvas);
		context.lineTo(position.X, position.Y);
		context.stroke();
		$("#divUDSE #btnSaveSig").addClass("bg-error");
	}

	function finishDrawing(mouseEvent, sigCanvas, context) {
	// draw the line to the finishing coordinates
		drawLine(mouseEvent, sigCanvas, context);
		context.closePath();
	// unbind any events which could draw
		$(sigCanvas).unbind("mousemove")
		.unbind("mouseup")
		.unbind("mouseout");
	}

	// Clear the canvas context using the canvas width and height
	function clearCanvas(canvas, ctx) {
		ctx.clearRect(0, 0, canvas.width, canvas.height);
	}


	// create a function to pass touch events and coordinates to drawer
	function draw(event) {
	  var coors = {
	    x: event.targetTouches[0].pageX,
	    y: event.targetTouches[0].pageY
	  };

	  // Now we need to get the offset of the canvas location
	  var obj = sigCanvas;

	  if (obj.offsetParent) {
	    // Every time we find a new object, we add its offsetLeft and offsetTop to curleft and curtop.
	    do {
	      coors.x -= obj.offsetLeft;
	      coors.y -= obj.offsetTop;
	    }
	    // The while loop can be "while (obj = obj.offsetParent)" only, which does return null
	    // when null is passed back, but that creates a warning in some editors (i.e. VS2010).
	    while ((obj = obj.offsetParent) != null);
	  }

	  // pass the coordinates to the appropriate handler
	  drawer[event.type](coors);

	}



	$(document).ready(function(){
		add_default_signature();

		$("#divUDSE #type_leg").off("change");
		$("#divUDSE #type_leg").on("change", function(){
			var dt = new Date();
			var time_now = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
			var sid = $("#divUDSE").data("sid");
			var imgSig = new Image();
			var type_leg =  $("#type_leg:checked").val();
			var check_url = "staff_signature/"+sid+"_"+type_leg+".png";

			$.ajax({
				url: check_url,
				type: "HEAD",
				error: function(){
					clearCanvas(canSig,context);
					context.drawImage(imgSig, 0, 0);
				},
				success: function(){
					clearCanvas(canSig,context);
					context.drawImage(imgSig, 0, 0);
					imgSig.src = "staff_signature/"+sid+"_"+type_leg+".png?"+time_now;
					imgSig.onload = function(){
						context.drawImage(imgSig, 0, 0);
					}
				}
			})
		});

		$("#divUDSE #btnUploadSig").off("click");
		$("#divUDSE #btnUploadSig").on("click",function(){
			objThis = $(this);
			objMain = $(this).closest("#divUDSE");
			objLoad = $(this).find("#btnUpload-loader");
			objStart = $(this).find(".savebtn");
			var type_leg =  $("#type_leg:checked").val();

			aData = new FormData();
			var files = $('#divUDSE #fileSig')[0].files[0];
			aData.append('u_mode', "upload_signature");
			aData.append('signature_file', files);
			aData.append('type_leg', type_leg);
			
	        startLoad($(objStart),$(objLoad));

	        callAjaxForm("staff_a.php",aData,function(jRes,retAData){
	         if(jRes.res=="1"){
	          	$.notify("File Upload","success");
	          	setDlgResult("REFRESH",$(objThis));

	          	sToday = new Date();
	          	sSid=$(objMain).attr("data-sid");
	          	$(objMain).find("#imgDefSig").attr('src',"staff_signature/"+sSid+"_"+type_leg+".png?k="+sToday.toString());
				imgSig.src = $(objMain).find("#imgDefSig").attr('src');
				//imgSig.onload = function(){

				context.drawImage(imgSig, 0, 0);
				//}

	         }else{
	         	$.notify(jRes.msg);
	         	endLoad($(objStart),$(objLoad));
	         }
	         
	        });
		});

		$("#divUDSE #btnClearSig").off("click");
		$("#divUDSE #btnClearSig").on("click",function(){
			//add_default_signature();
			sSid=$("#divUDSE").data("sid");
			var type_leg =  $("#type_leg:checked").val();
			objThis=$(this);
			$.ajax({
				url: "user_dlg_signature_delete.php",
				type: "POST",
				data: {path: "staff_signature/"+sSid+"_"+type_leg+".png"},
				success: function(dataResult){
					if(dataResult = 1){
						clearCanvas(canSig,context);
						setDlgResult("REFRESH",$(objThis));
						$("#divUDSE #btnCloseSig").click();
					}
					else{
						alert("ลบไม่สำเร็จ");
					}
				}
			});
		});

		$("#divUDSE #btnReloadSig").off("click");
		$("#divUDSE #btnReloadSig").on("click",function(){
			//add_default_signature();
			clearCanvas(canSig,context);
			add_default_signature();
			$("#divUDSE #btnSaveSig").removeClass("bg-error");
		});

		$("#divUDSE #btnCloseSig").off("click");
		$("#divUDSE #btnCloseSig").on("click",function(){
			//add_default_signature();
			if($("#divUDSE .bg-error").length){
				if(confirm("ยังไม่ได้บันทึกข้อมูล ละทิ้ง?\r\nData is not saved?")){
					closeDlg($(this),getDlgResult($(this)));
				}else{

				}
			}else{
				closeDlg($(this),getDlgResult($(this)));
			}
			
		});


		  // This will be defined on a TOUCH device such as iPad or Android, etc.
		var is_touch_device = 'ontouchstart' in document.documentElement;

		if (is_touch_device) {
		    // create a drawer which tracks touch movements
		    var drawer = {
		      isDrawing: false,
		      touchstart: function(coors) {
		        context.beginPath();
		        context.moveTo(coors.x, coors.y);
		        this.isDrawing = true;
		      },
		      touchmove: function(coors) {
		        if (this.isDrawing) {
		          context.lineTo(coors.x, coors.y);
		          context.stroke();
		        }
		      },
		      touchend: function(coors) {
		        if (this.isDrawing) {
		          this.touchmove(coors);
		          this.isDrawing = false;
		        }
		      }
		    };

		    

		    // attach the touchstart, touchmove, touchend event listeners.
		    canSig.addEventListener('touchstart', draw, false);
		    canSig.addEventListener('touchmove', draw, false);
		    canSig.addEventListener('touchend', draw, false);

		    // prevent elastic scrolling
		    canSig.addEventListener('touchmove', function(event) {
		      event.preventDefault();
		    }, false);
		} else {

			// start drawing when the mousedown event fires, and attach handlers to
			// draw a line to wherever the mouse moves to
			$("#canvSignature").mousedown(function(mouseEvent) {
				var position = getPosition(mouseEvent, canSig);
				context.moveTo(position.X, position.Y);
				context.beginPath();
				// attach event handlers
				$(this).mousemove(function(mouseEvent) {
					drawLine(mouseEvent, canSig, context);
				}).mouseup(function(mouseEvent) {
					finishDrawing(mouseEvent, canSig, context);
				}).mouseout(function(mouseEvent) {
					finishDrawing(mouseEvent, canSig, context);
				});
			});

		}
		
		$("#divUDSE #btnSaveSig").off("click");
		$("#divUDSE #btnSaveSig").on("click",function(){
			var dataURL = canSig.toDataURL();
			var type_leg =  $("#type_leg:checked").val();
			objThis=$(this);
			sUrl="staff_a.php";
			aData={u_mode:"update_signature",imgsig:dataURL, "type_leg":type_leg}
			startLoad($("#divUDSE .savebtn"),$("#divUDSE #btnSaveSig-loader"));
			callAjax(sUrl,aData,function(jRes,retAData){
				if(jRes.res=="1"){
					$.notify("Signature Updated","success");
					$("#divUDSE #btnSaveSig").removeClass("bg-error");
					setDlgResult("REFRESH",$(objThis));
				}else{
					
				}
				endLoad($("#divUDSE .savebtn"),$("#divUDSE #btnSaveSig-loader"));
        	});
		});




		/*
		var dataURL = canvas.toDataURL();
		$.ajax({
	      type: "POST",
	      url: "script.php",
	      data: { 
	         imgBase64: dataURL
	      }
	    }).done(function(o) {
	      console.log('saved'); 
	      // If you want the file to be visible in the browser 
	      // - please modify the callback in javascript. All you
	      // need is to return the url to the file, you just saved 
	      // and than put the image in your browser.
	    });
	    */
	});


</script>