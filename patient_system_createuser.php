<title>Register</title>

<?
	include("in_head_script.php");
	include_once("in_php_function.php");
    include("in_db_conn.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_GET['u_mode'] = "create_uid";
        $_POST['citizen_id'];
        include("patient_a.php");
    }
?>

<div class="fl-wrap-col fl-auto" id="patient_system_createuser">
    <div class="fl-wrap-row h-115 fl-mid-left">
        <div class="fl-fill holiday-ml-1">
            <img src="assets/image/logo_IHRI.png" alt="Italian Trulli" width="200">
        </div>
    </div>
    <div class="fl-wrap-row h-30"></div>
    <div class="fl-wrap-row h-35">
        <div class="fl-fill fl-mid smallfont05">
            <span>สร้างบัญชีใหม่</span>
        </div>
    </div>
    <div class="fl-wrap-row h-15">
        <div class="fl-fill fw-b smallfont5 fl-mid">
            <span><hr size="3" width="300" color="#A93226"></span>
        </div>
    </div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fill fl-mid smallfont03">
            <span style="color: #808B96;">มีบัญชีอยู่แล้ว? กรุณา<a href="patient_system_login.php">ลงชื่อเข้าใช้</a></span>
        </div>
    </div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fill fl-mid smallfont03">
            <span style="color: #808B96;">โปรดระบุข้อมูลเพื่อลงทะเบียน</span>
        </div>
    </div>
    <form role="form" method="post" action="" target="iframe_target">
    <!-- patient_a.php?u_mode=create_uid -->
    <iframe id="iframe_target" name="iframe_target" src="#"  style="width:0;height:0;border:0px solid #fff;"></iframe>
        <div class="fl-wrap-row h-15 fl-mid">
            <div class="fl-fill smallfont03 fw-b" style="min-width: 370px; max-width: 370px;">
                <span style="color: #34495E;">อีเมล์</span>
            </div>
        </div>
        <div class="fl-wrap-row h-45 fl-mid">
            <div class="fl-fill smallfont3 fl-mid-left" style="min-width: 375px; max-width: 375px;">
                <input type="text" name="email" id="email" style="width: 370px;">
            </div>
            <div class="fl-fill smallfont3 w-5 fl-mid-left">
                <span class="fw-b" style="color: red;">*</span>
            </div>
        </div>
        <div class="fl-wrap-row h-25 fl-mid smallfont2 validation-email">
            <div class="fl-fill h-25 smallfont2" style="min-width: 375px; max-width: 375px;">
                <p style="color: red;">กรุณากรอกอีเมล์</p>
            </div>
        </div>
        <div class="fl-wrap-row h-5"></div>
        <div class="fl-wrap-row h-15 fl-mid">
            <div class="fl-fill smallfont03 fw-b" style="min-width: 370px; max-width: 370px;">
                <span style="color: #34495E;">บัตรประชาชน หรือ หนังสือเดินทาง</span>
            </div>
        </div>
        <div class="fl-wrap-row h-45 fl-mid">
            <div class="fl-fill smallfont3 fl-mid-left" style="min-width: 375px; max-width: 375px;">
                <input type="text" name="citizen_id" id="citizen_id" style="width: 370px;">
            </div>
            <div class="fl-fill smallfont3 w-5 fl-mid-left"></div>
        </div>
        <div class="fl-wrap-row h-5"></div>
        <div class="fl-wrap-row h-15 fl-mid">
            <div class="fl-fill smallfont03 fw-b" style="min-width: 370px; max-width: 370px;">
                <span style="color: #34495E;">รหัสผ่าน</span>
            </div>
        </div>
        <div class="fl-wrap-row h-45 fl-mid">
            <div class="fl-fill fl-mid-left smallfont3"  style="min-width: 375px; max-width: 375px;">
                <input type="password" name="passwd" id="passwd" style="width: 370px;">
            </div>
            <div class="fl-fill smallfont3 w-5 fl-mid-left">
                <span class="fw-b" style="color: red;">*</span>
            </div>
        </div>
        <div class="fl-wrap-row h-20 fl-mid">
            <div class="fl-fill smallfont2" style="min-width: 375px; max-width: 375px;">
                <label><input class="btn" type="checkbox" onclick="showpassword()"> Show Password</label>
            </div>
        </div>
        <div class="fl-wrap-row h-25 fl-mid smallfont2 validation-pass">
            <div class="fl-fill h-25 smallfont2" style="min-width: 375px; max-width: 375px;">
                <p style="color: red;">กรุณากรอกรหัสผ่าน</p>
            </div>
        </div>
        <div class="fl-wrap-row h-5"></div>
        <div class="fl-wrap-row h-15 fl-mid">
            <div class="fl-fill smallfont03 fw-b" style="min-width: 370px; max-width: 370px;">
                <span style="color: #34495E;">ยืนยันรหัสผ่าน</span>
            </div>
        </div>
        <div class="fl-wrap-row h-45 fl-mid">
            <div class="fl-fill fl-mid-left smallfont3" style="min-width: 375px; max-width: 375px;">
                <input type="text" name="cf_password" id="cf_password" style="width: 370px;">
            </div>
            <div class="fl-fill smallfont3 w-5 fl-mid-left">
                <span class="fw-b" style="color: red;">*</span>
            </div>
        </div>
        <div class="fl-wrap-row h-25 fl-mid smallfont2 validation-cfpass">
            <div class="fl-fill h-25 smallfont2" style="min-width: 375px; max-width: 375px;">
                <p style="color: red;">กรุณายืนยันรหัสผ่าน</p>
            </div>
        </div>
        <div class="fl-wrap-row h-25 fl-mid smallfont2 validation-cfpassmach">
            <div class="fl-fill h-25 smallfont2" style="min-width: 375px; max-width: 375px;">
                <p style="color: red;">รหัสผ่านไม่ตรงกัน</p>
            </div>
        </div>
        <div class="fl-wrap-row h-5"></div>
        <div class="fl-wrap-row h-15 fl-mid">
            <div class="fl-fill smallfont03 fw-b" style="min-width: 370px; max-width: 370px;">
                <span style="color: #34495E;">เบอร์โทรศัพท์</span>
            </div>
        </div>
        <div class="fl-wrap-row h-45 fl-mid">
            <div class="fl-fill fl-mid-left smallfont3" style="min-width: 375px; max-width: 375px;">
                <input type="text" name="tel_no" id="tel_no" style="width: 370px;">
            </div>
            <div class="fl-fill smallfont3 w-5 fl-mid-left"></div>
        </div>
        <div class="fl-wrap-row h-5"></div>
        <div class="fl-wrap-row h-20 fl-mid">
            <div class="fl-fix smallfont3 w-5 "></div>
            <div class="fl-fix smallfont03 fw-b w-180 h-20" style="min-width: 170px; max-width: 170px;">
                <span style="color: #34495E;">ชื่อ</span>
            </div>
            <div class="fl-fix smallfont3 w-25"></div>
            <div class="fl-fix smallfont03 fw-b w-180 h-20" style="min-width: 180px; max-width: 180px;">
                <span style="color: #34495E;">นามสกุล</span>
            </div>
        </div>
        <div class="fl-wrap-row h-45 fl-mid">
            <div class="fl-fix smallfont3 w-185 h-45 fl-mid-left">
                <input type="text" name="fname" id="fname" style="width: 170px;">
            </div>
            <div class="fl-fill smallfont3 w-5 fl-mid-left">
                <span class="fw-b" style="color: red;">*</span>
            </div>
            <div class="fl-fix smallfont3 w-10 h-45"></div>
            <div class="fl-fix smallfont3 w-180 h-45 fl-mid-left">
                <input type="text" name="sname" id="sname" style="width: 170px;">
            </div>
            <div class="fl-fill smallfont3 w-5 fl-mid-left">
                <span class="fw-b" style="color: red;">*</span>
            </div>
        </div>
        <div class="fl-wrap-row h-25 fl-mid smallfont2 validation-name">
            <div class="fl-fill h-25 smallfont2" style="min-width: 375px; max-width: 375px;">
                <p style="color: red;">กรุณากรอกชื่อ หรือ นามสกุล</p>
            </div>
        </div>
        <div class="fl-wrap-row h-15 fl-mid">
            <div class="fl-fill smallfont03 fw-b" style="min-width: 370px; max-width: 370px;">
                <span style="color: #34495E;">วันเกิด</span>
            </div>
        </div>
        <div class="fl-wrap-row h-45 fl-mid">
            <div class="fl-fill fl-mid-left smallfont3" style="min-width: 375px; max-width: 375px;">
                <input type="text" name="date_of_birth" id="date_of_birth" style="width: 370px;">
            </div>
            <div class="fl-fill smallfont3 w-5 fl-mid-left">
                <span class="fw-b" style="color: red;">*</span>
            </div>
        </div>
        <div class="fl-wrap-row h-25 fl-mid smallfont2 validation-birthday">
            <div class="fl-fill h-25 smallfont2" style="min-width: 375px; max-width: 375px;">
                <p style="color: red;">กรุณากรอกวันเกิด</p>
            </div>
        </div>
        <div class="fl-wrap-row h-10"></div>
        <div class="fl-wrap-row h-30 fl-mid">
            <div class="fl-fill smallfont2" style="min-width: 375px; max-width: 375px;">
                <button class="button-bom button-register" style="width: 370px;">ลงทะเบียน</button>
                <i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
            </div>
            <div class="fl-fill smallfont3 w-5 fl-mid-left"></div>
        </div>
    </form>
</div>

<script>
    $(document).ready(function(){
        $("#patient_system_createuser [name=birthday]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});

        $("#patient_system_createuser .validation-email").hide();
        $("#patient_system_createuser .validation-pass").hide();
        $("#patient_system_createuser .validation-cfpass").hide();
        $("#patient_system_createuser .validation-name").hide();
        $("#patient_system_createuser .validation-birthday").hide();
        $("#patient_system_createuser .validation-cfpassmach").hide();

        $("#patient_system_createuser input").off("focusin");
        $("#patient_system_createuser input").on("focusin", function(){
            $(this).removeClass("alert-warning-bom").addClass("alert-unwarning-bom");

            if($(this)[0]["id"] == "email"){
                $("#patient_system_createuser .validation-email").hide();
            }
            if($(this)[0]["id"] == "passwd"){
                $("#patient_system_createuser .validation-pass").hide();
            }
            if($(this)[0]["id"] == "cf_password"){
                $("#patient_system_createuser .validation-cfpass").hide();
            }
            if($(this)[0]["id"] == "fname"){
                $("#patient_system_createuser .validation-name").hide();
            }
            if($(this)[0]["id"] == "sname"){
                $("#patient_system_createuser .validation-name").hide();
            }
            if($(this)[0]["id"] == "date_of_birth"){
                $("#patient_system_createuser .validation-birthday").hide();
            }
        });

        $("#patient_system_createuser .button-register").off("click");
        $("#patient_system_createuser .button-register").on("click", function(){
            if (confirm('ยืนยันที่จะส่งข้อมูลนี้หรือไม่?')) {
                $("#patient_system_createuser .button-register").next(".spinner").show();
                $("#patient_system_createuser .button-register").hide();

                var check_email = $("#patient_system_createuser [name=email]").val();
                var check_pass = $("#patient_system_createuser [name=passwd]").val();
                var check_cf_pass = $("#patient_system_createuser [name=cf_password]").val();
                var check_name = $("#patient_system_createuser [name=fname]").val();
                var check_lastname = $("#patient_system_createuser [name=sname]").val();
                var check_birth = $("#patient_system_createuser [name=date_of_birth]").val();
                var citizen_id_s = $("#patient_system_createuser [name=citizen_id]").val();
                var citizen_tel_s = $("#patient_system_createuser [name=tel_no]").val();

                if(check_email == ""){
                    $("#patient_system_createuser [name=email]").removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
                    $("#patient_system_createuser .validation-email").show();

                    $("#patient_system_createuser .button-register").next(".spinner").hide();
                    $("#patient_system_createuser .button-register").show();
                }
                if(check_pass == ""){
                    $("#patient_system_createuser [name=passwd]").removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
                    $("#patient_system_createuser .validation-pass").show();

                    $("#patient_system_createuser .button-register").next(".spinner").hide();
                    $("#patient_system_createuser .button-register").show();
                }
                if(check_cf_pass == ""){
                    $("#patient_system_createuser [name=cf_password]").removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
                    $("#patient_system_createuser .validation-cfpass").show();

                    $("#patient_system_createuser .button-register").next(".spinner").hide();
                    $("#patient_system_createuser .button-register").show();
                }
                if(check_name == ""){
                    $("#patient_system_createuser [name=fname]").removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
                    $("#patient_system_createuser .validation-name").show();

                    $("#patient_system_createuser .button-register").next(".spinner").hide();
                    $("#patient_system_createuser .button-register").show();
                }
                if(check_lastname == ""){
                    $("#patient_system_createuser [name=sname]").removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
                    $("#patient_system_createuser .validation-name").show();

                    $("#patient_system_createuser .button-register").next(".spinner").hide();
                    $("#patient_system_createuser .button-register").show();
                }
                if(check_birth == ""){
                    $("#patient_system_createuser [name=date_of_birth]").removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
                    $("#patient_system_createuser .validation-birthday").show();

                    $("#patient_system_createuser .button-register").next(".spinner").hide();
                    $("#patient_system_createuser .button-register").show();
                }
                
                if(check_email != "" && check_pass != "" && check_cf_pass != "" && check_name != ""  && check_lastname != "" && check_birth != ""){
                    var aData = {
                        citizen: $("#patient_system_createuser [name=citizen_id]").val(),
                        telno: $("#patient_system_createuser [name=tel_no]").val(),
                        name: $("#patient_system_createuser [name=fname]").val(),
                        lastname: $("#patient_system_createuser [name=sname]").val(),
                        birthday: $("#patient_system_createuser [name=date_of_birth]").val()
                    };
                    
                    $.ajax({url: "patient_system_createuser_ajax.php", 
                        method: "POST",
                        cache: false,
                        data: aData,
                        success: function(result){
                            var data_splite = result.split(",");
                            var citizen_check = data_splite[0];
                            var tel_check = data_splite[1];
                            var name_check = data_splite[2];
                            if(citizen_check != ""){
                                alert("มีรหัสบัตรประชา หรือ หนังสือเดินทางนี้ในระบบแล้ว");
                                window.location.href = "patient_system_remember.php?email="+check_email+"&citizenid="+citizen_id_s+"&telno="+citizen_tel_s+"&conditionmatch=1";

                                $("#patient_system_createuser .button-register").next(".spinner").hide();
                                $("#patient_system_createuser .button-register").show();
                            }
                            else if(name_check != ""){
                                alert("มีชื่อ และ วันเกิดนี้ในระบบแล้ว");
                                window.location.href = "patient_system_remember.php?email="+check_email+"&citizenid="+citizen_id_s+"&telno="+citizen_tel_s+"&conditionmatch=2";

                                $("#patient_system_createuser .button-register").next(".spinner").hide();
                                $("#patient_system_createuser .button-register").show();
                            }
                            else{
                                window.location.href = "patient_system_createuser_complete.php";
                                ("[name=iframe_target]").load('patient_a.php?u_mode=create_uid');
                            }
                        }
                    });
                }
                else{
                    $("#patient_system_createuser .button-register").next(".spinner").hide();
                    $("#patient_system_createuser .button-register").show();
                }
            }
        });

        $("#patient_system_createuser [name=cf_password]").off("focusout");
        $("#patient_system_createuser [name=cf_password]").on("focusout", function(){
            var check_val_match_pass = $("#patient_system_createuser [name=passwd]").val();
            var check_val_match_cfpass = $("#patient_system_createuser [name=cf_password]").val();
            if(check_val_match_pass != check_val_match_cfpass){
                $("#patient_system_createuser .validation-cfpassmach").show();
                $(this).removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
            }
            else{
                $("#patient_system_createuser .validation-cfpassmach").hide();
                $(this).removeClass("alert-warning-bom").addClass("alert-unwarning-bom");
            }
        })
    });

    function showpassword() {
        var x = $("#patient_system_createuser [name=passwd]");
        if (x[0]["type"] === "password") {
            x[0]["type"] = "text";
        } else {
            x[0]["type"] = "password";
        }
    }
</script>