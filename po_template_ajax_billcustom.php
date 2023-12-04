<?
    include("in_session.php");
    include("in_db_conn.php");
    include_once("in_php_function.php");

    $req_id = getQS("req_id");

    $data_j_bill_custom = array();
    $query = "select uid,
        bill_title,
        bill_name,
        bill_address,
        email,
        bill_attention
    from j_bill_custom
    where uid = ?;";

    $stmt = $mysqli -> prepare($query);
    $stmt -> bind_param("s", $req_id);

    if($stmt -> execute()){
        $stmt -> bind_result($uid, $bill_title, $bill_name, $bill_address, $email, $bill_attention);
        while($stmt -> fetch()){
            $data_j_bill_custom[$uid][$bill_title]["uid"] = $uid;
            $data_j_bill_custom[$uid][$bill_title]["title"] = $bill_title;
            $data_j_bill_custom[$uid][$bill_title]["name"] = $bill_name;
            $data_j_bill_custom[$uid][$bill_title]["addr"] = $bill_address;
            $data_j_bill_custom[$uid][$bill_title]["email"] = $email;
            $data_j_bill_custom[$uid][$bill_title]["att"] = $bill_attention;
        }
        // print_r($data_j_bill_custom);
    }
    $stmt->close();
    $mysqli->close();

    $sJS_bill_custom_sub = "";
    $count_loop = 0;
    
    if(count($data_j_bill_custom) > 0){
        foreach($data_j_bill_custom as $key_uid => $bill_title){
            foreach($bill_title as $key_billTitle => $value){
                $sJS_bill_custom_sub .= '<div class="fl-wrap-row row-color row-hover fs-xsmall fl-mid-left h-25 click-row">
                                                <div class="fl-fix wper-10 fl-mid-left fs-xsmall">
                                                    <span class="holiday-ml-1 holiday-mt-s1"><input type="radio" name="bill_title" checked value="'.$value["title"].'"></span><span class="holiday-ml-2">'.$value["title"].'</span>
                                                </div>
                                                <div class="fl-fix wper-20 fl-mid-left fs-xsmall">
                                                    <span class="holiday-ml-2">'.$value["name"].'</span>
                                                </div>
                                                <div class="fl-fix fl-mid-left fs-xsmall wper-15">
                                                    <span class="holiday-ml-2">'.$value["email"].'</span>
                                                </div>
                                                <div class="fl-fill fl-mid-left fs-xsmall">
                                                    <span class="holiday-ml-2">'.$value["addr"].'</span>
                                                </div>
                                                <div class="fl-fix fl-mid-left fs-xsmall wper-20">
                                                    <span class="holiday-ml-2">'.$value["att"].'</span>
                                                </div>
                                                <div class="fl-fix fl-mid-left fs-xsmall wper-5">
                                                    <i class="fa fa-edit edit-click fa-lg" aria-hidden="true" data-reqid="'.$req_id.'" data-addrtitle="'.$value["title"].'"></i>
                                                </div>
                                        </div>';
            }
        }
    }
    else{
        $sJS_bill_custom_sub .= '<label class="h-10">
                                        <div class="fl-wrap-row fs-xsmall fl-mid-left">
                                            <div class="fl-fix w-10"></div>
                                            <div class="fl-fix wper-20 fl-mid-left fs-xsmall">
                                                <class="holiday-ml-s6">No found data</span>
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

    echo $sJS_bill_custom_sub;
?>