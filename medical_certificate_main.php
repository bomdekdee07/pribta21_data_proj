<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $sid = getSS("s_id");
    $doctype = getQS("doctype");
?>

<div class="fl-wrap-col" id="medical_create_pdf" data-uid="<? echo $uid; ?>" data-coldate="<? echo $coldate; ?>" data-coltime="<? echo $coltime; ?>" data-sid="<? echo $sid; ?>" data-doctype="<? echo $doctype; ?>">
    <div class="fl-wrap-row h-10"></div>
    <div class="fl-wrap-row h-20 font-s-2 fw-b">
        <div class="fl-fix w-60"></div>
        <div class="fl-fill fl-mid-left">
            ประเภทใบรับรองแพทย์
        </div>
    </div>
    <div class="fl-wrap-row h-30 font-s-2 fw-b border-bt holiday-ml-4">
        <!-- <div class="fl-fix w-60"></div> -->
        <div class="fl-fill fl-mid-left fw-b">
            <select id="type_meducain" style="width: 250px; background-color: #B0EAF5;" disabled>
                <option value="00">Please Select</option>
                <option value="MEDICAL_C">ใบรับรองแพทย์ทั่วไป</option>
                <option value="MEDICAL_HEALTH">ใบตรวจสุขภาพทั่วไป</option>
                <option value="MEDICAL_REFERRAL">ใบส่งตัว Referral letter</option>
                <option value="MEDICAL_COVID">ใบรับรองแพทย์ ตรวจโควิด</option>
                <option value="MEDICAL_FITTOFLY">ใบรับรองแพทย์ fit to fly</option>
                <option value="MEDICAL_DRIVING">ใบรับรองแพทย์ขออนุญาติขับรถ</option>
                <option value="MEDICAL_PRESCRIPTION">ใบรับรองแพทย์สั่งยา</option>
            </select>
        </div>
    </div>
    <div class="fl-wrap-row h-15"></div>

    <div class="fl-wrap-col fl-auto" id="bind_cteate_main"></div>
</div>

<script>
    $(document).ready(function(){
        var type_doc = $("#medical_create_pdf").data("doctype");
        $("#medical_create_pdf #type_meducain").val(type_doc);

        $("#medical_create_pdf #type_meducain").off("change");
        $("#medical_create_pdf #type_meducain").on("change", function(){
            var uid_s = $("#medical_create_pdf").data("uid");
            var coldate_s = $("#medical_create_pdf").data("coldate");
            var coltime_s = $("#medical_create_pdf").data("coltime");
            var doc_code_s = $("#medical_create_pdf").data("doctype");

            type_array = {
                00: "",
                MEDICAL_C: "certificate_general_main.php",
                MEDICAL_REFERRAL: "certificate_referral_main.php",
                MEDICAL_COVID: "certificate_covid_main.php",
                MEDICAL_FITTOFLY: "certificate_fittifly_main.php",
                MEDICAL_DRIVING: "certificate_driving_license_main.php",
                MEDICAL_HEALTH: "certificate_health_checkup_main.php",
                MEDICAL_PRESCRIPTION: "certificate_prescription_main.php"
            };
            
            aData = {
                uid: uid_s,
                coldate: coldate_s,
                coltime: coltime_s,
                doctype: doc_code_s
            };
            var link_type = $(this).val();
            if(link_type != "00"){
                $.ajax({
                    url: type_array[link_type],
                    case: false,
                    data: aData,
                    success: function(results){
                        $("#medical_create_pdf #bind_cteate_main").children().remove();
                        $("#medical_create_pdf #bind_cteate_main").append(results);
                    }
                });
            }
            else{
                $("#medical_create_pdf #bind_cteate_main").children().remove();
            }
        });

        $("#medical_create_pdf #type_meducain").change();
    });
</script>