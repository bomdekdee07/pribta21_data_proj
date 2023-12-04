<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $sid = getSS("s_id");

    $days_array = array(1=>"จันทร์", 2=>"อังคาร", 3=>"พุธ", 4=>"พฤหัส", 5=>"ศุกร์", 6 =>"เสาร์", 7=>"อาทิตย์");
    $data_item = array();
    $query = "SELECT item.item_code,
        item.item_name,
        d_open.days_code,
        time_c.time_close,
        award.correct_number,
        item.upd_date
    from items_master item
    left join time_close time_c on(time_c.item_code = item.item_code)
    LEFT JOIN days_open d_open on(d_open.item_code = item.item_code)
    left join award_number award on(award.item_code = item.item_code and award.upd_date = curdate())
    order by time_c.time_close DESC, item.item_code, d_open.days_code;";

    $stmt = $mysqli->prepare($query);
    
    $old_itemCode = "";
    $loopCount = 0;
    $str_start_name_day = "";
    $str_end_name_day = "";
    if($stmt->execute()){
        $stmt->bind_result($item_code, $item_name, $days_code, $time_close, $correct_number, $update_date);
        while($stmt->fetch()){
            if($old_itemCode != $item_code){
                $loopCount = 0;
                $str_end_name_day = "";
            }
            $loopCount += 1;

            $data_item[$item_code]["code"] = $item_code;
            $data_item[$item_code]["name"] = $item_name;
            $data_item[$item_code]["time"] = $time_close;
            $data_item[$item_code]["award"] = $correct_number;
            if($loopCount == 1){
                $str_start_name_day = isset($days_code)? $days_array[$days_code]:"";
            }
            if($loopCount > 1 && $loopCount != 0){
                $str_end_name_day = isset($days_code)? $days_array[$days_code]:"";
            }
            $data_item[$item_code]["date"] = $str_start_name_day!=""? $str_start_name_day." - ".$str_end_name_day:"";

            $old_itemCode = $item_code;
        }
    }
    $stmt->close();
    $mysqli->close();

    $html_str = "";
    $html_str .='<div class="fl-wrap-row h-30 font-s-3 fw-b holiday-mt-3">
                    <div class="fl-fix w-40"></div>
                    <div class="fl-fill fl-mid-left border-bt" style="color: #34495E">
                        หวยรายวัน
                    </div>
                    <div class="fl-fix w-40"></div>
                </div>
                <div class="fl-wrap-row h-20"></div>
                <div class="fl-wrap-row h-90">
                    <div class="fl-wrap-col w-40"></div>';

    $loop_count = 0;
    $ml = "";
    $mr = "";
    $time_now = date("H:i:s");
    $bg_color_card = "";
    $font_color = "";
    $today = date("d/m/Y");
    $close_time = "";
    $loop_all = 0;
    $btn_open = "";
    $class_event = "";
    foreach($data_item as $keycode => $val){
        $loop_count += 1;
        $loop_all += 1;
        if($loop_count != 1){
            $ml = "holiday-ml-1";
        }
        else{
            $ml = "";
        }

        
        if($val["time"] > $time_now){
            $bg_color_card = "#27AE60";
            $font_color = "white";
            $close_time = "ปิดรับใน...";
            $btn_open = "btn";
            $class_event = "open-new";
        }
        else{
            $bg_color_card = "#D6DBDF";
            $font_color = "black";
            $close_time = "ปิดรับ";
            $btn_open = "btn-close";
            $class_event = "";
        }

        $html_str .='<div class="fl-wrap-col card-main-col border-line-1 '.$ml.' '.$btn_open.' '.$class_event.'" style="background-color: '.$bg_color_card.';" data-itemCode="'.$val["code"].'">
                        <div class="fl-wrap-row h-25 fw-b">
                            <div class="fl-fix w-30"></div>
                            <div class="fl-fill fl-mid-left font-s-3" style="color: '.$font_color.';">
                                '.$val["name"].'
                            </div>
                        </div>
                        <div class="fl-wrap-row h-20 fw-b">
                            <div class="fl-fix w-30"></div>
                            <div class="fl-fill fl-mid-left font-s-3" style="color: '.$font_color.';">
                                '.$today.'
                            </div>
                        </div>
                        <div class="fl-wrap-row h-15">
                            <div class="fl-fix w-30"></div>
                            <div class="fl-fix w-50 fl-mid-left font-s-1" style="color: '.$font_color.';">
                                เวลาปิด
                            </div>
                            <div class="fl-fill fl-mid-right font-s-1 get-time" style="color: '.$font_color.';">'.$val["time"].'</div>
                        </div>
                        <div class="fl-wrap-row h-15">
                            <div class="fl-fix w-30"></div>
                            <div class="fl-fix w-50 fl-mid-left font-s-1" style="color: '.$font_color.';">
                                สถานะ
                            </div>
                            <div class="fl-fill fl-mid-right font-s-1 bind-time" style="color: '.$font_color.';">
                                '.$close_time.'
                            </div>
                        </div>
                    </div>';
        if($loop_count == 5){
            $html_str .= '<div class="fl-wrap-col w-20"></div> </div>';
            $html_str .= '<div class="fl-wrap-row h-90"> <div class="fl-wrap-col w-40"></div>';
            $loop_count = 0;
        }
    }

    echo $html_str;
?>

<script>
    $(document).ready(function(){
        setTimeout(function(){begintimer()}, 1000);
    });

    var curtime = "ปิดรับ";
    function begintimer(){
        $.each($(".card-main-col"), function(k, v){
            var get_time = "";
            var get_time = $(".get-time")[k].innerHTML;
            var limit = get_time;
            var parselimit=limit.split(":")
            
            var dt = new Date();
            var time_now = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
            var limit2 = time_now;
            var parselimit2 = limit2.split(":");
            // console.log(get_time);

            if(limit != ""){
                parselimit2 = (parselimit2[0]*3600)+(parselimit2[1]*60)+parselimit2[2]*1;
                parselimit = ((parselimit[0]*3600)+(parselimit[1]*60)+parselimit[2]*1)-parselimit2;

                if (parselimit == 0){
                    $(".bind-time")[k].innerHTML = "ปิดรับ";
                    var url_load = "manage_lottery_main.php";
                    $("#detail_sub").load(url_load);
                }
                else{
                    parselimit-=1
                    curhr=parseInt((parselimit%86400)/3600);
                    curmin=parseInt((parselimit%86400)%3600/60);
                    cursec=parseInt(((parselimit%86400)%3600)%60);
                    if (curhr!==0 && curmin!=0 && parselimit > 0){
                        curtime = curhr+":"+curmin+":"+cursec;
                    }
                    else{
                        if(parselimit < 0 || cursec==0 && curmin==0 && curhr==0)
                        {
                            //wait
                        }
                        else
                        {
                            curtime = curhr+":"+curmin+":"+cursec;
                        }
                    
                        // console.log(curtime);
                        $(".bind-time")[k].innerHTML = curtime;
                        setTimeout("begintimer()",1000)
                    }
                }
            }
        })
    }
</script>