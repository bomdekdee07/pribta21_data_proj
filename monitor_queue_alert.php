<?
    include("in_session.php");
    include_once("in_php_function.php");

    $queue = isset($_POST["queue_s"])?$_POST["queue_s"] : "";
    $room = isset($_POST["room_s"])?$_POST["room_s"] : "";

    if($queue != ""){
        $loop_queue = explode(",", $queue);
        $loop_room = explode(",", $room);
    }else{
        $loop_queue = array();
    }
    // print_r(explode(",", $queue));

    $sJS = "";
    
    if(count($loop_queue) > 0){
        foreach($loop_queue as $key => $val){
            $sJS .= '   <div class="fl-wrap-row">';
            $sJS .= '       <div class="fl-fill smallfont2-queue monitor-title-detail holiday-mt-00" style="height: 110px;">';
            $sJS .= '           <b><span class="monitor-span">'.$val.'</span></b>';
            $sJS .= '       </div>';
            $sJS .= '       <div class="fl-fill smallfont2-queue monitor-title-detail holiday-mt-00" style="height: 110px;">';
            $sJS .= '           <b><span class="monitor-span">'.$loop_room[$key].'</span></b>';
            $sJS .= '       </div>';
            $sJS .= '   </div>';
        }
    }

    echo $sJS;
?>
