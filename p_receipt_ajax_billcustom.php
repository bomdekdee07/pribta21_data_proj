<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $uid = getQS("uid");

    $bind_param = "";
    $array_val = array();

    $data_j_bill_custom = array();
    $query = "select bill_title,
        bill_name,
        bill_address,
        bill_tax
    from j_bill_custom";

    if($uid != ""){
        $query .= " where uid = ?";
        $bind_param .= "s";
        $array_val[] = $uid;
    }
    else{
        $query .= " where uid = ?";
        $bind_param .= "s";
        $array_val[] = "P00-00000";
    }

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param($bind_param, ...$array_val);

    if($stmt -> execute()){
        $stmt -> bind_result($bill_title, $bill_name, $bill_address, $bill_tax);
        while($stmt -> fetch()){
            $data_j_bill_custom[$bill_title]["title"] = $bill_title;
            $data_j_bill_custom[$bill_title]["name"] = $bill_name;
            $data_j_bill_custom[$bill_title]["address"] = $bill_address;
            $data_j_bill_custom[$bill_title]["tax"] = $bill_tax;
        }
        // print_r($data_j_bill_custom);
    }
    $stmt->close();
    $mysqli->close();

    $sJS_bill_custom_sub = "";
    $count_loop = 0;
    
    if(count($data_j_bill_custom) > 0){
        $sJS_bill_custom_sub .= '<div id="ajax_bill_custom" class="fl-wrap-col fl-auto h-115"">';

        foreach($data_j_bill_custom as $key => $value){
            if($count_loop++ == 0){
                $sJS_bill_custom_sub .= '<label class="h-15">
                                            <div class="fl-wrap-row fs-xsmall fl-mid-left">
                                                <div class="fl-fix wper-20 fl-mid-left fs-xsmall">
                                                    <span class="holiday-ml-1 holiday-mt-s1"><input type="radio" name="bill_title" value="" checked></span><span class="holiday-ml-s6">Defult</span>
                                                </div>
                                                <div class="fl-fix wper-25 fl-mid-left fs-xsmall">
                                                    <span class="holiday-ml-2"></span>
                                                </div>
                                                <div class="fl-fix wper-15 fl-mid-left fs-xsmall">
                                                    <span class="holiday-ml-2"></span>
                                                </div>
                                                <div class="fl-fill fl-mid-left fs-xsmall">
                                                    <span class="holiday-ml-2"></span>
                                                </div>
                                            </div>
                                        </label>';
            }

            $sJS_bill_custom_sub .= '<label class="h-25">
                                        <div class="fl-wrap-row row-color fs-xsmall fl-mid-left">
                                            <div class="fl-fix wper-20 fl-mid-left fs-xsmall">
                                                <span class="holiday-ml-1 holiday-mt-s1"><input type="radio" name="bill_title" value="'.$value["title"].'"></span><span class="holiday-ml-s6">'.$value["title"].'</span>
                                            </div>
                                            <div class="fl-fix wper-25 fl-mid-left fs-xsmall">
                                                <span class="holiday-ml-2">'.$value["name"].'</span>
                                            </div>
                                            <div class="fl-fix wper-15 fl-mid-left fs-xsmall">
                                                <span class="holiday-ml-2">'.$value["tax"].'</span>
                                            </div>
                                            <div class="fl-fix fl-mid-left fs-xsmall wper-30">
                                                <span class="holiday-ml-2">'.$value["address"].'</span>
                                            </div>
                                            <div class="fl-fill fl-mid fs-xsmall fw-b">
                                                <i class="fa fa-edit edit-click fa-lg" aria-hidden="true" data-uid="'.$uid.'" data-addrtitle="'.$value["title"].'"></i>
                                            </div>
                                        </div>
                                    </label>';
        }
    }
    else{
        $sJS_bill_custom_sub .= '<div id="ajax_bill_custom" class="fl-wrap-col fl-auto h-115"">';
        $sJS_bill_custom_sub .= '<label class="h-10">
                                        <div class="fl-wrap-row fs-xsmall fl-mid-left">
                                            <div class="fl-fix wper-20 fl-mid-left fs-xsmall">
                                                <span class="holiday-ml-1 holiday-mt-s1"><input type="radio" name="bill_title" value="" checked></span><span class="holiday-ml-s6">Defult</span>
                                            </div>
                                            <div class="fl-fix wper-25 fl-mid-left fs-xsmall">
                                                <span class="holiday-ml-2"></span>
                                            </div>
                                            <div class="fl-fix wper-15 fl-mid-left fs-xsmall">
                                                <span class="holiday-ml-2"></span>
                                            </div>
                                            <div class="fl-fill fl-mid-left fs-xsmall">
                                                <span class="holiday-ml-2"></span>
                                            </div>
                                        </div>
                                    </label>';
    }
    $sJS_bill_custom_sub .= '</div>';

    echo $sJS_bill_custom_sub;
?>

<script>
    $(document).ready(function() {
        $("#ajax_bill_custom .edit-click").off("click");
        $("#ajax_bill_custom .edit-click").on("click", function() {
            var uid_s = $(this).data("uid");
            var addr_title_s = $(this).data("addrtitle");
            var url_receipt_create = "p_receipt_create.php?uid="+uid_s+"&addrtitle="+addr_title_s;

            showDialog(url_receipt_create, "Receipt management", "360", "350", "", function(sResult){    
                var url_gen_close = "p_receipt_ajax_billcustom.php?uid="+uid_s;

                $("#bill_custom_sub").load(url_gen_close);
            }, false, function(sResult){});
        });
    });
</script>