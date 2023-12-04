<?
    include("in_session.php");
    include_once("in_php_function.php");

    $queue = isset($_POST["queue_s"])?$_POST["queue_s"] : "";
    // $room = isset($_POST["room_al"])?$_POST["room_al"] : "";
    $sid = isset($_POST["sid_s"])?$_POST["sid_s"] : "";

    if($queue != ""){
        $loop_queue = explode(",", $queue);
    }else{
        $loop_queue = array();
    }

    if($sid != ""){
        $loop_sid = explode(",", $sid);
    }else{
        $loop_sid = array();
    }
    // print_r($loop_queue);

    $sJS = "";
    $count_n = 0;
    
    if(count($loop_sid) > 0){
        foreach($loop_sid as $key_sid => $val_sid){
            if($key_sid == 0){
                $sJS .= '<div class="fl-wrap-col holiday-ml-7">';
            }
            else{
                $sJS .= '<div class="fl-wrap-col">';
            }
            $sJS .= '<div class="fl-wrap-row" style="min-height: 20%; max-height: 20%;">';
            if(count($loop_queue) > 0){
                foreach($loop_queue as $key_queue => $val_queue){
                    $split_val = explode(":", $val_queue);
                    $split_queue = explode("*", $split_val[0]);
                    $split_room = explode("*", $split_val[0]);
                    $split_sid = explode("^", $val_sid);
                    
                    foreach($split_sid as $key_split_sid => $val_split_sid){
                        if($split_val[1] == $val_split_sid){
                            $sJS .= '<div class="fl-fix smallfont3 monitor-already-col holiday-ml-3">';
                            $sJS .= '<b><span>'.$split_queue[0].'('.$split_room[1].')</span></b>';
                            $sJS .= '</div>';
                            // echo $split_queue[0]."/".$split_room[1];
                        }
                    }
                }
            }
            $sJS .= '</div>';
            $sJS .= '</div>';
        }
    }

    echo $sJS;
?>
