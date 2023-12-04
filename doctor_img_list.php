<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $type_img = (getQS("type_img")!=""? getQS("type_img"): "anal");

    $bind_query = "ssss";
    $array_val = array($uid, $coldate, $coltime, $type_img);
    $row_detail = array();

    $query = "SELECT list_dt.img_id,
        list_dt.img_comment,
        list_dt.upd_date,
        staff.s_name,
        list_dt.type_file,
        list_dt.uid,
        list_dt.collect_date,
        list_dt.collect_time
    from img_uid_info list_dt
    left join p_staff staff on(staff.s_id = list_dt.s_id)
    where list_dt.uid = ? 
    and list_dt.collect_date = ?
    and list_dt.collect_time = ?
    and list_dt.type_img_id = ?
    and list_dt.status = 0
    order by img_seq;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_query, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $row_detail[$row["img_id"]]["name"] = $row["img_id"];
            $row_detail[$row["img_id"]]["type"] = $row["type_file"];
            $row_detail[$row["img_id"]]["comment"] = $row["img_comment"];
            $row_detail[$row["img_id"]]["upd_date"] = $row["upd_date"];
            $row_detail[$row["img_id"]]["user"] = $row["s_name"];

            $row_detail[$row["img_id"]]["uid"] = $row["uid"];
            $row_detail[$row["img_id"]]["collect_date"] = $row["collect_date"];
            $row_detail[$row["img_id"]]["collect_time"] = $row["collect_time"];
        }
    }
    $stmt->close();
    $mysqli->close();

    // login and Check drive NAS
    $user = "pribtatg";
    $password = "@PribTaTG|Img1#";
    $path_str_to = "";
    $uid = str_replace(' ', '', $uid);
    $uid = preg_replace('/-+/', '', $uid);
    $coldate = str_replace(' ', '', $coldate);
    $coldate = preg_replace('/-+/', '', $coldate);
    $path_str_to = $type_img."\\".preg_replace('/[^A-Za-z0-9\-]/', '', $uid)."\\".preg_replace('/[^A-Za-z0-9\-]/', '', $coldate);
    $path_str_img = $type_img."/".preg_replace('/[^A-Za-z0-9\-]/', '', $uid)."/".preg_replace('/[^A-Za-z0-9\-]/', '', $coldate);
    // echo $path_str_to;
    $output = "";
    exec('net use "\\\192.168.100.46\Clinic\img\\'.$path_str_to.'" /user:"'.$user.'" "'.$password.'" /persistent:no 2>&1', $output); 
    //Persistent:Yes หมายถึง ให้จำค่าเชื่อมต่อนี้ไว้ Login คราวหน้าก็ยังอยู่ไม่ต้องต่อใหม่ทุกครั้ง ถ้า เลือกเป็น No ก็จะเป็นครั้งๆไป หายทุกครั้งที่ Logoff
    $check_dir = is_dir('\\\192.168.100.46\Clinic\img\\'.$path_str_to);
    if($check_dir){
        $files = scandir('\\\192.168.100.46\Clinic\img\\'.$path_str_to);
        $str_row = "";
        $img = "";
        $i = 2; // 0,1 คือจำนวนชั้นที่ตั้ง path
        
        foreach($row_detail AS $key_name=>$val){
            $img = '//192.168.100.46/Clinic/img/'.$path_str_img."/".$val["name"].".".$val["type"];
            $str_row .= '   <div class="fl-wrap-row h-30 font-s-2 row-hover row-color">
                                <div class="fl-fix w-5"></div>
                                <div class="fl-fix w-30 fl-mid"><button class="btn" name="bt_view_img_doctor" data-name="'.$val["user"].'" data-id="'.$img.'" data-uid="'.$val["uid"].'" data-coldate="'.$val["collect_date"].'" data-coltime="'.$val["collect_time"].'" data-imgid="'.$val["name"].'" data-type="'.$val["type"].'" data-path="'.$path_str_img.'" data-typeimg="'.$type_img.'" style="padding: 0px 4px 0px 4px; background-color:#FEF9E7;"><i class="fa fa-eye fa-1x" aria-hidden="true"></i></button></div>
                                <div class="fl-fix w-185 fl-mid-left">'.$val["name"].'</div>
                                <div class="fl-fill fl-mid-left font-s-1">'.$val["comment"].'</div>
                                <div class="fl-fix w-160 fl-mid-left">'.$val["upd_date"].'</div>
                                <div class="fl-fix w-210 fl-mid-left">'.$val["user"].'</div>
                                <div class="fl-fix w-20 fl-mid"><i class="fa fa-trash fabtn" name="del_img_docinfo" data-id="'.$img.'" style="color: red;" aria-hidden="true"></i></div>
                                <div class="fl-fix w-5"></div>
                            </div>';
            $i++;
        }

        echo $str_row;
    }
    else{
        echo '<div class="fl-wrap-row h-40 font-s-2 row-hover row-color"><div class="fl-fill fl-mid fw-b">Not found data network path NAS.<br>'.$path_str_to.'</div></div>';
    }
?>

<script>
    $(document).ready(function(){
        // bt delete img
        $("#row_detail_img [name=del_img_docinfo]").off("click");
        $("#row_detail_img [name=del_img_docinfo]").on("click", function(){
            if(confirm("คุณต้องการลบหรือไม่?")){
                var imgId = $(this).attr("data-id");                
                var aData = {
                    img_id: imgId
                };

                $.ajax({
                    url: "doctor_img_delete_ajax.php",
                    type: "POST",
                    data: aData,
                    cache: false,
                    success: function(sResult){
                        if(sResult == "1"){
                            var sUid = $("#doctor_img_dlg [name=uid]").val();
                            var sColdate = $("#doctor_img_dlg [name=coldate]").val();
                            var sColtime = $("#doctor_img_dlg [name=coltime]").val();
                            var sTypeimg = $("#doctor_img_dlg [name=type_img]").val();
                            var sUrl = "doctor_img_list.php?uid="+sUid+"&coldate="+sColdate+"&coltime="+sColtime+"&type_img="+sTypeimg;
                            $("#doctor_img_dlg #row_detail_img").load(sUrl);
                        }
                        else{
                            console.log("del img uncomplete.");
                        }
                    },
                    async: false
                });
            }
        });

        // bt view img  
        $("#row_detail_img [name=bt_view_img_doctor]").off("click");
        $("#row_detail_img [name=bt_view_img_doctor]").on("click", function(){
            var sImg_id = $(this).attr("data-id");
            var imgName = $(this).attr("data-name");
            var imgUid = $(this).attr("data-uid");
            var imgColdate = $(this).attr("data-coldate");
            var imgColtime = $(this).attr("data-coltime");
            var sImgIdName = $(this).attr("data-imgid");
            var sImgType = $(this).attr("data-type");
            var sImgPath = $(this).attr("data-path");
            var sImgTypeImg = $(this).attr("data-typeimg");
            sUrl="doctor_img_list_img_view.php?img_id="+sImg_id+"&uid="+imgUid+"&coldate="+imgColdate+"&coltime="+imgColtime+"&img_nameid="+sImgIdName+"&path="+sImgPath+"&type_img="+sImgTypeImg;
            // console.log(sUrl);

            showDialog(sUrl, "Image View: "+imgName, "90%", "90%","",
            function(sResult){
                //CLose function
            },false,function(){
                //Load Done Function
            });
        });
    });
</script>