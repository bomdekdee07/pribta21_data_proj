<?
    include("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    if($data_id == null){
        $data_id = "";
    }

    $sopt = "<option value=''>-- Please Select --</option>";

    $query = "select section_id,
    section_name
    from p_staff_section;";

    $stmt = $mysqli -> prepare($query);
    // echo "sid:".$sSID."/".$data_result_staff."/".$data_id;

    if($stmt -> execute()){
        $stmt -> bind_result($section_id, $section_name);
        while($stmt -> fetch()){
            $sopt .= "<option value=".$section_id." data-id=".$data_id.">".$section_name."</option>";
        }
    }

    $stmt -> close();
    $mysqli -> close();

    echo $sopt;
?>