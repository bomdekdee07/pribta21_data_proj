<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $gUid = getQS("uid");
    $gSid = getSS("s_id");
    // echo "TEST:".$gUid;

    // function for concat string single quote sensitive.
    function convert_singel_c($value_S){
        $values_con = "'".$value_S."'";

        return $values_con;
    }

    $bind_param = "s";
    $array_val = array($gUid);
    $data_patient_info = array();

    $query = "SELECT fname,
        sname,
        en_fname,
        en_sname,
        uic
    from patient_info 
    where uid = ?;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($fname, $sname, $enfname, $ensname, $uic);
        while($stmt->fetch()){
            $data_patient_info["name"] = (isset($fname)? $fname." ".$sname : $enfname." ".$ensname);
            $data_patient_info["uic"] = $uic;
            $data_patient_info["uid"] = $gUid;
        }
    }
    $stmt->close();

    $consent_data = array();
    $query = "SELECT data_sing_1, 
        data_sing_2, 
        data_sing_3, 
        data_sing_4, 
        data_sing_5, 
        data_sing_6, 
        data_sing_7, 
        data_sing_8
    from consent_data
    where uid = ?;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $stmt->bind_result($data_sing_1, $data_sing_2, $data_sing_3, $data_sing_4, $data_sing_5, $data_sing_6, $data_sing_7, $data_sing_8);
        while($stmt->fetch()){
            $consent_data["data_sing_1"] = $data_sing_1;
            $consent_data["data_sing_2"] = $data_sing_2;
            $consent_data["data_sing_3"] = $data_sing_3;
            $consent_data["data_sing_4"] = $data_sing_4;//$data_sing_4 != ""? $data_sing_4: "N";
        }
    }
    $stmt->close();
    $mysqli->close();

    $sJS = "";
    $check_count_consent = count($consent_data);
    if($check_count_consent > 0){
        foreach($consent_data as $key_consent => $val){
            if($val != ""){
                $sJS .= '$("[name='.$key_consent.'][value='.$val.']").prop("checked", true);'; // echo $sJS."<br>";
                $sJS .= '$("[name='.$key_consent.']").attr("data-odata", '.convert_singel_c($val).');'; 
            }
        }
    }

    $html_bind = "";
    $html_bind .= ' <div class="fl-wrap-col border" id="consent_main" style="padding: 10px 10px 10px 10px;" data-uid = "'.$gUid.'" data-sid="'.$gSid.'">
                        <div class="fl-wrap-col h-35">
                            <div class="fl-wrap-row h-35 font-s-2">
                                <div class="fl-fix fl-mid-left wper-5"></div>
                                <div class="fl-fix fl-mid-left wper-10 fw-b">
                                    หมายเลข UIC:
                                </div>
                                <div class="fl-fix fl-mid-left wper-10">
                                    '.$data_patient_info["uic"].'
                                </div>
                                <div class="fl-fix fl-mid-left wper-25 fw-b">
                                    หรือ หมายเลขประจำตัวบัตรคลินิก:
                                </div>
                                <div class="fl-fill fl-mid-left">
                                    '.$data_patient_info["uid"].'
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                        </div>

                        <div class="fl-wrap-row h-10 font-s-2"></div>

                        <div class="fl-wrap-col fl-auto">
                            <div class="fl-wrap-row h-65 font-s-1">
                                <div class="fl-fix fl-mid-left wper-5"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2"><i class="fa fa-square" aria-hidden="true"></i>     ข้าพเจ้ารับทราบ วัตถุประสงค์ในการเก็บรวบรวม ใช้ หรือการเปิดเผยข้อมูลส่วนบุคคล และข้อมูลผลการรับบริการด้านสุขภาพของข้าพเจ้าจากเจ้าหน้าที่ของหน่วยงานผู้ให้บริการแล้ว โดยที่ข้าพเจ้าเข้าใจว่า กระบวนการดังกล่าวเป็นส่วนหนึ่งของการเข้ารับบริการด้านสุขภาพของข้าพเจ้า ซึ่งจะทำให้การรับบริการด้านสุขภาพของข้าพเจ้าเป็นไปอย่างสมบูรณ์</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-65 font-s-1">
                                <div class="fl-fix fl-mid-left wper-5"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2"><i class="fa fa-square" aria-hidden="true"></i>     ข้าพเจ้าทราบว่า ข้าพเจ้ามีสิทธิไม่ยินยอมให้หน่วยงานผู้ให้บริการด้านสุขภาพ เก็บรวบรวม ใช้ และเปิดเผยข้อมูลส่วนบุคคลรวมถึงข้อมูลผลการรับบริการของข้าพเจ้า ภายใต้มาตรการป้องกันรักษาข้อมูลของผู้รับบริการของพริบตา แทนเจอรีน สหคลินิก</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-65 font-s-1">
                                <div class="fl-fix fl-mid-left wper-5"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2"><i class="fa fa-square" aria-hidden="true"></i>     ข้าพเจ้าทราบว่า การให้ความยินยอมเมื่อเข้ารับบริการ ข้าพเจ้ามีสิทธิเพิกถอนความยินยอมเมื่อไรก็ได้ โดยการเพิกถอนความยินยอมนั้นไม่มีผลย้อนหลัง</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-65 font-s-1">
                                <div class="fl-fix fl-mid-left wper-5"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2"><i class="fa fa-square" aria-hidden="true"></i>     ข้าพเจ้าทราบว่า ข้าพเจ้ามีสิทธิขอให้หน่วยงานผู้ให้บริการด้านสุขภาพลบหรือทำลายข้อมูลส่วนบุคคลของข้าพเจ้าที่ให้ไว้ โดยการลบหรือทำลายข้อมูลนั้นอาจเป็นเหตุให้การรับบริการของข้าพเจ้าไม่สามารถดำเนินการต่อไปได้</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-65 font-s-1">
                                <div class="fl-fix fl-mid-left wper-5"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2"><i class="fa fa-square" aria-hidden="true"></i>     ข้าพเจ้ารับทราบว่า ประวัติการรับบริการของข้าพเจ้าจะถูกเก็บรวบรวมไว้เป็นระยะเวลา 5 ปีนับจากความต่อเนื่องในการรับบริการครั้งล่าสุด ตามกฏระเบียบของกระทรวงสาธารณสุข หากล่วงเลยกำหนดระยะเวลาดังกล่าว พริบตา แทนเจอรีน สหคลินิก สามารถทำลายประวัติหรือข้อมูลของข้าพเจ้า</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-65 font-s-1">
                                <div class="fl-fix fl-mid-left wper-5"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2"><i class="fa fa-square" aria-hidden="true"></i>     ในกรณีที่ข้าพเจ้าไม่ยินยอมในการเก็บรวบรวมและใช้ข้อมูลส่วนตัว ได้แก่ เลขบัตรประชาชน ข้อมูลการติดต่อ รวมถึงการตรวจสอบประวัติการรักษา ข้าพเจ้ารับทราบว่า ข้าพเจ้าจะไม่สามารถใช้สิทธิประกันสุขภาพของสำนักงานหลักประกันสุขภาพแห่งชาติได้ และ/หรือ การออกใบรับรองแพทย์ได้</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                        </div>
                    </div>

                    <div class="fl-wrap-col h-5"></div>

                    <div class="fl-wrap-col border" id="consent_sing_main" style="padding: 10px 10px 10px 10px;">
                        <div class="fl-wrap-col h-35">
                            <div class="fl-wrap-row h-35 font-s-1">
                                <div class="fl-fix fl-mid-left wper-5"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2 fw-b">โดยข้าพเจ้าขอให้ความยินยอมแก่หน่วยงานผู้ให้บริการด้านสุขภาพตามรายละเอียดดังต่อไปนี้</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                        </div>

                        <div class="fl-wrap-col fl-auto">
                            <div class="fl-wrap-row h-45 font-s-1">
                                <div class="fl-fix fl-mid-left wper-5"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2 fw-b">1.	การเก็บ รวบรวม การใช้ข้อมูลส่วนบุคคล และข้อมูลการรับบริการ</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-30 font-s-1">
                                <div class="fl-fix fl-mid-left wper-10"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2 fw-b">1.1</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-70 font-s-1 row-hover">
                                <div class="fl-fix fl-mid-left wper-10"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2">
                                        <input type="radio" name="data_sing_1" data-id="data_sing_1" value="Y" class="save-data need-click" data-odata=""/> ข้าพเจ้าให้ความยินยอม ในการเก็บรวบรวมและใช้ข้อมูล วันเดือนปีเกิด อายุ เพศ หมายเลขโทรศัพท์ และ/หรือ ข้อมูลอื่นๆ สำหรับการลงทะเบียนและเข้ารับบริการทางสุขภาพของโครงการที่เกี่ยวข้อง โดยรวมถึงข้อมูลอื่นๆ ที่จำเป็นเพื่อการตรวจสอบประวัติการรักษา และเพื่อประโยชน์ในการให้บริการและให้ความช่วยเหลือทางสุขภาพแก่ข้าพเจ้า เช่น ข้อมูลการติดต่อ ข้อมูลการสนทนา เป็นต้น
                                    </label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-40 font-s-1 row-hover">
                                <div class="fl-fix fl-mid-left wper-10"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2">
                                        <input type="radio" name="data_sing_1" data-id="data_sing_1" value="N" class="save-data need-click2" data-odata=""/> ข้าพเจ้าไม่ให้ความยินยอม ในการเก็บรวบรวมและใช้ข้อมูลดังกล่าว และรับทราบว่าข้าพเจ้าจะไม่สามารถรับบริการด้านสุขภาพได้
                                    </label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-30 font-s-1 open-next">
                                <div class="fl-fix fl-mid-left wper-10"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2 fw-b">1.2</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-40 font-s-1 row-hover open-next">
                                <div class="fl-fix fl-mid-left wper-10"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2">
                                        <input type="radio" name="data_sing_2" data-id="data_sing_2" value="Y" class="save-data clear-data-next" data-odata=""/> ข้าพเจ้าให้ความยินยอม ในการเก็บรวบรวมและใช้หมายเลขบัตรประจำตัวประชาชน สำหรับการลงทะเบียนและเข้ารับบริการทางสุขภาพของโครงการที่ IHRI
                                    </label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-40 font-s-1 row-hover open-next">
                                <div class="fl-fix fl-mid-left wper-10"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2">
                                        <input type="radio" name="data_sing_2" data-id="data_sing_2" value="N" class="save-data clear-data-next" data-odata=""/> ข้าพเจ้าไม่ให้ความยินยอม ในการเก็บรวบรวมและใช้ข้อมูลดังกล่าว และรับทราบว่าข้าพเจ้าจะไม่สามารถใช้สิทธิประกันสุขภาพได้
                                    </label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>

                            <div class="fl-wrap-row h-45 font-s-1 open-next" style="display: none">
                                <div class="fl-fix fl-mid-left wper-5"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2 fw-b">2.	การเก็บสำเนาบัตรประจำตัวประชาชน (ในกรณีที่เจ้าหน้าที่ร้องขอ)</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-40 font-s-1 row-hover open-next" style="display: none">
                                <div class="fl-fix fl-mid-left wper-10"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2">
                                        <input type="radio" name="data_sing_3" data-id="data_sing_3" value="Y" class="save-data clear-data-next" data-odata=""/> ข้าพเจ้าให้ความยินยอม ในการจัดเก็บสำเนาบัตรประจำตัวประชาชน
                                    </label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-40 font-s-1 row-hover open-next" style="display: none">
                                <div class="fl-fix fl-mid-left wper-10"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2">
                                        <input type="radio" name="data_sing_3" data-id="data_sing_3" value="N" class="save-data clear-data-next" data-odata=""/> ข้าพเจ้าไม่ให้ความยินยอม ในการจัดเก็บสำเนาบัตรประจำตัวประชาชน
                                    </label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>

                            <div class="fl-wrap-row h-45 font-s-1 open-next" style="display: none">
                                <div class="fl-fix fl-mid-left wper-5"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2 fw-b">3.  การเปิดเผยข้อมูล</label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-55 font-s-1 row-hover open-next" style="display: none">
                                <div class="fl-fix fl-mid-left wper-10"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2">
                                        <input type="radio" name="data_sing_4" data-id="data_sing_4" value="Y" class="save-data clear-data-next" data-odata=""/> ข้าพเจ้าให้ความยินยอม ในการเปิดเผยข้อมูลแก่หน่วยงานบริการด้านสุขภาพและภาคีเครือข่ายที่เกี่ยวข้อง เพื่อประโยชน์ที่เกี่ยวเนื่องต่อการรับบริการของข้าพเจ้า ภายใต้ข้อกำหนดของกฎหมายที่เกี่ยวข้อง
                                    </label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                            <div class="fl-wrap-row h-40 font-s-1 row-hover open-next" style="display: none">
                                <div class="fl-fix fl-mid-left wper-10"></div>
                                <div class="fl-fill fl-mid-left">
                                    <label class="font-s-2">
                                        <input type="radio" name="data_sing_4" data-id="data_sing_4" value="N" class="save-data clear-data-next" data-odata=""/> ข้าพเจ้าไม่ให้ความยินยอมในการเปิดเผยข้อมูล
                                    </label>
                                </div>
                                <div class="fl-fix fl-mid-left wper-5"></div>
                            </div>
                        </div>
                    </div>

                    <div class="fl-wrap-col h-50" id="consent_sing_bt" style="padding: 10px 10px 10px 10px; display: none;">
                        <div class="fl-wrap-row h-45">
                            <div class="fl-fill fl-mid">
                                <button class="btn" id="btCfSingConsent" style="padding-bottom: 2px; padding-top: 2px;"><i class="fa fa-check" aria-hidden="true" style="color: green;"> ยืนยัน/ Confrim</i></button><i class="fas fa-spinner fa-spin spinner" style="display:none;"></i>
                            </div>
                        </div>
                    </div>';

    echo $html_bind;
?>

<script>
    $(document).ready(function(){
        //form load first
        <? echo $sJS; ?>

        $("#consent_sing_main .need-click").off("change");
        $("#consent_sing_main .need-click").on("change", function(){
            if($(this).filter(":checked").val() == "N" || $(this).filter(":checked").val() === undefined){
                $(".open-next").hide();
                $("#consent_sing_bt").hide();
                $(".clear-data-next").prop("checked", false);
                return false;
            }
            else{
                $(".open-next").show();
                $("#consent_sing_bt").show();
                return false;
            }
        });

        $("#consent_sing_main .need-click2").off("change");
        $("#consent_sing_main .need-click2").on("change", function(){
            if($(this).filter(":checked").val() == "N" || $(this).filter(":checked").val() === undefined){
                $(".open-next").hide();
                $("#consent_sing_bt").hide();
                $(".clear-data-next").prop("checked", false);
                return false;
            }
            else{
                $(".open-next").show();
                $("#consent_sing_bt").show();
                return false;
            }
        });

        $("#consent_sing_main .need-click").change();

        $("#btCfSingConsent").off("click");
        $("#btCfSingConsent").on("click", function(){
            saveFormData();
            var objthis = $(this);
            closeDlg(objthis, "0");
        });
    });

    //function save all form
    function getWObjValue(obj){
        var sValue = "";
        if($(obj)){
            var sTagName = $(obj).prop("tagName").toUpperCase();

            if(sTagName=="INPUT"){
                if($(obj).prop("type")){
                    if($(obj).prop("type").toLowerCase()=="checkbox"){
                        sValue = ($(obj).prop("checked"))?1:"";
                    }
                    else if($(obj).prop("type").toLowerCase()=="radio"){
                        var sName = $(obj).attr("name");
                        sValue = $("input[name='"+sName+"']").filter(":checked").val();
                    }
                    else{
                        sValue = $(obj).val();
                    }
                }
                else{
                    sValue = $(obj).val();
                }
            }
            else{
                sValue = $(obj).val();
            }

            if($(obj).hasClass("v_date")){
                var arrDate = sValue.split("/");

                if(arrDate.length == 3){
                    sValue = (parseInt(arrDate[2]) - 543)+"-"+arrDate[1]+"-"+ arrDate[0] ;
                }
            }
            
            return sValue;
        }
    }

    function saveFormData(){
        var divSaveData = "div_form_view_data";
        var lst_data_obj = [];

        var old_value = "";
        $("#consent_sing_main .save-data").each(function(ix,objx){
            var objVal = "";
            var odata_val = "";
            
            objVal = getWObjValue($(objx));
            odata_val = $(objx).data("odata");
            if(typeof odata_val === "undefined"){
                odata_val = "";
            }
            if(typeof objVal === "undefined"){
                objVal  = "";
            }
            odata_val = (odata_val?odata_val.toString().replace(/"|'/g,''):odata_val); //ไม่ใช้แล้วเพราะใช้ json_encode()
            // console.log(odata_val+"new"+objVal);
            // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val.toString().replace(/'/g,"")); //cn_family_history_text
            // console.log("datavalue "+$(objx).data("id")+"- newdata "+ objVal+"/ odata "+odata_val);

            if(objVal != odata_val){
                var data_item = {};

                data_item[$(objx).data("id")] = (objVal?objVal.toString().replace(/"|'/g,'') : objVal);
                lst_data_obj.push(data_item);
                // console.log("data_id: "+$(objx).data("id")+":"+objVal+"-"+odata_val+";");
            }

            old_value = $(objx).data("id");
        });

        var d = new Date();
        var month = d.getMonth()+1;
        var day = d.getDate();

        var dt = new Date();
        var time = (dt.getHours()<10? "0":"")+dt.getHours() + ":" + (dt.getMinutes()<10? "0":"")+dt.getMinutes() + ":" + (dt.getSeconds()<10? "0":"")+dt.getSeconds();

        var output = d.getFullYear() + '-' + (month<10 ? '0' : '') + month + '-' + (day<10 ? '0' : '') + day+" "+time;

        if(lst_data_obj.length > 0){
            var aData = {
                app_mode: "ins_consent",
                uid: $("#consent_main").data("uid"),
                sid: $("#consent_main").data("sid"),
                date_now: output,
                dataid:lst_data_obj,
            };
            // console.log(aData);

            callAjax("consent_db_ins_upd.php", aData, saveFormDataComplete);
            $("#btCfSingConsent").next(".spinner").show();
            $("#btCfSingConsent").hide();
        }
        else{
            $.notify("No data change", "warn");
        }
    }

    function saveFormDataComplete(flagSave, aData, rtnDataAjax){
        // console.log(flagSave+"/"+rtnDataAjax);
        if(flagSave){
            $.notify("Save Data", "success");

            //update all odata of  value changed data_id
            var conValue = "";
            Object.keys(aData.dataid).forEach(function(i){
                Object.keys(aData.dataid[i]).forEach(function(data_id){
                    conValue = aData.dataid[i][data_id];
                    conValue = conValue;
                    // console.log(i+data_id + " - " +conValue);
                    $("[name="+data_id+"]").data("odata", conValue);
                });
            });
        }

        $("#btCfSingConsent").next(".spinner").hide();
        $("#btCfSingConsent").show();
    }
</script>