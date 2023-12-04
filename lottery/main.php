<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $sid = getSS("s_id");
?>

<div class="fl-wrap-row h-100 border-bt-head">
    <div class="fl-fix w-200 fl-mid">
        <img src="assets/image/img_main.png" alt="Italian Trulli" width="150" height="100">
    </div>
    <div class="fl-fill fl-mid-right holiday-mt-4">
        <button id="bt_logout" class="btn font-s-1 btn-danger" style="padding: 3px 3px 3px;"><i class="fa fa-sign-out-alt" aria-hidden="true"></i> <span class="fw-b">LOGOUT</span></button>
    </div>
    <div class="fl-fix w-5"></div>
</div>

<div class="fl-wrap-row">
    <div class="fl-wrap-col font-s-2 body-menu content2 isOpen2">
        <div class="wrapper">
            <div class="sidebar">
                <ul class="topnav">
                    <li><a class="link active" href="#" data-id="link-home" style="color: white;"><i class="fa fa-home" aria-hidden="true"></i> หน้าหลัก</a></li>
                    <li><a class="link" href="#" data-id="link-lottery" style="color: white;"><i class="fa fa-plus-circle" aria-hidden="true"></i> แทงหวย</a></li>
                    <li><a class="link" href="#" data-id="link-report" style="color: white;"><i class="fa fa-eye" aria-hidden="true"></i> รายงาน</a></li>
                    <li><a class="link" href="#" data-id="link-manage" style="color: white;"><i class="fa fa-cubes" aria-hidden="true"></i> จัดการรายการ</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="fl-wrap-col w-15">
        <div class="fl-fix w-5 content isOpen fl-mid btn-menu">
            <i class="fa fa-angle-double-left fa-xs chang-img" style="color: blue;" aria-hidden="true"></i>
        </div>
    </div>
    <div class="fl-wrap-col fl-auto" id="detail_sub"></div>
</div>

<script>
    $(document).ready(function(){
        $("#lotteryMain .btn-menu").off("click");
        $("#lotteryMain").on("click", ".btn-menu", function(){
            var content = $("#lotteryMain .content2");
            content.toggle("isOpen2");

            var check_img_left = $("#lotteryMain .btn-menu").find(".fa-angle-double-left").val();
            var check_img_right = $("#lotteryMain .btn-menu").find(".fa-angle-double-right").val();
            // console.log(check_img_left+"/"+check_img_right);
            if(typeof check_img_left !== "undefined"){
                $("#lotteryMain .chang-img").removeClass("fa-angle-double-left");
                $("#lotteryMain .chang-img").addClass("fa-angle-double-right");
            }
            if(typeof check_img_right !== "undefined"){
                $("#lotteryMain .chang-img").removeClass("fa-angle-double-right");
                $("#lotteryMain .chang-img").addClass("fa-angle-double-left");
            }
        });

        $("#lotteryMain .link").off("click");
        $("#lotteryMain .link").on("click", function(){
            $("#lotteryMain .link").removeClass("active");
            if($(this).data("id") == "link-home"){
                $(this).addClass("active");
            }
            else if($(this).data("id") == "link-lottery"){
                $(this).addClass("active");

                var aData = {};
                $.ajax({url: "manage_lottery_main.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        $("#lotteryMain #detail_sub").children().remove();
                        $("#lotteryMain #detail_sub").append(result);
                    }
                });
            }
            else if($(this).data("id") == "link-report"){
                $(this).addClass("active");
            }
            else if($(this).data("id") == "link-manage"){
                $(this).addClass("active");

                var aData = {};
                $.ajax({url: "manage_item_main.php", 
                    method: "POST",
                    cache: false,
                    data: aData,
                    success: function(result){
                        $("#lotteryMain #detail_sub").children().remove();
                        $("#lotteryMain #detail_sub").append(result);
                    }
                });
            }
        })

        $("#lotteryMain #bt_logout").off("click");
        $("#lotteryMain").on("click", "#bt_logout", function(){
            var aData = {
                mode: "logout"
            };

            $.ajax({
                url: "login_a.php",
                method: "POST",
                cache: false,
                data: aData,
                success: function(){
                    var url_gen = "patient_system_login.php";
                    $("#lotteryMain").load(url_gen, function(){
                        endLoad($("#patient_system_main"), $(""));
                    });
                }
            })
        });
    });
</script>