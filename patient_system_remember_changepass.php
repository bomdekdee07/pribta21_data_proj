<?
    include("in_head_script.php");
	include_once("in_php_function.php");
    include("in_db_conn.php");

    $uid = getQS("uid");
    $check_val_pass = getQS("val");
?>

<div class="fl-wrap-col fl-auto" id="patient_system_remember_newpass">
    <div id="data_defult" data-valuep="<? echo $check_val_pass; ?>" data-uid="<?echo $uid; ?>"></div>
    <form role="form" method="post" action="" target="iframe_target">
    <div class="fl-wrap-row h-115 fl-mid-left">
        <div class="fl-fill holiday-ml-1">
            <img src="assets/image/logo_IHRI.png" alt="Italian Trulli" width="200">
        </div>
    </div>
    <div class="fl-wrap-row h-30"></div>
    <div class="fl-wrap-row h-35">
        <div class="fl-fill fl-mid smallfont05">
            <span>เปลี่ยนรหัสผ่าน</span>
        </div>
    </div>
    <div class="fl-wrap-row h-15">
        <div class="fl-fill fw-b smallfont5 fl-mid">
            <span><hr size="3" width="400" color="#A93226"></span>
        </div>
    </div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fill fl-mid smallfont03">
            <span style="color: #808B96;">กรุณาใส่รหัสผ่านใหม่</span>
        </div>
    </div>

    <div class="fl-wrap-row h-15 fl-mid"></div>
    <div class="fl-wrap-row h-15 fl-mid condition-type-telno">
        <div class="fl-fill smallfont03 fw-b" style="min-width: 385px; max-width: 385px;">
            <span style="color: #34495E;">รหัสผ่านใหม่</span>
        </div>
    </div>
    <div class="fl-wrap-row h-45 fl-mid">
        <div class="fl-fill smallfont3 fl-mid-left" style="min-width: 375px; max-width: 375px;">
            <input type="password" name="new_pass" id="new_pass" style="width: 375px;">
        </div>
        <div class="fl-fill smallfont3 w-10 fl-mid">
            <span class="fw-b" style="color: red;">*</span>
        </div>
    </div>
    <div class="fl-wrap-row h-20 fl-mid">
        <div class="fl-fill smallfont2" style="min-width: 385px; max-width: 385px;">
            <label><input class="btn" type="checkbox" onclick="showpassword()"> Show Password</label>
        </div>
    </div>
    <div class="fl-wrap-row h-15 fl-mid"></div>
    <div class="fl-wrap-row h-15 fl-mid condition-type-telno">
        <div class="fl-fill smallfont03 fw-b" style="min-width: 385px; max-width: 385px;">
            <span style="color: #34495E;">ยืนยันรหัสผ่านใหม่</span>
        </div>
    </div>
    <div class="fl-wrap-row h-45 fl-mid">
        <div class="fl-fill smallfont3 fl-mid-left" style="min-width: 375px; max-width: 375px;">
            <input type="text" name="new_pass_cf" id="new_pass_cf" style="width: 375px;">
        </div>
        <div class="fl-fill smallfont3 w-10 fl-mid">
            <span class="fw-b" style="color: red;">*</span>
        </div>
    </div>
    <div class="fl-wrap-row h-25 fl-mid smallfont2 validation-cfpassmach">
        <div class="fl-fill h-25 smallfont2" style="min-width: 375px; max-width: 375px;">
            <p style="color: red;">รหัสผ่านไม่ตรงกัน</p>
        </div>
    </div>

    <div class="fl-wrap-row h-15"></div>
    <div class="fl-wrap-row h-30 fl-mid">
        <div class="fl-fill smallfont03" style="min-width: 385px; max-width: 385px;">
            <button class="button-bom button-forget-cf" style="width: 385px;">ยืนยัน</button>
            <i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
        </div>
        <div class="fl-fill smallfont3 w-10 fl-mid-left"></div>
    </div>
    <iframe id="update_newpass" name="iframe_target" src="#"  style="width:0;height:0;border:0px solid #fff;"></iframe>
    </form>
</div>

<script>
    $(document).ready(function(){
        $("#patient_system_remember_newpass .validation-cfpassmach").hide();

        $("#patient_system_remember_newpass .button-forget-cf").off("click")
        $("#patient_system_remember_newpass .button-forget-cf").on("click", function(){
            if (confirm('ยืนยันที่จะส่งข้อมูลนี้หรือไม่?')) {
                $("#patient_system_remember_newpass .button-forget-cf").next(".spinner").show();
                $("#patient_system_remember_newpass .button-forget-cf").hide();

                var uid_s = $("#patient_system_remember_newpass #data_defult").data("uid");
                var valpass_s = $("#patient_system_remember_newpass #data_defult").data("valuep");
                var newpass_s = $("#patient_system_remember_newpass [name=new_pass]").val();
                var check_val_match_cfpass = $("#patient_system_remember_newpass [name=new_pass_cf]").val();

                if(newpass_s != "" && check_val_match_cfpass != ""){
                    if(check_val_match_cfpass == newpass_s){
                        var url_ud = "patient_system_remember_changepass_ud.php?uid="+uid_s+"&val="+valpass_s+"&newpass="+newpass_s;
                        $("#update_newpass").load(url_ud, function(data){
                            if(data != ""){
                                window.location.href = "patient_system_remember_changepass_complete.php";
                            }
                            else{
                                alert("ข้อมูลผิดพลาด โปรดลองกดลิ้งใหม่อีกครั้ง");
                                $("#patient_system_remember_newpass .button-forget-cf").next(".spinner").hide();
                                $("#patient_system_remember_newpass .button-forget-cf").show();
                            }
                        });
                    }
                    else{
                        $("#patient_system_remember_newpass .validation-cfpassmach").show();
                        $(this).removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
                    }
                }
                else{
                    alert("กรุณากรอกข้อมูลให้ครบ");
                    $("#patient_system_remember_newpass .button-forget-cf").next(".spinner").hide();
                    $("#patient_system_remember_newpass .button-forget-cf").show();
                }
            }
        });

        $("#patient_system_remember_newpass [name=new_pass_cf]").off("focusout");
        $("#patient_system_remember_newpass [name=new_pass_cf]").on("focusout", function(){
            var check_val_match_pass = $("#patient_system_remember_newpass [name=new_pass]").val();
            var check_val_match_cfpass = $("#patient_system_remember_newpass [name=new_pass_cf]").val();
            if(check_val_match_pass != check_val_match_cfpass){
                $("#patient_system_remember_newpass .validation-cfpassmach").show();
                $(this).removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
            }
            else{
                $("#patient_system_remember_newpass .validation-cfpassmach").hide();
                $(this).removeClass("alert-warning-bom").addClass("alert-unwarning-bom");
            }
        })
    });

    function showpassword() {
        var x = $("#patient_system_remember_newpass [name=new_pass]");
        if (x[0]["type"] === "password") {
            x[0]["type"] = "text";
        } else {
            x[0]["type"] = "password";
        }
    }
</script>