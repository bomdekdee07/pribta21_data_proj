<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $type_img = (getQS("type_img")!=""? getQS("type_img"): "anal");

    $str_type_img = "";
    $query = "SELECT type_img_id,
        type_img_name
    from type_img_master;";
    $stmt = $mysqli->prepare($query);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $str_type_img .= '<option value="'.$row["type_img_id"].'">'.$row["type_img_name"].'</option>';
        }
    }

    $hidden_val = "";
    $hidden_val .= '<input type="hidden" name="uid" value="'.$uid.'" class="hidden-val">
                    <input type="hidden" name="coldate" value="'.$coldate.'" class="hidden-val">
                    <input type="hidden" name="coltime" value="'.$coltime.'" class="hidden-val">
                    <input type="hidden" name="type_img" value="'.$type_img.'" class="hidden-val">
                    <input type="hidden" name="type_file" value="" class="hidden-val">';
?>

<div class="fl-wrap-col fl-auto" id="doctor_img_dlg">
    <div class="fl-wrap-row h-5"></div>
    <div class="fl-wrap-row h-130">
        <div class="fl-fix w-5"></div>
        <div class="fl-wrap-col border" style="min-width: 700px; max-width: 700px;">
            <form id="form_submit_img" method="post" enctype="multipart/form-data" target="_blank">
                <div class="fl-wrap-row h-5"></div>
                <div class="fl-wrap-row h-25">
                    <div class="fl-fix w-15"></div>
                    <div class="fl-fix w-65 fl-mid-left font-s-2 fw-b">Type:</div>
                    <div class="fl-fix w-10"></div>
                    <div class="fl-fix w-200 fl-mid-left font-s-2">
                        <select name="type_img" style="width: 100%;">
                            <?echo $str_type_img; ?>
                        </select>
                    </div>
                    <div class="fl-fix w-100 fl-mid-left font-s-2">
                        <button class="btn btn-success" name="bt_add_type_img" style="padding: 0px 7px 0px 7px;" title="เพิ่มประเภท"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div class="fl-wrap-row h-45">
                    <div class="fl-fix w-15"></div>
                    <div class="fl-fix w-65 fl-mid-left font-s-2 fw-b">Comment:</div>
                    <div class="fl-fix w-10"></div>
                    <div class="fl-fix w-400 fl-mid-left font-s-1">
                        <textarea class="" name="img_comment" style="width: 99%;"></textarea>
                    </div>
                </div>
                <div class="fl-wrap-row h-10"></div>

            
                <div class="fl-wrap-row h-50">
                    <div class="fl-fix w-15"></div>
                    <div class="fl-fix w-250 fl-mid-left font-s-1 fw-b">
                        <input type='file' class="clear-input" name="fileToUpload[]" id="fileToUpload[]" multiple />
                        <? echo $hidden_val; ?>
                    </div>
                    <div class="fl-fix w-80 fl-mid-left font-s-1">
                        <button class="btn btn-info fw-b" name="doctor_img_sumit" id="doctor_img_sumit" style="padding: 0px 5px 0px 5px;"><i class="fa fa-upload" aria-hidden="true"> Upload</i></button>
                    </div>
                    <div class="fl-fix w-100 fl-mid complete-bt-img" style="display: none;">
                        <button class="btn h-20 fw-b font-s-1" style="padding: 0px 4px 0px 4px; color: #89F151; background-color: #585858;" disabled>Completed</button> 
                    </div>
                </div>
            </form>

            <div class="fl-wrap-row h-5"></div>
        </div>
        <div class="fl-wrap-col border" style="display: none;">
            <div class="fl-wrap-row h-130">
                <div class="fl-fill fl-mid">
                    <div class="fl-fix w-200 fl-mid font-s-1">
                        <img id="blah" src="#" alt=". your image upload." style="max-width: 100%; max-height: 100%; object-fit: contain;"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="fl-fix w-5"></div>
    </div>
    <div class="fl-wrap-row h-5"></div>
    <div class="fl-wrap-row h-400">
        <div class="fl-fix w-5"></div>
        <div class="fl-wrap-col border">
            <div class="fl-wrap-row h-5"></div>
            <div class="fl-wrap-row h-20">
                <div class="fl-fix w-5 border-bt"></div>
                <div class="fl-fix w-30 border-bt"></div>
                <div class="fl-fix w-185 font-s-2 fw-b border-bt fl-mid-left">Name</div>
                <div class="fl-fill font-s-2 fw-b border-bt fl-mid-left">Comment</div>
                <div class="fl-fix w-160 font-s-2 fw-b border-bt fl-mid-left">Date</div>
                <div class="fl-fix w-210 font-s-2 fw-b border-bt fl-mid-left">User</div>
                <div class="fl-fix w-20 font-s-2 fw-b border-bt fl-mid-left"></div>
                <div class="fl-fix w-5 border-bt"></div>
            </div>
            <div class="fl-wrap-row h-350">
                <div class="fl-wrap-col fl-auto" id="row_detail_img"><? include("doctor_img_list.php"); ?></div>
            </div>
        </div>
        <div class="fl-fix w-5"></div>
    </div>
</div>

<script>
    $(document).ready(function(){
        // change type
        $("#form_submit_img [name=type_img]").off("cahnge");
        $("#form_submit_img [name=type_img]").on("change", function(){
            var valThis = $(this).val();
            var sUid = $("#doctor_img_dlg [name=uid]").val();
            var sColdate = $("#doctor_img_dlg [name=coldate]").val();
            var sColtime = $("#doctor_img_dlg [name=coltime]").val();

            $("#form_submit_img [name=type_img]").val(valThis);

            var sUrl = "doctor_img_list.php?uid="+sUid+"&coldate="+sColdate+"&coltime="+sColtime+"&type_img="+valThis;
            $("#doctor_img_dlg #row_detail_img").load(sUrl);
        });

        // add type
        $("#form_submit_img [name=bt_add_type_img]").off("click");
        $("#form_submit_img [name=bt_add_type_img]").on("click", function(e){
            e.preventDefault();
            var sUid = $("#doctor_img_dlg [name=uid]").val();
            var sColdate = $("#doctor_img_dlg [name=coldate]").val();
            var sColtime = $("#doctor_img_dlg [name=coltime]").val();
            sUrl="doctor_img_add_type_dlg.php?uid="+sUid+"&coldate="+sColdate+"&coltime="+sColtime;
            // console.log(sUrl);

            showDialog(sUrl, "Type Management", "35%", "40%","",
            function(sResult){
                //CLose function
            },false,function(){
                //Load Done Function
            });
        });

        // upload
        $("#form_submit_img").off("submit");
        $("#form_submit_img").on("submit", function(e){
            e.preventDefault();
            var url = "doctor_img_upload_ajax.php";
            var valthis = this;
            
            $.ajax({
                url: url,
                type: 'post',
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function(sResult){
                    console.log($.parseJSON(sResult));
                    sResult = $.parseJSON(sResult);
                    $("#doctor_img_dlg [name=type_file]").val(sResult.type_file);

                    if(sResult.status == "1"){
                        var sUid = $("#doctor_img_dlg [name=uid]").val();
                        var sColdate = $("#doctor_img_dlg [name=coldate]").val();
                        var sColtime = $("#doctor_img_dlg [name=coltime]").val();
                        var sTypeimg = $("#doctor_img_dlg [name=type_img]").val();
                        var sTypeFile = $("#doctor_img_dlg [name=type_file]").val();

                        var sUrl = "doctor_img_list.php?uid="+sUid+"&coldate="+sColdate+"&coltime="+sColtime+"&type_img="+sTypeimg+"&type_file="+sTypeFile;
                        $("#doctor_img_dlg #row_detail_img").load(sUrl);
                        $("#doctor_img_dlg .clear-input").val("");
                        $("#doctor_img_dlg [name=img_comment]").val("");
                        $("#doctor_img_dlg .complete-bt-img").show();
                    }
                    else{
                        $("#doctor_img_dlg .complete-bt-img").hide();
                        alert("Not complete img or log.");
                    }
                },
                async: false
            });
        });
    });

    function readURL(input) {
            if (input.files && input.files[0]) {
                $("#doctor_img_dlg .complete-bt-img").hide();
                var reader = new FileReader();
                // console.log("test:"+input);

                reader.onload = function (e) {
                    $('#blah')
                        .attr('src', e.target.result)
                        .width(95)
                        .height(120);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
</script>