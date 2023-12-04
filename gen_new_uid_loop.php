<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $data_loop_uid = array();
    $query = "select name, lastname, sex, gender, dob, id_card, addressid, line, pid from temp_genuid_real order by pid;";
    $stmt = $mysqli->prepare($query);
    
    if($stmt->execute()){
        $stmt->bind_result($fname, $sname, $sex, $gender, $dob, $id_card, $address, $line, $pid);
        while($stmt->fetch()){
            $data_loop_uid[$pid]["name"] = $fname;
            $data_loop_uid[$pid]["sname"] = $sname;
            $data_loop_uid[$pid]["sex"] = $sex;
            $data_loop_uid[$pid]["gender"] = $gender;
            $data_loop_uid[$pid]["dob"] = $dob;
            $data_loop_uid[$pid]["id_card"] = strval($id_card);
            $data_loop_uid[$pid]["address"] = $address;
            $data_loop_uid[$pid]["line"] = $line;
        }
    }
    $stmt->close();
    $mysqli->close();

    $js_bindHtml = "";
    foreach($data_loop_uid as $key_pid => $val){
        $js_bindHtml .= '<div class="obj_new_uid" data-name="'.$val["name"].'" data-sname="'.$val["sname"].'" data-sex="'.$val["sex"].'" data-gender="'.$val["gender"].'" data-dob="'.$val["dob"].'" data-idcard="'.$val["id_card"].'" data-address="'.$val["address"].'" data-line="'.$val["line"].'" data-pid="'.$key_pid.'">'.$val["name"].'</div>';
    }
    echo $js_bindHtml;
?>

<script>
    $(document).ready(function(){
        $(".obj_new_uid").each(function(){
            var fnameS = $(this).data("name");
            var snameS = $(this).data("sname");
            var sexS = $(this).data("sex");
            var genderS = $(this).data("gender");
            var dobrS = $(this).data("dob");
            var idcardS = $(this).data("idcard");
            var addressS = $(this).data("address");
            var lineS = $(this).data("line");
            var pidS = $(this).data("pid");

            var aData = {
                u_mode: "create_uid",
                fname: fnameS,
                sname: snameS,
                sex: sexS,
                gender: genderS,
                date_of_birth: dobrS,
                citizen_id: idcardS,
                address: addressS,
                line_id: lineS
            };
            // console.log(aData);
            $.ajax({url: "patient_a.php", 
                method: "POST",
                cache: false,
                data: aData,
                success: function(result){
                    var check_data_rt = JSON.parse(result);
                    if(check_data_rt["res"] == "1" && check_data_rt["uid"] != ""){
                        var aData_upd = {
                            u_mode: "create_uid",
                            uid: check_data_rt["uid"],
                            pid: pidS
                        };

                        $.ajax({url: "gen_new_uid_loop_update.php", 
                            method: "POST",
                            cache: false,
                            data: aData_upd,
                            success: function(result){
                                console.log("susses: "+check_data_rt["uid"]);
                            }
                        });
                    }
                    else{
                        console.log("error: "+pidS);
                    }
                }
            });
        });
    });
</script>