<?
    include("in_session.php");
    include_once("in_php_function.php");

    $queue = isset($_POST["queue_s"])?$_POST["queue_s"] : "";
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
    // print_r($loop_sid);
    // print_r($loop_queue);
    // echo "<br>";

    $sJS = "";
    $count_n = 0;

    if(count($loop_sid) > 0){
        foreach($loop_sid as $key_sid => $val_sid){
            $sJS .= '<div class="fl-wrap-col">';
            if(count($loop_queue) > 0){
                foreach($loop_queue as $key_queue => $val_queue){
                    $split_val = explode(":", $val_queue);
                    $split_sid_con = explode("#", $split_val[1]);
                    $split_queue = explode("*", $split_val[0]);
                    $split_room = explode("*", $split_val[0]);
                    $split_sid = explode("^", $val_sid);
                    // print_r($split_sid_con);
                    
                    $sJS .= '<div class="fl-wrap-row" style="min-height: 13%; max-height: 13%;">';
                    foreach($split_sid as $key_split_sid => $val_split_sid){
                        if($split_sid_con[0] == $val_split_sid){
                            // echo $split_sid_con[1];
                            if($split_sid_con[1] == 2){
                                $sJS .= '<div class="fl-fill font-s-6 monitor-title-sub monitor-alert-already border">';
                            }
                            else{
                                $sJS .= '<div class="fl-fill font-s-6 monitor-title-sub">';
                            }
                            $sJS .= '<b><span>'.$split_queue[0].'</span></b>';
                            $sJS .= '</div>';
                            if($split_sid_con[1] == 2){
                                $sJS .= '<div class="fl-fill font-s-6 monitor-title-sub monitor-alert-already border">';
                            }
                            else{
                                $sJS .= '<div class="fl-fill font-s-6 monitor-title-sub">';
                            }
                            $sJS .= '<b><span>'.$split_room[1].'</span></b>';
                            $sJS .= '</div>';
                            // echo $split_queue[0]."/".$split_room[1];
                        }
                    }

                    $sJS .= '</div>';
                }
            }
            $sJS .= '</div>';
        }
    }

    echo $sJS;
?>