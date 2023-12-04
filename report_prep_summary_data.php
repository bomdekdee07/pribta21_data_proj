<?
    include("in_session.php");
    include_once("in_php_function.php");
    include("in_db_conn.php");

    $sToday = date("Y-m-d");
    $sVisitDate = getQS("vdate",$sToday);

    // PREP
    $bind_param = "s";
    $array_val = array($sVisitDate);
    $data_prep_html = array();

    $query = "SELECT main.uid,
        main.collect_date,
        main.collect_time,
        project.data_result AS project_result,
        name_project.data_name_th AS name_project,
        prep_first.data_result AS prep_first_result,
        name_prep_first.data_name_th AS name_prep_first,
        clinic.data_result AS service_clinic
    from p_data_result main
    left join p_data_result project on(project.uid = main.uid and project.collect_date = main.collect_date and project.collect_time = main.collect_time and project.data_id = 'prep_project')
    left join p_data_sub_list name_project on(name_project.data_id = project.data_id and name_project.data_value = project.data_result)
    left join p_data_result prep_first on(prep_first.uid = main.uid and prep_first.collect_date = main.collect_date and prep_first.collect_time = main.collect_time and prep_first.data_id = 'prep_first')
    left join p_data_sub_list name_prep_first on(name_prep_first.data_id = prep_first.data_id and name_prep_first.data_value = prep_first.data_result)
    join p_data_result clinic on(clinic.uid = main.uid and clinic.collect_date = main.collect_date and clinic.collect_time = main.collect_time and clinic.data_id = 'service_clinic')
    where main.collect_date = ?
    and main.data_id = 'serv_coun_prep'
    and main.data_result = '1';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_prep_html[$row["uid"]] = $row;
        }
    }
    $stmt->close();

    // nPEP
    $data_npep_html = array();

    $query = "SELECT main.uid,
        main.collect_date,
        main.collect_time,
        project.data_result AS project_result,
        name_project.data_name_th AS name_project,
        clinic.data_result AS service_clinic
    from p_data_result main
    left join p_data_result project on(project.uid = main.uid and project.collect_date = main.collect_date and project.collect_time = main.collect_time and project.data_id = 'pep_project')
    left join p_data_sub_list name_project on(name_project.data_id = project.data_id and name_project.data_value = project.data_result)
    join p_data_result clinic on(clinic.uid = main.uid and clinic.collect_date = main.collect_date and clinic.collect_time = main.collect_time and clinic.data_id = 'service_clinic')
    where main.collect_date = ?
    and main.data_id = 'serv_coun_pep'
    and main.data_result = '1';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_npep_html[$row["uid"]] = $row;
        }
    }
    $stmt->close();

    // ARV
    $data_arv_html = array();

    $query = "SELECT main.uid,
        main.collect_date,
        main.collect_time,
        project.data_result AS project_result,
        clinic.data_result AS service_clinic
    from p_data_result main
    left join p_data_result project on(project.uid = main.uid and project.collect_date = main.collect_date and project.collect_time = main.collect_time and project.data_id = 'serv_coun_nhso')
    join p_data_result clinic on(clinic.uid = main.uid and clinic.collect_date = main.collect_date and clinic.collect_time = main.collect_time and clinic.data_id = 'service_clinic')
    where main.collect_date = ?
    and main.data_id = 'serv_coun_art'
    and main.data_result = '1';";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_arv_html[$row["uid"]] = $row;
        }
    }
    $stmt->close();
    $mysqli->close();

    $sHtml = "";
    $prep_count = "";
    $pep_count = "";
    $arv_count = "";
    $sTP = ""; $sTT = ""; $sTSum = "";
    // prep
    $prep_array_paid_fixs = array("1"=>"KP-led PrEP", "2"=>"Paid PrEP", "3"=>"Other", "4"=>"Global Fund", "5"=>"NHSO");
    $prep_array_eat_fixs = array("N"=>"PrEP new", "RE"=>"PrEP restart", "Y"=>"[PrEP F/U]");
    // npep
    $npep_array_paid_fixs = array("1"=>"Paid PrEP", "2"=>"NHSO");

    $sTemp="<div class='row-color lh-25 row-hover'>
                <div class='fl-wrap-row'>
                    <div class='fl-fill'>";

    // html prep
    foreach($data_prep_html as $key_uid => $result){
        if($result["service_clinic"] == "1" || $result["service_clinic"] == "2"){
            $sTP .= $sTemp."[".$result["uid"]."]/ PrEP/ ".$prep_array_paid_fixs[$result["project_result"]]."/ ".$prep_array_eat_fixs[$result["prep_first_result"]]."</div></div></div>";
            $prep_count++;
        }
    }

    // html npep
    foreach($data_npep_html as $key_uid => $result){
        if($result["service_clinic"] == "1" || $result["service_clinic"] == "2"){
            $sTT .= $sTemp."[".$result["uid"]."]/ nPEP/ ".$npep_array_paid_fixs[$result["project_result"]]."</div></div></div>";
            $pep_count++;
        }
    }

    // html arv
    foreach($data_arv_html as $key_uid => $result){
        if($result["service_clinic"] == "1" || $result["service_clinic"] == "2"){
            $sTSum .= $sTemp."[".$result["uid"]."]/ ARV".($result["project_result"]=="1"? "/ NHSO": ($result["project_result"]=="0"? "/ NHSO": ""))."</div></div></div>";
            $arv_count++;
        }
    }

    $selDate = date_create($sVisitDate);
    $showDate = date_format($selDate,"d/m/Y");

    $sHtml="<div class='fl-fix h-25'></div>
            <div class='fl-wrap-row h-25'><div class='fl-fill w-200'>PrEP : $prep_count ราย</div></div>
            <div class='fl-wrap-row h-25'><div class='fl-fill w-200'>nPEP : $pep_count ราย</div></div>
            <div class='fl-wrap-row h-25'><div class='fl-fill w-200'>ARV : $arv_count ราย</div></div>
            <div class='fl-wrap-row h-25'><div class='fl-fill w-200 fw-b'>Total : ".($prep_count+$pep_count+$arv_count)." ราย</div></div>";
?>

<div class='fl-wrap-col fl-auto fs-smaller'>
	<div class='fl-wrap-row'>
		<div class='fl-wrap-col' style='border-right: 2px solid white'>
			<div class='fl-fix h-30 fl-mid bg-head-1'>PrEP</div>
			<div class='fl-fix h-30 fl-mid row-color-2'><? echo("<div class='fl-wrap-row h-30'><div class='fl-fill'>วันที่ $showDate จำนวน ".($prep_count)." ราย</div></div>"); ?></div>
			<div class='fl-wrap-col fl-auto'><? echo($sTP); ?></div>
			
		</div>
		<div class='fl-wrap-col' style='border-right: 2px solid white'>
			<div class='fl-fix h-30 fl-mid bg-head-1'>nPEP</div>
			<div class='fl-fix h-30 fl-mid row-color-2'><? echo("<div class='fl-wrap-row h-30'><div class='fl-fill'>วันที่ $showDate จำนวน ".($pep_count)." ราย</div></div>"); ?></div>
			<div class='fl-wrap-col fl-auto'><? echo($sTT); ?></div>
			
		</div>
		<div class='fl-wrap-col' style='border-right: 2px solid white'>
			<div class='fl-fix h-30 fl-mid bg-head-1'>ARV</div>
			<div class='fl-fix h-30 fl-mid row-color-2'><? echo("<div class='fl-wrap-row h-30'><div class='fl-fill'>วันที่ $showDate จำนวน ".($arv_count)." ราย</div></div>"); ?></div>
			<div class='fl-wrap-col fl-auto'><? echo($sTSum); ?></div>
			
		</div>
	</div>
	<div class='fl-wrap-col h-180 fl-auto'>
		<? echo($sHtml);	?>
	</div>
</div>