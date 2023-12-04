<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $data_loop_uid = array();
    $query = "select pid, uid_new_gen from temp_genuid_real order by pid;";
    $stmt = $mysqli->prepare($query);
    
    if($stmt->execute()){
        $stmt->bind_result($pid, $uid);
        while($stmt->fetch()){
            $data_loop_uid[$pid]["uid"] = $uid;
        }
    }
    $stmt->close();
    $mysqli->close();

    $js_bindHtml = "";
    foreach($data_loop_uid as $key_pid => $val){
        $js_bindHtml .= '<div class="obj_new_uid" data-pid="'.$key_pid.'" data-uid="'.$val["uid"].'" >'.$val["uid"].$key_pid.'</div>';
    }
    echo $js_bindHtml;
?>

<script>
    $(document).ready(function(){
        $(".obj_new_uid").each(function(){
            var pidS = $(this).data("pid");
            var uidS = $(this).data("uid");

            var aData = {
                u_mode: "update_old_uid",
                pid: pidS,
                uid: uidS
            };
            // console.log(aData);
            $.ajax({url: "gen_new_uid_loop_update.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    console.log("susses: "+result);
                }
            });
        });
    });
</script>