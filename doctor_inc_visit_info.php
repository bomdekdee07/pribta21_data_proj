<div class='fl-fill ml-2'>
    <div class='fl-wrap-row smallfont1'> 
        <div class='fl-fix' style='min-width:85px'>
            <span class='language_en'><label>Medical examiner:</label></span><span class='language_th'><label>แพทย์ผู้ตรวจสอบ:</label></span>
        </div>    
        <div class='fl-fix' style='min-width:180px'>
            <select name='staff_md' data-id='staff_md' class='save-data v_text smallfont input-group'>
                <? $data_id = "staff_md"; $data_result;  include("doctor_opt_staff_md.php"); ?>
            </select>
        </div>

        <div class='fl-fix ml-2' style='min-width:70px'>
            <span class='language_en'><label>Doctor fee:</label></span><span class='language_th'><label>ค่าตรวจรักษา:</label></span>
        </div>
        <div class='fl-fix' style='min-width:85px'>
            <input type='text' name='cn_fee' data-id ='cn_fee' data-require='' data-odata='' class='save-data v_text smallfont input-group' value=''>
        </div>

        <div class='fl-fix ml-2' style='min-width:200px'>
            <span class='language_en'><label>Total Summary:</label></span><span class='language_th'><label>ค่าตรวจรักษารวม:</label></span>
        </div>

        <div class='fl-fix' style='min-width:85px'>
            <span class='language_en'><label>Follow Up Date:</label></span><span class='language_th'><label>ติดตามผล:</label></span>
        </div>
        
        <div class='fl-fix' style='min-width:180px'>
            <input type='text' name='nextvisit_date' data-id ='nextvisit_date' data-require='' data-odata='' class='save-data v_text smallfont input-group' value=''>
        </div>
    </div>
</div>