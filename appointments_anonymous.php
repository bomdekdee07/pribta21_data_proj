<?
    include("in_session.php");
    include_once("in_php_function.php");
?>

<div class="fl-wrap-col fl-auto" id="anonymous_main">
    <div class="fl-wrap-row h-20"></div>
    <!-- <form role="form" method="post" action="" target="iframe_target_anonymous">
        <iframe id="iframe_target_anonymous" name="iframe_target_anonymous" src="" ></iframe> -->
        <div class="fl-wrap-row h-35 font-s-3">
            <div class="fl-fix w-20"></div>
            <div class="fl-fix w-100 fl-mid-right holiday-mr-2">
                ชื่อ:
            </div>
            <div class="fl-fix w-300 fl-mid-left">
                <input type="text" name="fname" style="min-height: 29px; max-height: 29px; min-width: 299px;">
            </div>
        </div>
        <div class="fl-wrap-row h-35 font-s-3">
            <div class="fl-fix w-20"></div>
            <div class="fl-fix w-100 fl-mid-right holiday-mr-2">
                นามสกุล:
            </div>
            <div class="fl-fix w-300 fl-mid-left">
                <input type="text" name="sname" style="height: 29px; max-height: 29px; min-width: 299px;">
            </div>
        </div>
        <div class="fl-wrap-row h-35 font-s-3">
            <div class="fl-fix w-20"></div>
            <div class="fl-fix w-100 fl-mid-right holiday-mr-2">
                เบอร์โทร:
            </div>
            <div class="fl-fix w-300 fl-mid-left">
                <input type="text" name="tel_no" style="height: 29px; max-height: 29px; min-width: 299px;" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')">
            </div>
        </div>
        <div class="fl-wrap-row h-30">
            <div class="fl-fix fl-mid-left" style="min-width: 362px; max-width: 362px;"></div>
            <div class="fl-fix w-60 fl-mid-left">
                <button class="btn btn-success" id="btSubmitAnnonymous" style="padding: 1px 15px;"><span class="fw-b font-s-1">ยืนยัน</span></button>
            </div>
        </div>
    <!-- </form> -->
    <div class="fl-wrap-row h-20"></div>
    <div class="fl-wrap-col" id="detail_dup_uid"></div>
    <div class="fl-fix appointments-text-left" style="min-width:10px">
        <button id="btn_cancel_hide_dup" hidden class="btn btn-danger border" type="button"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
    </div>
</div>

<script>
    $(document).ready(function(){
        //Submit
        $("#anonymous_main #btSubmitAnnonymous").off("click");
        $("#anonymous_main #btSubmitAnnonymous").on("click", function(){
            var check_name = $("#anonymous_main [name=fname]").val();
            var check_tel = $("#anonymous_main [name=tel_no]").val();
            if(check_name == "")
                alert("กรุณาใส่ชื่อ");
            if(check_tel == "")
                alert("กรุณาเบอร์โทร");

            if(check_name != "" && check_tel != ""){
                var tel_noS = $("#anonymous_main [name=tel_no]").val();
                var aData = {
                    tel_no: tel_noS.replace(/\s/g, '')
                };
                // console.log(aData);

                $.ajax({url: "appointments_anonymous_ajax_dup.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        var data_split = result.split("///");
                        // console.log(data_split[0]);
                        if(data_split[0] == "yesDupMany"){
                            $("#anonymous_main #detail_dup_uid").children().remove();
                            $("#anonymous_main #detail_dup_uid").append(data_split[1]);
                        }
                        else if(data_split[0] == "yesDupOne"){
                            alert("พบข้อมูลซ้ำในระบบ");
                            $("#anonymous_main #btn_cancel_hide_dup").trigger("click", [data_split[1]] );
                        }
                        else{
                            if(confirm('คุณแน่ใจที่จะเพิ่ม Anonymous?')){
                                var fnameS = $("#anonymous_main [name=fname]").val();
                                var snameS = $("#anonymous_main [name=sname]").val();
                                var tel_noS = $("#anonymous_main [name=tel_no]").val();
                                var aData = {
                                    u_mode: "create_uid",
                                    fname: fnameS,
                                    sname: snameS,
                                    tel_no: tel_noS
                                };

                                $.ajax({url: "patient_a.php", 
                                    method: "POST",
                                    cache: false,
                                    data: aData,
                                    success: function(result){
                                        var check_data_rt = JSON.parse(result);
                                        if(check_data_rt["res"] == "1" && check_data_rt["uid"] != ""){
                                            alert("เพิ่ม Anonymous สำเร็จ");
                                            $("#anonymous_main #btn_cancel_hide_dup").trigger("click", [check_data_rt["uid"]]);
                                        }
                                    }
                                });
                            }
                        }
                    }
                });
            }
        });

        $("#anonymous_main #btn_cancel_hide_dup").off("click");
        $("#anonymous_main #btn_cancel_hide_dup").on("click", function(event, valUid){
            var objthis = $(this);
            closeDlg(objthis, valUid);
        });
    });
</script>