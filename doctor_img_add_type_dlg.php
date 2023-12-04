<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $s_id = getSS("s_id");
    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");

    $jsBind = "";
    $jsBind .= '$("#img_add_type_dlg").attr("data-uid", '.json_encode($uid).');';
    $jsBind .= '$("#img_add_type_dlg").attr("data-coldate", '.json_encode($coldate).');';
    $jsBind .= '$("#img_add_type_dlg").attr("data-coltime", '.json_encode($coltime).');';
?>

<div class="fl-wrap-col" id="img_add_type_dlg" data-uid="" data-coldate="" data-coltime="">
    <div class="fl-wrap-row h-20"></div>
    <div class="fl-wrap-row h-30">
        <div class="fl-fix w-40"></div>
        <div class="fl-fill fl-mid-left font-s-5 fw-b">
            ชื่อประเภทของการเก็บไฟล์ภาพ
        </div>
    </div>
    <div class="fl-wrap-row h-20"></div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fix w-40"></div>
        <div class="fl-fill fl-mid-left font-s-2 fw-b" style="color: #646464;">
            Type ID
        </div>
    </div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fix w-40"></div>
        <div class="fl-fix w-200">
            <input type="text" name="img_type_name" class="input-group h-25 font-s-1" onkeypress="return /[0-9a-zA-Z]/i.test(event.key)">
        </div>
    </div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fix w-40"></div>
        <div class="fl-fill font-s-1 fl-mid-left" style="color: #646464;">ตัวอย่าง Type ID: anal02</div>
    </div>

    <div class="fl-wrap-row h-10"></div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fix w-40"></div>
        <div class="fl-fill fl-mid-left font-s-2 fw-b" style="color: #646464;">
            ชื่อประเภท
        </div>
    </div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fix w-40"></div>
        <div class="fl-fix w-200">
            <input type="text" name="img_type_name_show" class="input-group h-25 font-s-1">
        </div>
    </div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fix w-40"></div>
        <div class="fl-fill font-s-1 fl-mid-left" style="color: #646464;">เมื่อสร้างชื่อประเภทนี้แล้วระบบจะทำการสร้างโฟรเดอร์ทันทีการมีการเปลี่ยนแปลงชื่อในภายหลังจะกระทบทั้งระบบครับ</div>
    </div>

    <div class="fl-wrap-row h-20"></div>
    <div class="fl-wrap-row h-30 ">
        <div class="fl-fill"></div>
        <div class="fl-fix w-100 fl-mid">
            <button name="add_type_img_dlg" class="btn btn-success font-s-2 w-90" style="padding: 0px 0px 0px 0px;">เพิ่ม</button>
        </div>
        <div class="fl-fix w-100 fl-mid">
            <button name="close_type_img_dlg" class="btn btn-danger font-s-2 w-90" style="padding: 0px 0px 0px 0px;">ยกเลิก</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        <? echo $jsBind; ?>

        // add
        $("#img_add_type_dlg [name=add_type_img_dlg]").off("click");
        $("#img_add_type_dlg [name=add_type_img_dlg]").on("click", function(){
            if(confirm("คุณต้องการสร้างชื่อนี้ใช่หรือไม่?")){
                var data = [];
                var sTypeName = $("#img_add_type_dlg [name=img_type_name]").val();
                var sTypeNameShow = $("#img_add_type_dlg [name=img_type_name_show]").val();
                var typeName = {
                    type_name: sTypeName,
                    name_show: sTypeNameShow
                };
                data.push(typeName);
                var str_json = JSON.stringify(data);

                $.ajax({
                    url: "doctor_img_add_type_ajax.php",
                    method: "POST",
                    cache: false,
                    data: {data: str_json},
                    success: function(sResult){
                        if(sResult){
                            var objthis = $("#img_add_type_dlg [name=add_type_img_dlg]");
                            close_dlg(objthis);

                            var sUid = $("#img_add_type_dlg").attr("data-uid");
                            var sColdate = $("#img_add_type_dlg").attr("data-coldate");
                            var sColtime = $("#img_add_type_dlg").attr("data-coltime");
                            $("#doctor_img_dlg").load("doctor_img_dlg.php?uid="+sUid+"&coldate="+sColdate+"&coltime="+sColtime);
                        }
                        else{
                            alert("มีโฟเดอร์นี้ในระบบแล้ว");
                        }
                    }
                })
            }
        });

        // close
        $("#img_add_type_dlg [name=close_type_img_dlg]").off("click");
        $("#img_add_type_dlg [name=close_type_img_dlg]").on("click", function(){
            var objthis = $(this);
            close_dlg(objthis);
        });
    });

    function close_dlg(obj){
        var objthis = obj;
        closeDlg(objthis, "0");
    }
</script>