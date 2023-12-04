<?
    include("in_session.php");
    include("monitor_in_head_script.php");
	include_once("in_php_function.php");
    include("in_db_conn.php");

    $sClinicID = getQS("clinic_id");
    $sSID = getQS("s_id");
    $sSound = getQS("sound"); // muted = off
    // echo $sClinicID."/".$sSID.":".$sSound;

    $data_head_wait = array();
    if($sSID == "D07" || $sSID == "D03"){
        $query = "select distinct room_name from i_room_list where section_id in ('D07', 'D03') and clinic_id = ?;";
    }
    else if($sSID == "D05" || $sSID == "D06" || $sSID == "D02" || $sSID == "D09"){
        $query = "select distinct room_name from i_room_list where section_id in ('D05', 'D06', 'D02', 'D09') and clinic_id = ? and room_status = 1;";
    }

    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("s", $sClinicID);
    $loop_data = 0;

    if($stmt->execute()){
        $stmt->bind_result($room_name);
        while ($stmt->fetch()) {
            $data_head_wait[$loop_data] = $room_name;
            $loop_data = $loop_data+1;
        }
        // print_r($data_head_wait);
    }
    else{
        $msg_error .= $stmt->error;
    }
    $stmt->close();
    $mysqli->close();

    $sJS_head_sub = "";
    $sJS_head_sub .= '<div class="fl-wrap-row">';
    foreach($data_head_wait as $key => $val){
        $sJS_head_sub .= '<div class="fl-fill font-s-6 monitor-title-head">';
        $sJS_head_sub .= '<b><span>'.$val.'</span></b>';
        $sJS_head_sub .= '</div>';
    }
    $sJS_head_sub .= '</div>';

    $sJS_head_sub_head = "";
    $loop_hr_href = 1;
    $check_length_data = count($data_head_wait);
    $sJS_head_sub_head .= '<div class="fl-wrap-row">';
    foreach($data_head_wait as $key => $val){
        $sJS_head_sub_head .= '<div class="fl-fill font-s-6 monitor-title-head">';
        $sJS_head_sub_head .= '<b><span>หมายเลข</span></b>';
        $sJS_head_sub_head .= '</div>';
        $sJS_head_sub_head .= '<div class="fl-fill font-s-6 monitor-title-head">';
        $sJS_head_sub_head .= '<b><span>ห้องบริการ</span></b>';
        $sJS_head_sub_head .= '</div>';
        if($check_length_data == 2){
            $sJS_head_sub_head .= '<div class="hr-vl-2"></div>';
        }
        else if($check_length_data == 5){
            $sJS_head_sub_head .= '<div class="hr-vl-5-'.$loop_hr_href.'"></div>';
            $loop_hr_href = $loop_hr_href+1;
        }
    }
    $sJS_head_sub_head .= '</div>';
?>

<audio id="fix_sound" class="fix-sound" src="" webkit-playsinline="true" playsinline="true"></audio>
<div id="monitor_main" class="fl-wrap-col" style="background-color: #00BCFF;">
<span id="data_defult" data-clinic="<? echo $sClinicID;?>" data-sid="<? echo $sSID; ?>" data-sound="<? echo $sSound; ?>" ></span>
    <div class="smallfont4" id="show_err"></div>
    
    <div clas="fl-wrap-row holiday-mt-01">
        <div class="fl-fill">
            <? echo $sJS_head_sub; ?>
            <? echo $sJS_head_sub_head; ?>
            <div class="fl-wrap-row" style="min-height: 2%; max-height: 2%;">
                <div class="fl-fill">
                    <hr class="holiday-mt-01" style="max-width: 98%; border: 4px solid #FFFFFF;">
                </div>
            </div>
        </div>
    </div>

    <!-- Data Wait -->
    <div class="fl-wrap-row holiday-mt-0" id="monitor_wait_sub" style="min-height: 66%; max-height: 66%;">
        <? include("monitor_queue_wait.php"); ?>
    </div>

    <!-- Data alert already -->
    <div class="fl-wrap-row" style="min-height: 1%; max-height: 1%;">
        <div class="fl-fill">   
            <hr class="holiday-mt-01" style="max-width: 95%; border: 4px solid #FFFFFF;">
        </div>
    </div>
    
    <div class="fl-wrap-row" id="monitor_alert_sub_already" style="min-height: 12%; max-height: 12%;">
        <? include("monitor_queue_alert_already.php"); ?>
    </div>

    <div class="fl-wrap-row" style="min-height: 1%; max-height: 1%;">
        <div class="fl-fill">   
            <hr class="holiday-mt-01" style="max-width: 95%; border: 4px solid #FFFFFF;">
        </div>
    </div>
    <b class="holiday-ml-4 font-s-4"><span>หมายเหตุ: หมายเลขคิวที่ถูกเรียกแล้ว, ( ) = หมายเลขห้องบริการ</span></b>

    <span id="monitor_alert_sub">
        <!-- Button for run sound -->
        <button id="play_sound" class="btn-non-show"></button>
    </span>
</div>

<script>
    $(document).ready(function(){
        var audioAddress = [];
        var audioAddress_test = [];
        var d = new Date();
        var month = d.getMonth()+1;
        var day = d.getDate();
        var year = d.getFullYear();
        if (month < 10) {
            month = "0" + month;
        }

        var date_cur = year+"-"+month+"-"+day;
        var clinic_id_post = $("#monitor_main #data_defult").data("clinic");
        var sid = $("#monitor_main #data_defult").data("sid");

        var aData_post = {
            col_date: date_cur,
            clinic_id: clinic_id_post,
            s_id: sid
        };

        $.ajax({url: "monitor_queue_ajax.php", 
            method: "POST",
            cache: false,
            data: aData_post,
            success: function(result){
                var check_loop = result.split(",");
                var length_array = 9999;
                var queue_already = "";
                var room_already = "";

                // AJAX show queue wait
                $.ajax({url: "monitor_queue_ajax_wait.php", 
                    method: "POST",
                    cache: false,
                    data: aData_post,
                    success: function(result){
                        // console.log(result);
                        var check_loop = result.split("/");
                        var sid_loop = check_loop[1];
                        var queue_loop = check_loop[0].split(",");
                        var queue = "";

                        $.each(queue_loop, function(x, values){
                            queue = queue+","+values;
                        });
                        queue = queue.substring(1);
                        // console.log(queue);

                        var aData = {
                            queue_s: queue,
                            sid_s: sid_loop
                        }

                        $.ajax({url: "monitor_queue_wait.php", 
                            method: "POST",
                            cache: false,
                            data: aData,
                            success: function(result){
                                // console.log(result);
                                $("#monitor_main #monitor_wait_sub").children().remove();
                                $("#monitor_main #monitor_wait_sub").append(result);
                            }
                        });
                    }
                });

                    // AJAX get data alert already and show detail
                    $.ajax({url: "monitor_queue_ajax_already.php", 
                        method: "POST",
                        cache: false,
                        data: aData_post,
                        success: function(result){
                            var check_loop = result.split("/");
                            var sid_loop = check_loop[1];
                            var queue_loop = check_loop[0].split(",");
                            var queue = "";

                            $.each(queue_loop, function(x, values){
                                queue = queue+","+values;
                            });
                            queue = queue.substring(1);
                            // console.log(sid_loop+"/"+queue);

                            var aData_already = {
                                queue_s: queue,
                                sid_s: sid_loop
                            }

                            $.ajax({url: "monitor_queue_alert_already.php", 
                                method: "POST",
                                cache: false,
                                data: aData_already,
                                success: function(result){
                                    // console.log(result);
                                    $("#monitor_main #monitor_alert_sub_already").children().remove();
                                    $("#monitor_main #monitor_alert_sub_already").append(result);
                                }
                            });
                        }
                    });

                if(check_loop.length > 0 && check_loop[0] != ""){
                    console.log(check_loop.length+"/"+check_loop[0]);
                    
                    var queue_s = "";
                    var room_s = "";
                    var coldate = "";
                    var queue_present = [];
                    var room_present = [];

                    $.each(check_loop, function(x, values){
                        var queue = values.split("/")[0].split(":")[0];
                        var room = values.split("/")[0].split(":")[1];
                        var col_date = values.split("/")[1];

                        queue_s = queue_s+","+queue;
                        room_s = room_s+","+room;
                        coldate = coldate+","+col_date;

                        queue_present.push(queue);
                        room_present.push(room);

                        audioAddress.push (
                            "assets/mp3_monitor/press_num.wav",
                            (typeof(convert_number_sound(queue).split(",")[0]) != "undefined"? convert_number_sound(queue).split(",")[0] : "N1"),
                            (typeof(convert_number_sound(queue).split(",")[1]) != "undefined"? convert_number_sound(queue).split(",")[1] : "N1"),
                            "assets/mp3_monitor/at_room.wav",
                            (typeof(convert_number_sound(room).split(",")[0]) != "undefined"? convert_number_sound(room).split(",")[0] : "N1"),
                            (typeof(convert_number_sound(room).split(",")[1]) != "undefined"? convert_number_sound(room).split(",")[1] : "N1"),
                            "assets/mp3_monitor/kub.wav"
                        );
                    });

                    audioAddress = audioAddress.filter(function(item) 
                    { return item !== 'N1'; });
                    console.log(audioAddress);

                    length_array = audioAddress.length;
                    var loop_length_present = 0;
                    var length_hraf = (length_array/check_loop.length);
                    var loop_queue_present = 0;
                    // console.log(length_hraf);

                    audioAddress.map(function(url){
                        audioAddress_test.push(new Audio(url));
                    });

                    // console.log(audioAddress_test);
                    $('#play_sound').click();
        
                    $("#play_sound").on("click", function() {
                        var sounds = audioAddress_test;

                        var index = 0;
                        function recursive_play()
                        {   
                            sounds[index].load();
                            if(index+1 === sounds.length)
                            {
                                play(sounds[index],null);
                            }
                            else
                            {
                                try {
                                play(sounds[index],function(){index++; recursive_play();});
                                }
                                catch(err) {
                                    document.getElementById("show_err").innerHTML = err.message;
                                }
                            }
                            // console.log(index);
                        }

                        recursive_play();

                        function play(audio, callback) {

                            // Update queue list alert
                            var coldate_con = coldate.substr(1).split(",");
                            var clinic_id = $("#monitor_main #data_defult").data("clinic");
                            saveFormData_document(clinic_id, queue_present[loop_queue_present], coldate_con[loop_queue_present]);
                            
                            // Error alert in screen
                            var promise = audio.play();
                            if (promise) {
                                //Older browsers may not return a promise, according to the MDN website
                                promise.catch(function(error) { 
                                    document.getElementById("show_err").innerHTML = error; 
                                });
                            }
                            
                            audio.play();

                            if(callback){
                                audio.onended = callback;
                            }

                            // AJAX show queue present
                            loop_length_present = loop_length_present+1;
                                
                            if(loop_length_present == 1){
                                var aData = {
                                    queue_s:queue_present[loop_queue_present],
                                    room_s:room_present[loop_queue_present]
                                }
                                
                                $.ajax({url: "monitor_queue_alert.php", 
                                    method: "POST",
                                    cache: false,
                                    data: aData,
                                    success: function(result){
                                        // console.log(result);
                                        $("#monitor_main #monitor_alert_sub").children().remove();
                                        // $("#monitor_main #monitor_alert_sub").append(result);
                                    }
                                });

                                $.ajax({url: "monitor_queue_ajax_wait.php", 
                                    method: "POST",
                                    cache: false,
                                    data: aData_post,
                                    success: function(result){
                                        // console.log(result);
                                        var check_loop = result.split("/");
                                        var sid_loop = check_loop[1];
                                        var queue_loop = check_loop[0].split(",");
                                        var queue = "";

                                        $.each(queue_loop, function(x, values){
                                            queue = queue+","+values;
                                        });
                                        queue = queue.substring(1);
                                        console.log(queue);

                                        var aData = {
                                            queue_s: queue,
                                            sid_s: sid_loop
                                        }

                                        $.ajax({url: "monitor_queue_wait.php", 
                                            method: "POST",
                                            cache: false,
                                            data: aData,
                                            success: function(result){
                                                // console.log(result);
                                                $("#monitor_main #monitor_wait_sub").children().remove();
                                                $("#monitor_main #monitor_wait_sub").append(result);
                                            }
                                        });
                                    }
                                });

                                $.ajax({url: "monitor_queue_ajax_already.php", 
                                    method: "POST",
                                    cache: false,
                                    data: aData_post,
                                    success: function(result){
                                        var check_loop = result.split("/");
                                        var sid_loop = check_loop[1];
                                        var queue_loop = check_loop[0].split(",");
                                        var queue = "";

                                        $.each(queue_loop, function(x, values){
                                            queue = queue+","+values;
                                        });
                                        queue = queue.substring(1);
                                        // console.log(sid_loop+"/"+queue);

                                        var aData_already = {
                                            queue_s: queue,
                                            sid_s: sid_loop
                                        }

                                        $.ajax({url: "monitor_queue_alert_already.php", 
                                            method: "POST",
                                            cache: false,
                                            data: aData_already,
                                            success: function(result){
                                                // console.log(result);
                                                $("#monitor_main #monitor_alert_sub_already").children().remove();
                                                $("#monitor_main #monitor_alert_sub_already").append(result);
                                            }
                                        });
                                    }
                                });

                                // loop_length_present = 0;
                                loop_queue_present = loop_queue_present+1;
                            }

                            if(loop_length_present == length_hraf){
                                loop_length_present = 0;
                            }

                            length_array = length_array-1; //check loop last
                            // console.log(length_array);
                        }
                    });
                }

                // Reload check data every 5 sec
                var mode = "start";
                setInterval(function(){
                    $('#play_sound').click();
                    if(length_array == 9999){
                        $.ajax({url: "monitor_queue_ajax_wait_check.php", 
                            method: "POST",
                            cache: false,
                            data: aData_post,
                            success: function(result){
                                var check_loop = result.split(",");
                                if(check_loop.length > 0 && check_loop[0] != ""){
                                    length_array = 0;
                                    mode = "start";
                                }
                            }
                        });

                        $.ajax({url: "monitor_queue_ajax_already.php", 
                            method: "POST",
                            cache: false,
                            data: aData_post,
                            success: function(result){
                                var check_loop = result.split(",");
                                if(check_loop.length > 0 && check_loop[0] != ""){
                                    length_array = 0;
                                    mode = "start";
                                }
                            }
                        });

                        $.ajax({url: "monitor_queue_ajax.php", 
                            method: "POST",
                            cache: false,
                            data: aData_post,
                            success: function(result){
                                var check_loop = result.split(",");
                                if(check_loop.length > 0 && check_loop[0] != ""){
                                    length_array = 0;
                                    mode = "start";
                                }
                            }
                        });
                    }

                    if(length_array < 1 && mode == "start"){
                        loadlink(); // this will run after every 5 seconds
                        mode = "end";
                    }
                }, 5000);
        }});
    });

    function saveFormData_document(clinic_id, queue, collect_date){
        var aData = {
            app_mode: "monitor_queue",
            clinic_id: clinic_id,
            queue: queue,
            collect_date: collect_date,
            dataid: [{"queue_call":2}]
        };

        $.ajax({url: "doctor_db_form_update.php", 
            method: "POST",
            cache: false,
            data: aData,
            success: function(result){
                //return success
            }
        });
    }

    function loadlink(){
        var clinic_id = $("#monitor_main #data_defult").data("clinic");
        var sid = $("#monitor_main #data_defult").data("sid");
        var sound_check = $("#monitor_main #data_defult").data("sound");
        var gen_link = "monitor_queue_main.php?clinic_id="+clinic_id+"&s_id="+sid+"&sound="+sound_check;

        $('#monitor_main').load(gen_link, function () {});
    }

    function fetchVideoAndPlay(a) {
        // console.log(a);
        fetch(a)
        .then(response => response.blob())
        .then(blob => {
            audio.srcObject = blob;
            return audio.play();
        })
        .then(_ => {
        // Video playback started ;)
        })
        .catch(e => {
        // Video playback failed ;(
        })
    }

    function convert_number_sound(number){
        var str_con = "";
        if(number <= 10){
            str_con = "assets/mp3_monitor/"+number+".wav";
        }
        else if(number == 11){
            str_con = "assets/mp3_monitor/10.wav,assets/mp3_monitor/ad.wav";
        }
        else if(number > 11 && number < 20){
        var sub_num_e = number.toString().substring(1);
        str_con = "assets/mp3_monitor/10.wav,assets/mp3_monitor/"+sub_num_e+".wav";
        }

        else if(number == 20){
            str_con = "assets/mp3_monitor/20.wav";
        }
        else if(number == 21){
            str_con = "assets/mp3_monitor/20.wav,assets/mp3_monitor/เอ็ด.wav";
        }
        else if(number > 21 && number < 30){
        var sub_num_e = number.toString().substring(1);
        str_con = "assets/mp3_monitor/20.wav,assets/mp3_monitor/"+sub_num_e+".wav";
        }

        else if(number == 30){
            str_con = "assets/mp3_monitor/30.wav";
        }
        else if(number == 31){
            str_con = "assets/mp3_monitor/30.wav,assets/mp3_monitor/เอ็ด.wav";
        }
        else if(number > 31 && number < 40){
        var sub_num_e = number.toString().substring(1);
        str_con = "assets/mp3_monitor/30.wav,assets/mp3_monitor/"+sub_num_e+".wav";
        }

        else if(number == 40){
            str_con = "assets/mp3_monitor/40.wav";
        }
        else if(number == 41){
            str_con = "assets/mp3_monitor/40.wav,assets/mp3_monitor/เอ็ด.wav";
        }
        else if(number > 41 && number < 50){
        var sub_num_e = number.toString().substring(1);
        str_con = "assets/mp3_monitor/40.wav,assets/mp3_monitor/"+sub_num_e+".wav";
        }

        return str_con;
    }
</script>