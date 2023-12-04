<?
    include_once("in_session.php");
    include("monitor_in_head_script.php");
	include_once("in_php_function.php");

    $sSID = getSS("s_id");
    $sClinicID = getSS("clinic_id");

    if($sSID == "" || $sClinicID == ""){
        exit("Please login again.");
    }

    $sJS = "";
    $sJS_end = "";
    
    $sJS .= '<div class="fl-wrap-col" id="queue_index">';
    $sJS .= '<div class="fl-wrap-row" style="min-height: 70px; max-height: 70px">';
    $sJS .= '<div class="fl-fill border queueindex-head-title">';
    $sJS .= '<div class="fl-fill smallfont5 queueindex-mt-1">';
    $sJS .= '<b><span>Queue Mangement</span></b>';
    $sJS .= '</div>';
    $sJS .= '</div>';
    $sJS .= '</div>';

    $sJS_end .= '<div class="fl-wrap-row monitir-border-bt" style="min-height: 350px; max-height: 350px">';
    $sJS_end .= '<div class="fl-fix w-250">';
    $sJS_end .= '<button type="button" class="bt holiday-mt-2 holiday-ml-1" style="height: 100px;" id="test"> ห้องตรวจ, ห้องแลป, ห้องฉีดยา</button>';
    $sJS_end .= '</div>';
    $sJS_end .= '<div class="fl-fix w-250">';
    $sJS_end .= '<button type="button" class="bt holiday-mt-2 holiday-ml-1" style="height: 100px;" id="test2"> ห้องด้านหน้า</button>';
    $sJS_end .= '</div>';
    $sJS_end .= '</div>';
    $sJS_end .= '</div>';

    $sJS_end .= '<div class="fl-wrap-col" id="queue_main_hide"></div>';
    
    echo $sJS;
    echo $sJS_end;
?>


<script>
    $(document).ready(function() {
        $("#queue_main_hide").hide();

        $("#queue_index #test").unbind("click");
        $("#queue_index #test").on("click", function(){
            $.ajax({url: "monitor_queue_main.php?clinic_id=IHRI&s_id=D05", 
                method: "POST",
                cache: false,
                // data: aData_post,
                success: function(result){
                    $("#queue_index").remove();
                    
                    $("#queue_main_hide").children().remove();
                    $("#queue_main_hide").append(result);
                    $("#queue_main_hide").show();
                }
            });
        });

        $("#queue_index #test2").unbind("click");
        $("#queue_index #test2").on("click", function(){
            $.ajax({url: "monitor_queue_main.php?clinic_id=IHRI&s_id=D07", 
                method: "POST",
                cache: false,
                // data: aData_post,
                success: function(result){
                    $("#queue_index").remove();
                    
                    $("#queue_main_hide").children().remove();
                    $("#queue_main_hide").append(result);
                    $("#queue_main_hide").show();
                }
            });
        });
    });
</script>