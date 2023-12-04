<?
    include("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");
    // echo "Test:".$$data_result_staff; //GET from main include.

    $sopt = "<option value=''>-- Not found! --</option>";

    $query = "select distinct supply_group_type
    from i_stock_type order by supply_group_type;;";

    $stmt = $mysqli -> prepare($query);

    if($stmt -> execute()){
        $stmt -> bind_result($supply_group_type);
        while($stmt -> fetch()){
            $sopt .= "<option value=".$supply_group_type.">".$supply_group_type."</option>";
        }
    }
    $stmt -> close();
    $mysqli -> close();

    
    echo $sopt;
?>