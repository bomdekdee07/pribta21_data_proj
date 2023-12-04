<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $gClinicid = getSS("clinic_id");
    $gSid = getSS("s_id");

?>

<div id='consent_mng_main' class='fl-wrap-col'>
    <div class="fl-wrap-row h-10"></div>
    <div class="fl-wrap-row h-30 font-s-3 fw-b">
        <div class="fl-fix w-20"></div>
        <div class="fl-fill fl-mid-left">
            Consent Management
        </div>
    </div>

    <div class="fl-wrap-row h-90">
        <div class="fl-wrap-col w-20 h-75"></div>
        <div class="fl-wrap-col h-75 border-line-1" style="background-color: #7DD185;">
            <div class="fl-wrap-row h-15"></div>
            <div class="fl-wrap-row h-20 font-s-2">
                <div class="fl-fix w-10"></div>
                <div class="fl-fix w-225 fl-mid-left">
                    UID:
                </div>
                <div class="fl-fix w-60 fw-b">
                    OR
                </div>
                <div class="fl-fix w-225 fl-mid-left">
                    วันที่เข้ารับบริการ:
                </div>
            </div>

            <div class="fl-wrap-row h-35 font-s-2">
                <div class="fl-fix w-10"></div>
                <div class="fl-fix w-225 fl-mid-left">
                    <input type="text" name="uid_find" style="min-width: 199px; min-height: 26px; max-height: 26px;"/>
                </div>
                <div class="fl-fix w-60"></div>
                <div class="fl-fix w-225 fl-mid-left">
                    <input type="text" name="collectdate_find" style="min-width: 199px; min-height: 26px; max-height: 26px;"/>
                </div>
                <div class="fl-fix w-50 fl-mid-left ml-2">
                    <button class="btn btn-primary font-s-2 fw-b" id="btFindConsent" style="padding: 2px 10px;"><i class="fa fa-search" aria-hidden="true"> ค้นหา</i></button>
                    <i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
                </div>
                <div class="fl-fill"></div>
                <div class="fl-wrap-col w-35 h-35 border-line-2">
                    <div class="fl-fill fl-mid">
                        <i class="fa fa-file-excel fabtn fa-2x export-toExcel" style="background-color:green" aria-hidden="true" title="Export to Excel"></i>
                    </div>
                </div>
                <div class="fl-fix w-5"></div>
            </div>
        </div>
        <div class="fl-wrap-col w-20"></div>
    </div>

    <div class="fl-wrap-row font-s-2 fw-b h-30 header-hide holiday-mr-3" style="display: none; background-color: #53BBDF;">
        <div class="fl-fix w-20" style="background-color: white;"></div>
        <div class="fl-fix w-200 fl-mid border-line-1" style="border-color: #1A5276;">
            <span class="holiday-ml-4">UID</span>
        </div>
        <div class="fl-fill fl-mid border-line-1" style="border-color: #1A5276;">
            ชื่อ-นามสกุล
        </div>
        <div class="fl-fix fl-mid border-line-1 w-150" style="border-color: #1A5276;">
            เบอร์โทร
        </div>
        <div class="fl-fix fl-mid border-line-1 w-250" style="border-color: #1A5276;">
            Email
        </div>
        <div class="fl-fix fl-mid border-line-1 w-200" style="border-color: #1A5276;">
            วันที่เข้ารับบริการ
        </div>
        <div class="fl-fix fl-mid border-line-1 w-150" style="border-color: #1A5276;">
            สถานะข้อตกลง
        </div>
    </div>

    <div class="fl-wrap-col fl-auto" id="rp_consent_detail"></div>
</div>


<script>
    $(document).ready(function(){
        $("#consent_mng_main #btFindConsent").off("click");
        $("#consent_mng_main #btFindConsent").on("click", function(){
            var suid = $("[name=uid_find]").val();
            var scoldate = $("[name=collectdate_find]").val();
            var aData = {
                uid: suid,
                coldate: scoldate
            };

            $("#btFindConsent").next(".spinner").show();
            $("#btFindConsent").hide();

            $.ajax({
                url: "consent_mng_detail.php",
                method: "POST",
                cache: false,
                data: aData,
                success: function(sResult){
                    $("#consent_mng_main .header-hide").show();
                    $("#consent_mng_main #rp_consent_detail").children().remove();
                    $("#consent_mng_main #rp_consent_detail").append(sResult);

                    $("#btFindConsent").next(".spinner").hide();
                    $("#btFindConsent").show();
                }
            });
        });
    });
</script>