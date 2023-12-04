<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $sid = getSS("s_id");

    $days_array = array(1=>"จันทร์", 2=>"อังคาร", 3=>"พุธ", 4=>"พฤหัส", 5=>"ศุกร์", 6 =>"เสาร์", 7=>"อาทิตย์");
    $data_item = array();
    $query = "SELECT item.item_code,
        item.item_name,
        d_open.days_code,
        time_c.time_close,
        award.correct_number,
        item.upd_date
    from items_master item
    left join time_close time_c on(time_c.item_code = item.item_code)
    LEFT JOIN days_open d_open on(d_open.item_code = item.item_code)
    left join award_number award on(award.item_code = item.item_code and award.upd_date = curdate())
    order by item.item_code, d_open.days_code;";

    $stmt = $mysqli->prepare($query);
    
    $old_itemCode = "";
    $loopCount = 0;
    $str_start_name_day = "";
    $str_end_name_day = "";
    if($stmt->execute()){
        $stmt->bind_result($item_code, $item_name, $days_code, $time_close, $correct_number, $update_date);
        while($stmt->fetch()){
            if($old_itemCode != $item_code){
                $loopCount = 0;
                $str_end_name_day = "";
            }
            $loopCount += 1;

            $data_item[$item_code]["code"] = $item_code;
            $data_item[$item_code]["name"] = $item_name;
            $data_item[$item_code]["time"] = $time_close;
            $data_item[$item_code]["award"] = $correct_number;
            if($loopCount == 1){
                $str_start_name_day = isset($days_code)? $days_array[$days_code]:"";
            }
            if($loopCount > 1 && $loopCount != 0){
                $str_end_name_day = isset($days_code)? $days_array[$days_code]:"";
            }
            $data_item[$item_code]["date"] = $str_start_name_day!=""? $str_start_name_day." - ".$str_end_name_day:"";

            $old_itemCode = $item_code;
        }
    }
    $stmt->close();
    $mysqli->close();

    $html_str = "";
    $html_str .= '<div class="fl-wrap-row h-30 font-s-3 fw-b holiday-mt-3">
                    <div class="fl-fix w-40"></div>
                    <div class="fl-fill fl-mid-left border-bt" style="color: blue">
                        Items Master Management
                    </div>
                    <div class="fl-fix w-40"></div>
                </div>
                <div class="fl-wrap-row h-30">
                    <div class="fl-fix w-40"></div>
                    <div class="fl-fill fl-mid-left">
                        <button id="addNewBtn" type="button" style="padding: 3px 4px 2px;" class="btn btn-success font-s-2"><i class="fa fa-plus-square" aria-hidden="true"></i> เพิ่มประเภทหวย</button>
                    </div>
                </div>
                <div class="fl-wrap-row h-10"></div>

                <div class="fl-wrap-col fl-auto holiday-ml-4 holiday-mr-4">
                    <table id="myTable_itemMaster" class="display table table-striped table-hover font-s-2" style="width: 100%;">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Item Code</th>
                                <th>Name</th>
                                <th>Day Open</th>
                                <th>Time Close</th>
                                <th>Award Today</th>
                                <th>Manage</th>
                            </tr>
                        </thead>
                        <tbody>';
    foreach($data_item as $key => $val){
        $html_str .=        '<tr>
                                <td><i class="fa fa-cogs fabtn btn-edit-item" aria-hidden="true" data-lotid="'.$val["code"].'"></i></td>
                                <td>'.$val["code"].'</td>
                                <td>'.$val["name"].'</td>
                                <td>'.$val["date"].'</td>
                                <td>'.$val["time"].'</td>
                                <td>'.$val["award"].'</td>
                                <td><button id="manageDays" type="button" style="padding: 3px 4px 2px;" class="btn btn-info font-s-2"><i class="fa fa-cog" aria-hidden="true"></i> วันที่เปิด</button>
                                    <button id="manageDays" type="button" style="padding: 3px 4px 2px;" class="btn btn-info font-s-2"><i class="fa fa-clock" aria-hidden="true"></i> เวลปิด</button>
                                    <button data-itemcode="'.$val["code"].'" type="button" style="padding: 3px 4px 2px;" class="btn btn-success font-s-2 numberCorrect"><i class="fa fa-star" aria-hidden="true"></i> เลขที่ออก</button>
                                    <button id="deleteBtn" type="button" style="padding: 3px 4px 2px;" class="btn btn-default font-s-2">ลบ</button>
                                </td>
                            </tr>';
    }

    $html_str .=        '</tbody>
                    </table>
                </div>';

    echo $html_str;
?>

<script>
    $(document).ready(function(){
        $("#myTable_itemMaster").dataTable({
            data: null,
            columns: [
                { data: 0, width: "2%"},
                { data: 1, width: "10%"},
                { data: 2, width: "20%" },
                { data: 3 },
                { data: 4 },
                { data: 5 },
                { data: 6, width: "20%"}
            ],
            scrollY: "90%",
            scrollCollapse: true,
            paging: true
        });

        $(".numberCorrect").off("click");
        $(".numberCorrect").on("click", function(){
            var item_code_s = $(this).data("itemcode");
            var sUrl_appoint = "award_create.php?item_code="+item_code_s;
            showDialog(sUrl_appoint, "Manage Items Main", "280", "500", "", function(sResult){
                var url_reload = "manage_item_main.php";
                $("#detail_sub").load(url_reload);
            }, false, function(sResult){});
        });

        $("#addNewBtn").off("click");
        $("#addNewBtn").on("click", function(){
            var sUrl_appoint = "manage_item_create.php?item_code=";
            showDialog(sUrl_appoint, "Manage Items Main", "250", "500", "", function(sResult){
                var url_reload = "manage_item_main.php";
                $("#detail_sub").load(url_reload);
            }, false, function(sResult){});
        })

        $(".btn-edit-item").off("click");
        $(".btn-edit-item").on("click", function(){
            var item_code = $(this).data("lotid");
            var sUrl_appoint = "manage_item_create.php?item_code="+item_code;
            showDialog(sUrl_appoint, "Manage Items Main", "250", "500", "", function(sResult){
                var url_reload = "manage_item_main.php";
                $("#detail_sub").load(url_reload);
            }, false, function(sResult){});
        });
    });
</script>