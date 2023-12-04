<?
    include_once("in_session.php");
    include_once("in_php_function.php");
    $sOFind = urlDecode(getQS("term"));
    $sFind = "%".$sOFind."%";
    $isIncServ = getQS("incserv");
    $sShowAmt = getQS("showamt");
    $sClinicId = getSS("clinic_id");

    if($sFind=="%%"){}
    else{
        include_once("in_setting_row.php");
        include("in_db_conn.php");
        $query = "SELECT is_service,IST.supply_group_type,supply_group_name,ISM.supply_code,supply_name,supply_desc,supply_unit,bulk_unit,convert_amt ";
        if($sShowAmt=="1"){
            $query .= " ,stock_lot,stock_amt";
        }else{
            $query .= " ,'0','0'";
        }

        $sModIns = "";
        if(isset($_SESSION["MODULE"]["ORDER"])){
            foreach ($_SESSION["MODULE"]["ORDER"] as $sOptCode => $aMode) {
                if(isset($aMode["insert"])){
                    if($aMode["insert"]=="1"){
                        $sModIns .= (($sModIns=="")?"":",")."'".$sOptCode."'";
                    }
                }
            }
        }

        if($sShowAmt=="1"){
            $query.=",stock_exp_date FROM i_stock_master ISM
            LEFT JOIN i_stock_group ISG
            ON ISG.supply_group_code = ISM.supply_group_code 
            LEFT JOIN i_stock_type IST
            ON IST.supply_group_type = ISG.supply_group_type LEFT JOIN i_stock_list ISL
            ON ISL.clinic_id = '".$sClinicId."'
            AND ISL.supply_code=ISM.supply_code
            ";
        }else{
            $query .= ",'0000-00-00' AS stock_exp_date FROM i_stock_master ISM
            LEFT JOIN i_stock_group ISG
            ON ISG.supply_group_code = ISM.supply_group_code 
            LEFT JOIN i_stock_type IST
            ON IST.supply_group_type = ISG.supply_group_type";
        }

        $query .= " WHERE supply_status='1' AND (supply_name LIKE ? OR ISM.supply_code = ? OR ISG.supply_group_name LIKE ?)";
        //if($isIncServ!="1")	$query .= " AND IST.is_service=0";
        $query .= " AND IST.supply_group_type IN (".$sModIns.") 
        UNION
        SELECT '0', '1', 'Package items', package_stock_id, package_stock_name, 'Package_items_group', '', '', '0', '', '1', '0000-00-00'
        from i_stock_package where package_stock_name like ?
        ORDER BY
            supply_name,
            stock_exp_date;";

        // echo "Query: ".$query;
        // Add permission here in where cause

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssss",$sFind,$sOFind,$sFind, $sFind);

	    $sHtml = array();
	    if($stmt->execute()){
            $stmt->bind_result($is_service,$supply_group_type,$supply_group_name,$supply_code,$supply_name,$supply_desc,$supply_unit,$bulk_unit,$convert_amt,$stock_lot,$stock_amt,$stock_exp_date);

		while($stmt->fetch()){
			$sHtml[] = $supply_name;
		}
	}

	if(count($sHtml) == 0) 
        $sHtml[] = "ไม่พบข้อมูล";
	
	$mysqli->close();

	echo json_encode($sHtml);
}

?>