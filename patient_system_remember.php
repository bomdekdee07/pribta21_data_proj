<title>Forgot Password</title>

<?
    include("in_head_script.php");
	include_once("in_php_function.php");
    include("in_db_conn.php");

    $citizen_id = getQS("citizenid");
    $telno = getQS("telno");
    $email = getQS("email");
    $condition = getQS("conditionmatch");

    $sJS_forgot_email = "";
    if($email != ""){
        $sJS_forgot_email .= '$("#patient_system_remember [name=email]").val("'.$email.'");';
    }
?>

<div class="fl-wrap-col fl-auto" id="patient_system_remember">
    <div id="data_defult" data-telno="<? echo $telno; ?>" data-citizen="<? echo $citizen_id; ?>" data-condition="<? echo $condition; ?>"></div>
    <div class="fl-wrap-row h-115 fl-mid-left">
        <div class="fl-fill holiday-ml-1">
            <img src="assets/image/logo_IHRI.png" alt="Italian Trulli" width="200">
        </div>
    </div>
    <div class="fl-wrap-row h-30"></div>
    <div class="fl-wrap-row h-35">
        <div class="fl-fill fl-mid smallfont05">
            <span>เปลี่ยนแปลงรหัสผ่าน</span>
        </div>
    </div>
    <div class="fl-wrap-row h-15">
        <div class="fl-fill fw-b smallfont5 fl-mid">
            <span><hr size="3" width="300" color="#A93226"></span>
        </div>
    </div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fill fl-mid smallfont03">
            <span style="color: #808B96;">โปรดระบุข้อมูลของคุณ</span>
        </div>
    </div>

    <div class="fl-wrap-row h-15 fl-mid"></div>
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
    <div class="fl-wrap-row h-15 fl-mid smallfont2 validation-email">
        <div class="fl-fill h-15 smallfont2" style="min-width: 375px; max-width: 375px;">
            <p style="color: red;">กรุณากรอกอีเมล์</p>
        </div>
    </div>
    <div class="fl-wrap-row h-20 fl-mid"></div>
    <div class="fl-wrap-row h-15 fl-mid">
        <div class="fl-fill smallfont03 fw-b" style="min-width: 370px; max-width: 370px;">
            <span style="color: #34495E;">กรุณาเลือก</span>
        </div>
    </div>
    <div class="fl-wrap-row h-5 fl-mid"></div>
    <div class="fl-wrap-row h-25 fl-mid">
        <div class="fl-fill smallfont03 h-25 fl-mid-left" style="min-width: 375px; max-width: 375px;">
            <select id="select_re_pass" style="width: 370px; height: 23px;">
                <option value="telno">เบอร์โทร</option>
                <option value="citizenid">เลขบัตรประชาชน หรือ หนังสือเดินทาง</option>
            </select>
        </div>
        <div class="fl-fill smallfont03 w-5 fl-mid h-25">
            <span class="fw-b" style="color: red;">*</span>
        </div>
    </div>
    <div class="fl-wrap-row h-5 fl-mid"></div>
    <div class="fl-wrap-row h-45 fl-mid">
        <div class="fl-fill smallfont3 fl-mid-left" style="min-width: 375px; max-width: 375px;">
            <input type="text" name="pone_or_citizen" id="pone_or_citizen" style="width: 370px;">
        </div>
        <div class="fl-fill smallfont3 w-5 fl-mid-left">
            <span class="fw-b" style="color: red;">*</span>
        </div>
    </div>
    <div class="fl-wrap-row h-15 fl-mid smallfont2 validation-citicen">
        <div class="fl-fill h-15 smallfont2" style="min-width: 375px; max-width: 375px;">
            <p style="color: red;">กรุณากรอกเลขบัตรประชาชน หรือ หนังสือเดินทาง</p>
        </div>
    </div>  
    <div class="fl-wrap-row h-15 fl-mid smallfont2 validation-telno">
        <div class="fl-fill h-15 smallfont2" style="min-width: 375px; max-width: 375px;">
            <p style="color: red;">กรุณากรอกเบอร์โทร</p>
        </div>
    </div>
    <div class="fl-wrap-row h-15"></div>
    <div class="fl-wrap-row h-30 fl-mid">
        <div class="fl-fill smallfont03" style="min-width: 375px; max-width: 375px;">
            <button class="button-bom button-forget-next" style="width: 370px;">ต่อไป</button>
        </div>
        <div class="fl-fill smallfont3 w-5 fl-mid-left"></div>
    </div>

    <div class="fl-wrap-row h-30"></div>
    <div class="fl-wrap-row h-30 fl-mid">
        <div class="fl-fill smallfont3" style="min-width: 370px; max-width: 370px;">
            <a href="patient_system_createuser.php" class="fw-b"> สร้างบัญชีใหม่</a>
        </div>
        <div class="fl-fill smallfont3 w-5 fl-mid-left"></div>
    </div>
</div>

<script>
    $(document).ready(function(){
        <? echo $sJS_forgot_email; ?>

        var check_telno = $("#patient_system_remember #data_defult").data("telno");
        var check_citizen = $("#patient_system_remember #data_defult").data("citizen");
        var check_condition = $("#patient_system_remember #data_defult").data("condition");
        if(check_telno == "" && check_citizen != ""){
            $("#patient_system_remember #select_re_pass").val("citizenid");
            $("#patient_system_remember [name=pone_or_citizen]").val(check_citizen);
        }
        else if(check_telno != "" && check_condition == "1"){
            $("#patient_system_remember #select_re_pass").val("citizenid");
            $("#patient_system_remember [name=pone_or_citizen]").val(check_citizen);
        }
        else{
            $("#patient_system_remember [name=pone_or_citizen]").val(check_telno);
        }

        $("#patient_system_remember #select_re_pass").off("change");
        $("#patient_system_remember #select_re_pass").on("change", function(){
            if($(this).val() == "citizenid"){
                $("#patient_system_remember [name=pone_or_citizen]").val(check_citizen);
            }
            else{
                $("#patient_system_remember [name=pone_or_citizen]").val(check_telno);
            }
        });

        $("#patient_system_remember .validation-email").hide();
        $("#patient_system_remember .validation-citicen").hide();
        $("#patient_system_remember .validation-telno").hide();

        $("#patient_system_remember .button-forget-next").off("click")
        $("#patient_system_remember .button-forget-next").on("click", function(){
            var check_email = $("#patient_system_remember [name=email]").val();
            var check_select = $("#patient_system_remember #select_re_pass").val();
            var check_data_next = $("#patient_system_remember [name=pone_or_citizen]").val();

            if(check_email == ""){
                $("#patient_system_remember .validation-email").show();
                $("#patient_system_remember [name=email]").removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
            }

            if(check_select == "telno"){
                if(check_data_next == ""){
                    $("#patient_system_remember .validation-telno").show();
                    $("#patient_system_remember .validation-citicen").hide();
                    $("#patient_system_remember [name=pone_or_citizen]").removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
                }
            }
            else{
                if(check_data_next == ""){
                    $("#patient_system_remember .validation-citicen").show();
                    $("#patient_system_remember .validation-telno").hide();
                    $("#patient_system_remember [name=pone_or_citizen]").removeClass("alert-unwarning-bom").addClass("alert-warning-bom");
                }
            }

            if(check_email != "" && check_data_next != ""){
                var aData = {
                    conditionmatch: check_select,
                    phoneorcitizenid: check_data_next
                };
                
                $.ajax({url: "patient_system_remember_ajax.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        var data_split = result.split(",");
                        if(data_split[0] != ""){
                            window.location.href = "patient_system_remember_cf.php?conditiontype="+check_select+"&valuecf="+data_split[0]+"&email="+check_email+"&uid="+data_split[1];
                        }
                        else{
                            alert("ไม่มีเบอร์โทร หรือ เลขบัตรประชาชนนี้อยู่ในระบบ");
                        }
                    }
                });
            }
        });

        $("#patient_system_remember input").off("focusin");
        $("#patient_system_remember input").on("focusin", function(){
            $(this).removeClass("alert-warning-bom").addClass("alert-unwarning-bom");

            if($(this)[0]["id"] == "email"){
                $("#patient_system_remember .validation-email").hide();
            }

            if($(this)[0]["id"] == "pone_or_citizen"){
                $("#patient_system_remember .validation-citicen").hide();
                $("#patient_system_remember .validation-telno").hide();
            }
        });
    });
</script>