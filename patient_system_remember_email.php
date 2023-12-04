<title>Confirm Email</title>
<?
    include("in_head_script.php");
    include_once("in_php_function.php");

    $email = getQS("email");
    $uid = getQS("uid");
?>

<div class="fl-wrap-col fl-auto" id="patient_system_remember_cf">
    <div id="data_defult" data-email="<? echo $email; ?>" data-uid="<?echo $uid; ?>"></div>
    <div class="fl-wrap-row h-115 fl-mid-left">
        <div class="fl-fill holiday-ml-1">
            <img src="assets/image/logo_IHRI.png" alt="Italian Trulli" width="200">
        </div>
    </div>
    <div class="fl-wrap-row h-80"></div>
    <div class="fl-wrap-row h-200">
        <div class="fl-wrap-col wper-55 h-250 holiday-mt-4">
            <div class="fl-wrap-row fl-mid-left h-50">
                <div class="fl-fix wper-20"></div>
                <div class="fl-fill smallfont05 fw-b">
                    <span>เราได้ส่งลิ้งค์สำหรับเปลี่ยนรหัสผ่านใหม่ไปที่อีเมล์เรียบร้อย</span>
                </div>
            </div>
            <div class="fl-wrap-row fl-mid-left h-40">
                <div class="fl-fix wper-20"></div>
                <div class="fl-fill smallfont4">
                    <span>กรุณาเข้าอีเมล์ของคุณเพื่อตรวจสอบ</span>
                </div>
            </div>
            <div class="fl-wrap-row fl-mid-left h-30">
                <div class="fl-fix wper-20"></div>
                <div class="fl-fill smallfont3">
                    <span>ท่านสามารถตรวจอีเมล์ เพื่อยืนยันการสมัครได้ที่</span>
                </div>
            </div>
            <div class="fl-wrap-row fl-mid-left h-30">
                <div class="fl-fix wper-20"></div>
                <div class="fl-fill smallfont3 fw-b">
                    <span><? echo $email; ?></span>
                </div>
            </div>
        </div>
        <div class="fl-wrap-col wper-45 h-350">
            <div class="fl-wrap-row h-350">
                <div class="fl-fill fl-mid-left">
                    <img src="assets/image/email_img_con.png" alt="Italian Trulli" width="400" height="240">
                </div>
            </div>
        </div>
    </div>
</div>