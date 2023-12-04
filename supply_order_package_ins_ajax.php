<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $row_array = json_decode($_POST['data'], true);
    $clinic_id = getSS("clinic_id");
    $sSid = getSS("s_id");
    // echo $clinic_id;
    // print_r($row_array);
    
    $sum_array = array();
    $uid = "";
    $coldate = "";
    $coltime = "";
    foreach($row_array AS $key=>$val){
        $sum_array[$val["sup_code"]][$val["id"]] = $val["value"];
        $uid = $val["uid"];
        $coldate = $val["coldate"];
        $coltime = $val["coltime"];
    }
    // echo "rtn: ".$uid;
    // print_r($sum_array);

    $master_val = array();
    foreach($sum_array AS $key=>$id){
        // echo $key.":".$id["order_amt"]."/".$id["sale_opt_id"]."<br>";
        $bind_param = "ss";
        $array_val = array($key, $id["sale_opt_id"]);

        // query master items
        $query = "SELECT stmt.supply_desc,
            stmt.supply_unit,
            stmt.dose_before,
            stmt.dose_breakfast,
            stmt.dose_lunch,
            stmt.dose_dinner,
            stmt.dose_night,
            stmt.dose_note,
            sp.sale_price,
            st.is_service
        from i_stock_master stmt
        join i_stock_price sp on(sp.supply_code = stmt.supply_code)
        left join i_stock_group sg on(sg.supply_group_code = stmt.supply_group_code)
        left join i_stock_type st on(st.supply_group_type = sg.supply_group_type)
        where stmt.supply_code = ?
        AND sp.sale_opt_id = ?;";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($bind_param, ...$array_val);

        if($stmt->execute()){
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){
                $master_val[$key]["dose_day"] = $id["order_amt"];
                $master_val[$key]["sale_opt_id"] = $id["sale_opt_id"];
                $master_val[$key]["uid"] = $uid;
                $master_val[$key]["coldate"] = $coldate;
                $master_val[$key]["coltime"] = $coltime;

                $master_val[$key]["supply_desc"] = $row["supply_desc"];
                $master_val[$key]["supply_unit"] = $row["supply_unit"];
                $master_val[$key]["dose_before"] = $row["dose_before"];
                $master_val[$key]["dose_breakfast"] = $row["dose_breakfast"];
                $master_val[$key]["dose_lunch"] = $row["dose_lunch"];
                $master_val[$key]["dose_dinner"] = $row["dose_dinner"];
                $master_val[$key]["dose_night"] = $row["dose_night"];
                $master_val[$key]["dose_note"] = $row["dose_note"];
                $master_val[$key]["sale_price"] = $row["sale_price"];
                $master_val[$key]["is_service"] = $row["is_service"];
                $master_val[$key]["projid"] = "";
            }
        }
    }
    $stmt->close();
    // print_r($master_val);

    foreach($master_val AS $sup_code=>$val){
        // echo "IN TEST:".$sup_code."/".$val["is_service"]."<br>";
        // Query from P.POP custom by BOM 2023/07/24.
        $aStock = array();
        $sOCode = date("YmdHis");
        $sToday = date("Y-m-d");
        $isOk = false;

        if($val["is_service"]=="1"){
            //Service No need to check stock
            $aStock[0]["amt"] = 99999999999999;
            $aStock[0]["exp"] = $sToday;
            $aStock[0]["isamt"]=$val["dose_day"];
            $aStock[0]["done"]=true;
            $aStock[0]["price"]=$val["dose_day"]*$val["sale_price"];
            $aStock[0]["cost"] = 0;
            $isOk = true;
        }
        else{
            $query = "SELECT stock_lot,stock_amt,stock_exp_date,stock_cost FROM i_stock_list 
            WHERE clinic_id=? AND supply_code=? AND stock_amt > 0  AND (stock_exp_date >= ? || stock_exp_date ='0000-00-00') ORDER BY stock_exp_date ASC";
            $stmt=$mysqli->prepare($query);

            $stmt->bind_param("sss",$clinic_id, $sup_code, $sToday);
            if($stmt->execute()){
                $stmt->bind_result($stock_lot,$stock_amt,$stock_exp_date,$stock_cost);
                while($stmt->fetch()){
                    $aStock[$stock_lot]["amt"] = $stock_amt;
                    $aStock[$stock_lot]["exp"] = $stock_exp_date;
                    $aStock[$stock_lot]["cost"] = $stock_cost;
                    $isOk = true;
                }
                // print_r($aStock);
            }
            $stmt->close();
        }

        if($isOk){
			$iAmt = $val["dose_day"];
			$iLeft = $iAmt;
			foreach ($aStock as $sLot => $aT) {
				if($iLeft > 0){
					$iOrdAmt = $iLeft;
					if($aT["amt"]>=$iLeft){

					}
					else{
						$iOrdAmt = $aT["amt"];
					}
					$query = "UPDATE i_stock_list SET stock_amt=stock_amt-? WHERE clinic_id=? AND supply_code=? AND stock_lot=? AND stock_amt >= ?";
					$stmt=$mysqli->prepare($query);
					
					$stmt->bind_param("sssss",$iOrdAmt, $clinic_id, $sup_code, $sLot, $iOrdAmt);
					if($stmt->execute()){
						$iAffRow =$stmt->affected_rows;
						if($iAffRow > 0) {
							$aStock[$sLot]["isamt"]=$iOrdAmt;
							$aStock[$sLot]["done"]=true;
							$aStock[$sLot]["price"]=$iOrdAmt*$val["sale_price"];
							$iLeft = $iLeft - $iOrdAmt;
							$isOk = true;
						}else{
							//Not Success
						}
					}
                    $stmt->close();
				}
			}
		}
        // print_r($aStock);

        if($isOk){
            //Cut Stock at least one of stock was removed.
            foreach ($aStock as $sLot => $aT) {
                $isOk = false;
                
                if(isset($aT["done"])){
                    $val["dose_note"]=(isset($val["dose_note"])?$val["dose_note"]:"");
                    $val["supply_desc"]=(isset($val["supply_desc"])?$val["supply_desc"]:"");
                    $iCost= (intval($aT["cost"])*intval($aT["isamt"]));
                    // echo "cal: ".$iCost."/".$aT["cost"]."/".$aT["isamt"];

                    $bind_param = "ssssssssssssssssssss";
                    $array_val = array(
                        $sOCode,
                        $val["coldate"],
                        $val["coltime"],
                        $val["uid"],
                        $clinic_id,
                        $sup_code,
                        $sLot,
                        $val["dose_before"],
                        $val["dose_breakfast"],
                        $val["dose_lunch"],
                        $val["dose_dinner"],
                        $val["dose_night"],
                        $aT["isamt"],
                        $val["sale_price"],
                        $val["sale_opt_id"],
                        $val["dose_note"],
                        $val["supply_desc"],
                        $aT["price"],
                        $sSid,
                        $iCost
                    );

                    $query = "INSERT into i_stock_order(
                        order_code,
                        collect_date,
                        collect_time,
                        uid,
                        clinic_id,
                        supply_code,
                        supply_lot,
                        order_status,
                        dose_before,
                        dose_breakfast,
                        dose_lunch,
                        dose_dinner,
                        dose_night,
                        dose_day,
                        sale_price,
                        sale_opt_id,
                        order_note,
                        supply_desc,
                        total_price,
                        order_datetime,
                        order_by,
                        is_paid,
                        is_pickup,
                        total_cost) 
                    VALUES (?,?,?,?,?,?,?,'1',?,?,?,?,?,?,?,?,?,?,?,NOW(),?,'0','0',?);";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param($bind_param, ...$array_val);

                    if($stmt->execute()){
                        $isOk = true;
                    }
                }
            }
        }
    }
    
    $stmt->close();
    $mysqli->close();

    if($isOk){
        echo "1";
    }
    else{
        echo "0";
    }
?>