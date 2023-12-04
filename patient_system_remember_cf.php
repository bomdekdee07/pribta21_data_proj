<title>Forgot Password Confirm</title>

<?
    include("in_head_script.php");
	include_once("in_php_function.php");

    $condition_type = getQS("conditiontype");
    $value_cf = getQS("valuecf");
    $email_send = getQS("email");
    $uid_send = getQS("uid");

    $sJS_cf = "";

    if($value_cf != ""){
        $sJS_cf .= '$("#patient_system_remember_cf [name=phone_citizen]").val("'.$value_cf.'");';
    }
?>

<div class="fl-wrap-col fl-auto" id="patient_system_remember_cf">
    <div id="data_defult" data-condition="<? echo $condition_type; ?>" data-valuecf="<? echo $value_cf; ?>" data-email="<? echo $email_send; ?>" data-uid="<?echo $uid_send; ?>"></div>
    <div class="fl-wrap-row h-115 fl-mid-left">
        <div class="fl-fill holiday-ml-1">
            <img src="assets/image/logo_IHRI.png" alt="Italian Trulli" width="200">
        </div>
    </div>
    <div class="fl-wrap-row h-30"></div>
    <div class="fl-wrap-row h-35">
        <div class="fl-fill fl-mid smallfont05">
            <span>ยืนยันการเปลี่ยนแปลงรหัสผ่าน</span>
        </div>
    </div>
    <div class="fl-wrap-row h-15">
        <div class="fl-fill fw-b smallfont5 fl-mid">
            <span><hr size="3" width="400" color="#A93226"></span>
        </div>
    </div>
    <div class="fl-wrap-row h-25">
        <div class="fl-fill fl-mid smallfont03">
            <span style="color: #808B96;">โปรดระบุข้อมูลของคุณ</span>
        </div>
    </div>

    <div class="fl-wrap-row h-15 fl-mid"></div>
    <div class="fl-wrap-row h-15 fl-mid condition-type-telno">
        <div class="fl-fill smallfont03 fw-b" style="min-width: 385px; max-width: 385px;">
            <span style="color: #34495E;">โปรดใส่เลขท้ายบัตรประชาชน 4 หลัก</span>
        </div>
    </div>
    <div class="fl-wrap-row h-15 fl-mid condition-type-citizen">
        <div class="fl-fill smallfont03 fw-b" style="min-width: 385px; max-width: 385px;">
            <span style="color: #34495E;">โปรดใส่เลขท้ายเบอร์โทร 3 หลัก</span>
        </div>
    </div>
    <div class="fl-wrap-row h-45 fl-mid">
        <div class="fl-fill smallfont3 fl-mid-left" style="min-width: 290px; max-width: 290px;">
            <input type="text" name="phone_citizen" id="phone_citizen" disabled style="width: 290px;">
        </div>
        <div class="fl-fix w-10"></div>
        <div class="fl-fill smallfont3 fl-mid-left citizenid_text" style="min-width: 75px; max-width: 75px;">
            <input type="text" name="phone_citizen_cf" id="phone_citizen_cf" style="width: 75px;" maxlength="3">
        </div>
        <div class="fl-fill smallfont3 fl-mid-left telno_text" style="min-width: 75px; max-width: 75px;">
            <input type="text" name="phone_citizen_cf" id="phone_citizen_cf" style="width: 75px;" maxlength="4">
        </div>
        <div class="fl-fill smallfont3 w-10 fl-mid">
            <span class="fw-b" style="color: red;">*</span>
        </div>
    </div>  
    <div class="fl-wrap-row h-15 fl-mid smallfont2 validation-telno">
        <div class="fl-fill h-15 smallfont2" style="min-width: 385px; max-width: 385px;">
            <p style="color: red;">กรุณาใส่เลขให้ครบจำนวน 4 หลัก</p>
        </div>
    </div>
    <div class="fl-wrap-row h-15 fl-mid smallfont2 validation-citizen">
        <div class="fl-fill h-15 smallfont2" style="min-width: 385px; max-width: 385px;">
            <p style="color: red;">กรุณาใส่เลขให้ครบจำนวน 3 หลัก</p>
        </div>
    </div>
    <div class="fl-wrap-row h-15"></div>
    <div class="fl-wrap-row h-30 fl-mid">
        <div class="fl-fill smallfont03" style="min-width: 385px; max-width: 385px;">
            <button class="button-bom button-forget-next-cf" style="width: 385px;">ต่อไป</button>
            <i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
        </div>
        <div class="fl-fill smallfont3 w-10 fl-mid-left"></div>
    </div>

    <div class="fl-wrap-row h-40"></div>
    <div class="fl-wrap-row h-30 fl-mid">
        <div class="fl-fill smallfont3" style="min-width: 375px; max-width: 375px;">
            <a href="patient_system_createuser.php" class="fw-b"> สร้างบัญชีใหม่</a>
        </div>
        <div class="fl-fill smallfont3 w-10 fl-mid-left"></div>
    </div>
</div>

<script>
    $(document).ready(function(){
        <? echo $sJS_cf; ?>

        $("#patient_system_remember_cf [name=phone_citizen_cf]").off("keypress keyup blur");
        $("#patient_system_remember_cf [name=phone_citizen_cf]").on("keypress keyup blur",function (event) {
            $(this).val($(this).val().replace(/[^\d].+/, ""));
            if ((event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });

        $("#patient_system_remember_cf .validation-telno").hide();
        $("#patient_system_remember_cf .validation-citizen").hide();

        var condition_cf = $("#patient_system_remember_cf #data_defult").data("condition");
        var email_send = $("#patient_system_remember_cf #data_defult").data("email");
        if(condition_cf == "telno"){
            $("#patient_system_remember_cf .condition-type-telno").show();
            $("#patient_system_remember_cf .condition-type-citizen").hide();

            $("#patient_system_remember_cf .citizenid_text").hide();
            $("#patient_system_remember_cf .telno_text").show();
        }
        else{
            $("#patient_system_remember_cf .condition-type-telno").hide();
            $("#patient_system_remember_cf .condition-type-citizen").show();

            $("#patient_system_remember_cf .citizenid_text").show();
            $("#patient_system_remember_cf .telno_text").hide();
        }

        $("#patient_system_remember_cf [name=phone_citizen_cf]").off("focusout");
        $("#patient_system_remember_cf [name=phone_citizen_cf]").on("focusout", function(){
            var check_val = $(this).val();
            if(condition_cf == "telno"){
                if(check_val.length < 4){
                    $("#patient_system_remember_cf .validation-telno").show();
                    $("#patient_system_remember_cf .validation-citizen").hide();
                }
                else{
                    $("#patient_system_remember_cf .validation-telno").hide();
                    $("#patient_system_remember_cf .validation-citizen").hide();
                }
            }
            else if(condition_cf == "citizenid"){
                if(check_val.length < 3){
                    $("#patient_system_remember_cf .validation-telno").hide();
                    $("#patient_system_remember_cf .validation-citizen").show();
                }
                else{
                    $("#patient_system_remember_cf .validation-telno").hide();
                    $("#patient_system_remember_cf .validation-citizen").hide();
                }
            }
        })

        $("#patient_system_remember_cf .button-forget-next-cf").off("click")
        $("#patient_system_remember_cf .button-forget-next-cf").on("click", function(){
            $("#patient_system_remember_cf .button-forget-next-cf").next(".spinner").show();
            $("#patient_system_remember_cf .button-forget-next-cf").hide();
            
            var valuecf_s = $("#patient_system_remember_cf #data_defult").data("valuecf");
            var uid_s = $("#patient_system_remember_cf #data_defult").data("uid");

            var check_val_loop = "";
            $("#patient_system_remember_cf [name=phone_citizen_cf]").each(function() {
                if($(this).val() != "")
                check_val_loop = $(this).val();
            })
            
            if(check_val_loop != ""){
                var aData = {
                    conditionmatch: condition_cf,
                    valfirst: valuecf_s,
                    valend: check_val_loop,
                    uid: uid_s
                };

                $.ajax({url: "patient_system_remember_cf_ajax.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        var data_split = result.split(",");
                        if(data_split[0] == "1"){
                            var aData_MAIL = {
                                uid: uid_s,
                                pval: data_split[1],
                                email: email_send
                            };

                            $.ajax({url: "patient_system_remember_cf_sendemail.php", 
                                method: "POST",
                                cache: false,
                                data: aData_MAIL,
                                success: function(result){
                                    if(result == "ส่งเมลล์สำเร็จ")
                                    window.location.href = "patient_system_remember_email.php?email="+email_send+"&uid="+uid_s;
                                }
                            });
                        }
                        else{
                            alert("ข้อมูลไม่ถูกต้อง");
                            $("#patient_system_remember_cf .button-forget-next-cf").next(".spinner").hide();
                            $("#patient_system_remember_cf .button-forget-next-cf").show();
                        }
                    }
                });
            }
            else{
                var check_val = $("#patient_system_remember_cf [name=phone_citizen_cf]").val();
                if(condition_cf == "telno"){
                    if(check_val.length < 4){
                        $("#patient_system_remember_cf .validation-telno").show();
                        $("#patient_system_remember_cf .validation-citizen").hide();
                    }
                    else{
                        $("#patient_system_remember_cf .validation-telno").hide();
                        $("#patient_system_remember_cf .validation-citizen").hide();
                    }
                }
                else if(condition_cf == "citizenid"){
                    if(check_val.length < 3){
                        $("#patient_system_remember_cf .validation-telno").hide();
                        $("#patient_system_remember_cf .validation-citizen").show();
                    }
                    else{
                        $("#patient_system_remember_cf .validation-telno").hide();
                        $("#patient_system_remember_cf .validation-citizen").hide();
                    }
                }

                $("#patient_system_remember_cf .button-forget-next-cf").next(".spinner").hide();
                $("#patient_system_remember_cf .button-forget-next-cf").show();
            }
        });
    });
</script>