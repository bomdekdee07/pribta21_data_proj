<!DOCTYPE html>
<html lang="th">
<head>
	<title>Patient system</title>
<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");
?>

<div class="fl-wrap-col" id="patient_system_main">
    <div class="fl-wrap-row h-115 fl-mid-left">
        <div class="fl-fill holiday-ml-1">
            <!-- <img src="assets/image/logo_IHRI.png" alt="Italian Trulli" width="200"> -->
        </div>
    </div>
    <div class="fl-wrap-row h-70"></div>
    <div class="fl-wrap-row h-45">
        <div class="fl-fill fl-mid smallfont5 fw-b">
            <span>Lottery Login</span>
        </div>
    </div>
    <div class="fl-wrap-row h-20">
        <div class="fl-fill fw-b smallfont5 fl-mid">
            <span><hr size="1" width="300" color="#A93226"></span>
        </div>
    </div>
    <div class="fl-wrap-row h-45">
        <div class="fl-fill fl-mid smallfont3">
            <span style="color: #808B96;">โปรดเข้าสู่ระบบด้วยบัญชี</span>
        </div>
    </div>
    <div class="fl-wrap-row h-15"></div>
    <div class="fl-wrap-row h-45">
        <div class="fl-fill fl-mid smallfont3">
            <input type="text" name="username" id="username" style="width: 350px;">
        </div>
    </div>
    <div class="fl-wrap-row h-45">
        <div class="fl-fill fl-mid smallfont3">
            <input type="password" name="password" id="password" style="width: 350px;">
        </div>
    </div>
    <div class="fl-wrap-row h-20 fl-mid">
        <div class="fl-fill smallfont2" style="min-width: 350px; max-width: 350px;">
            <label><input class="btn" type="checkbox" onclick="showpassword()"> Show Password</label>
        </div>
    </div>
    <div class="fl-wrap-row h-5"></div>
    <div class="fl-wrap-row h-15">
        <div class="fl-fill fl-mid smallfont2">
            <!-- <span style="color: #808B96;">ลืมรหัสผ่าน? <a href="patient_system_remember.php" class="fw-b"> โปรดคลิก</a></span> -->
        </div>
    </div>

    <div class="fl-wrap-row h-10"></div>
    <div class="fl-wrap-row h-30">
        <div class="fl-fill fl-mid smallfont2">
            <button class="button-bom button-login" style="width: 350px;">เข้าสู่ระบบ</button>
        </div>
    </div>

    <div class="fl-wrap-row h-30"></div>
    <div class="fl-wrap-row h-30 fl-mid">
        <div class="fl-fill smallfont3" style="min-width: 350px; max-width: 350px;">
            <!-- <span>ไม่มีบัญชี? <a href="patient_system_createuser.php" class="fw-b"> สร้างบัญชีใหม่</a></span> -->
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#patient_system_main #username").val("หมายเลขโทรศัพท์ หรือ อีเมล์ หรือ UID");
        $("#patient_system_main #username").prop("style", "color:#D5D8DC; width: 350px;");
        $("#patient_system_main #password").val("รหัสผ่าน");
        $("#patient_system_main #password").prop("style", "color:#D5D8DC; width: 350px;");

        $("#patient_system_main #username").off("focus");
        $("#patient_system_main #username").on("focus", function(){
            if($(this).val() == "หมายเลขโทรศัพท์ หรือ อีเมล์ หรือ UID"){
                $(this).val("");
                $(this).prop("style", "color:#1C2833; width: 350px;");
            }
        });
        $("#patient_system_main #username").off("focusout");
        $("#patient_system_main #username").on("focusout", function(){
            if($(this).val() == ""){
                $(this).val("หมายเลขโทรศัพท์ หรือ อีเมล์ หรือ UID");
                $(this).prop("style", "color:#D5D8DC; width: 350px;");
            }
        });

        $("#patient_system_main #password").off("focus");
        $("#patient_system_main #password").on("focus", function(){
            if($(this).val() == "รหัสผ่าน"){
                $(this).val("");
                $(this).prop("style", "color:#1C2833; width: 350px;");
            }
        });
        $("#patient_system_main #password").off("focusout");
        $("#patient_system_main #password").on("focusout", function(){
            if($(this).val() == ""){
                $(this).val("รหัสผ่าน");
                $(this).prop("style", "color:#D5D8DC; width: 350px;");
            }
        });

        $("#patient_system_main #password").off("keyup");
        $("#patient_system_main").on("keyup", "#password", function(e){
            if(e.keyCode == 13)
            $("#patient_system_main .button-login").click();
        });

        $("#patient_system_main .button-login").off("click");
        $("#patient_system_main .button-login").on("click", function(){
            var objThis = $(this);

            var aData = {
                username: $("#patient_system_main #username").val(),
                password: $("#patient_system_main #password").val(),
                mode: "login"
            };

            $.ajax({
                url: "login_a.php",
                method: "POST",
                cache: false,
                data: aData,
                success: function(){}
            })

            $.ajax({url: "patient_system_login_ajax.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    if(result == true){
                        var url_gen = "main.php";
                        $("#lotteryMain").load(url_gen, function(){
                            startLoad($("#patient_system_main"), $("#main_menu"));
                        });
                    }
                    else{
                        alert(result);
                    }
                }
            });
        });
    });

    function showpassword() {
        var x = $("#patient_system_main [name=password]");
        if (x[0]["type"] === "password") {
            x[0]["type"] = "text";
        } else {
            x[0]["type"] = "password";
        }
    }
</script>