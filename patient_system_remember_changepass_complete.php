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
        <div class="fl-wrap-col wper-40 h-200 holiday-mt-4">
            <div class="fl-wrap-row fl-mid-left h-50">
                <div class="fl-fix wper-35"></div>
                <div class="fl-fill smallfont05 fw-b">
                    <span>เราเปลี่ยนรหัสผ่านใหม่เรียบร้อย</span>
                </div>
            </div>
            <div class="fl-wrap-row fl-mid-left h-40">
                <div class="fl-fix wper-35"></div>
                <div class="fl-fill smallfont4">
                    <span>กรุณา<a href="patient_system_login.php">เข้าสู่ระบบใหม่อีกครั้ง</a></span>
                </div>
            </div>
            <div class="fl-wrap-row fl-mid-left h-30">
                <div class="fl-fix wper-35"></div>
                <div class="fl-fill smallfont3">
                    <span>กำลังจะเปลี่ยนหน้าภายใน ...3</span>
                </div>
            </div>
            <div class="fl-wrap-row fl-mid-left h-30">
                <div class="fl-fix wper-35"></div>
                <div class="fl-fill smallfont3 fw-b">
                    <span><? echo $email; ?></span>
                </div>
            </div>
        </div>
        <div class="fl-wrap-col wper-45 h-350">
            <div class="fl-wrap-row h-350">
                <div class="fl-fill fl-mid-left">
                    <img src="assets/image/logo_login.png" alt="Italian Trulli" width="350" height="215">
                </div>
            </div>
        </div>
    </div>
</div>