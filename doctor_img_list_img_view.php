<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $img_id = getQS("img_id");
    $uid = getQS("uid");
    $coldate = getQS("coldate");
    $coltime = getQS("coltime");
    $img_nameid = getQS("img_nameid");
    $path = getQS("path");
    $type_img = getQS("type_img");

    $bind_param = "sssss";
    $array_val = array($uid, $coldate, $coltime, $type_img, $img_nameid);
    // print_r($array_val);
    $img_id_all = array();

    $query = "SELECT img_id,
        type_file
    from img_uid_info
    where uid = ?
    and collect_date = ?
    and collect_time = ?
    AND type_img_id = ?
    and img_id != ?
    and `status` != '1'
    order by img_seq;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $img_id_all[$row["img_id"]]["id"] = $row["img_id"];
            $img_id_all[$row["img_id"]]["type_file"] = $row["type_file"];
        }
        // print_r($img_id_all);
    }
    $stmt->close();
    $mysqli->close();

    $img_view_str = "";
    $html_img = "";
    $html_img .= '<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="box">
                                <img id="pic" src="getImage.php?i=' . urlencode($img_id) . '" class="" alt="...">
                            </div>
                        </div>';
    foreach($img_id_all AS $key=>$val){
        $img_view_str = '//192.168.100.46/Clinic/img/'.$path."/".$val["id"].".".$val["type_file"];
        $html_img .= '<div class="carousel-item">
                            <img id="pic" src="getImage.php?i=' . urlencode($img_view_str) . '" class="" alt="...">
                        </div>';
    }

    $html_img .='</div>
                    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true" style="color:#92E192; background-color:#92E192;"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true" style="color:#92E192; background-color:#92E192;"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>';
    
    echo $html_img;
?>

<style>
    .box {
        text-align: center;
    }
    img {
        width: 1200px;
        height: 800px;
        object-fit: contain;
    }
</style>

<script>
    $(document).ready(function(){
        $('.carousel').carousel({
            interval: 100000
        });

        $('#pic').on('click', function(){
            if($(this).hasClass('zoomed')) {
            $(this).removeClass('zoomed')
            $(this).children('img').animate({width: "10%"}, 'slow');
            }
            $(this).addClass('zoomed')
            $(this).children('img').animate({width: "70%"}, 'slow');
        });
    });
</script>