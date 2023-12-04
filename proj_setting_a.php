<?
include("in_session.php");
include_once("in_php_function.php");
include_once("in_php_encode.php");

$u_mode = getQS('u_mode');
$sProjid = getQS("projid");
$s_id = getSS("s_id");
$clinic_id = getSS("clinic_id");


$flag_auth=1;

$res = 0;
$msg_error = "";
$msg_info = "";
$returnData = "";



if($flag_auth != 0){ // valid user session

include_once("in_php_pop99.php");
include_once("in_php_pop99_sql.php");

//echo "umode : $u_mode";

if($u_mode == "select_list_protocol"){ // select list data
  $txtsearch = getQS('txtsearch');
	$txtsearch = "%$txtsearch%";
	$txtrow = ""; $row_amt = 0;

	$query ="SELECT group_id, protocol_id, protocol_version, protocol_note, start_date, stop_date
	FROM i_project_protocol
	WHERE proj_id =? AND CONCAT(protocol_id,',',protocol_note) LIKE ?
	ORDER BY protocol_version, protocol_id ";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ss',$sProjid,$txtsearch);
//error_log()"$sProjid,$txtsearch / $query";
	if($stmt->execute()){
		$stmt->bind_result($group_id, $protocol_id, $protocol_version, $protocol_note, $start_date, $stop_date);
		while($stmt->fetch()) {
			$txtrow .= addRowList_proj_protocol($protocol_id, $group_id, $protocol_note, $protocol_version,
		  $start_date, $stop_date);
			$row_amt++;
		}//while
	}
  $stmt->close();
	$rtn['txtrow'] = $txtrow;
	$rtn['row_amt'] = $row_amt;

}
else if($u_mode == "update_row_protocol"){
	$projid = getQS("projid");
//	$lst_data = getQS("lst_data");
	$lst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];
//	error_log(print_r($lst_data));
	$txtrow = "";

	$query = "INSERT INTO i_project_protocol (proj_id, group_id,
		protocol_id, protocol_note, protocol_version, start_date, stop_date) VALUES
		(?,?,?,?,?,?,?) ON Duplicate key
		UPDATE protocol_note=VALUES(protocol_note), protocol_version=VALUES(protocol_version), start_date=VALUES(start_date),stop_date=VALUES(stop_date);";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("sssssss",$projid, $lst_data['group_id'], $lst_data['protocol_id'],
		$lst_data['protocol_note'], $lst_data['protocol_version'],$lst_data['start_date'],$lst_data['stop_date']
	  );

		if($stmt->execute()){
			$affect_row = $stmt->affected_rows;
			$res = 1;
			$txtrow = addRowList_proj_protocol($lst_data['protocol_id'], $lst_data['group_id'], $lst_data['protocol_note'], $lst_data['protocol_version'],
		  $lst_data['start_date'],$lst_data['stop_date']);
		}
		else{
			error_log("proj_setting_a.php: ".$stmt->error);
		}

		$stmt->close();
		if($res) addToLog("update project protocol  [$projid|".$lst_data['group_id']."|".$lst_data['protocol_id']."]", $s_id);

		$rtn['txtrow'] = $txtrow;
		$rtn['res'] = $res;
	}

  else if($u_mode == "update_row_protocol_formid"){
  	$protocolid = getQS("protocolid");
  	$lst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];
  //	error_log(print_r($lst_data));
    $form_seq = 1;
  	$txtrow = "";

    $query = "INSERT INTO i_protocol_form (protocol_id, form_id, form_seq) VALUES
      (?,?,?) ON Duplicate key
      UPDATE form_seq=VALUES(form_seq)";

      foreach($lst_data as $form_id){

    		$stmt = $mysqli->prepare($query);
    		$stmt->bind_param("sss",$protocolid, $form_id, $form_seq);

    		if($stmt->execute()){
    			$affect_row = $stmt->affected_rows;
    			$res = 1;
    		}
    		else{
    			error_log("proj_setting_a.php: ".$stmt->error);
    		}

    		$stmt->close();
    		if($res) addToLog("update protocol formid  [$protocolid|$form_id]", $s_id);

    }
  		$rtn['res'] = $res;
  }
  else if($u_mode == "update_protocol_form_seq"){
    $protocolid = getQS("protocolid");
    $formid = getQS("formid");
    $formseq = getQS("formseq");


    $query = "UPDATE i_protocol_form SET form_seq=? WHERE protocol_id=? AND form_id=?";
    //error_log("$formseq, $protocolid, $formid / $query");
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss",$formseq, $protocolid, $formid );

        if($stmt->execute()){
          $affect_row = $stmt->affected_rows;
          $res = 1;
        }
        else{
          error_log("proj_setting_a.php: ".$stmt->error);
        }

        $stmt->close();
        $rtn['res'] = $res;
  }

  else if($u_mode == "update_protocol_form_visit"){
  	$protocolid = getQS("protocolid");
    $formid = getQS("formid");
  	$lst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];
  //	error_log(print_r($lst_data));
    $queryInsert = "INSERT INTO i_protocol_form_visit (protocol_id, form_id, visit_id, option_form, is_enable) VALUES
    (?,?,?,?,1) ON Duplicate key
    UPDATE option_form=VALUES(option_form)";

    $queryRemove = "DELETE FROM i_protocol_form_visit
    WHERE protocol_id=? AND form_id=? AND visit_id=?";

  	foreach($lst_data as $item_update){
      $arr_update = explode(":",$item_update); // 0:visitid, 1:is_add, 2:is_optional
      if($arr_update[1] == '1'){ // add form in visit
      //    echo "$protocolid,$formid, ".$arr_update[0]." / ".$arr_update[2]." / ".$queryInsert;
      		$stmt = $mysqli->prepare($queryInsert);
      		$stmt->bind_param("ssss",$protocolid,$formid, $arr_update[0], $arr_update[2]);
          if($stmt->execute()){
      			$affect_row = $stmt->affected_rows;
      			$res = 1;
            if($res) addToLog("Update form visit [$protocolid|$formid|".$arr_update[0]."]", $s_id);

      		}
          else{
            error_log("proj_setting_a.php: ".$stmt->error);
          }
      }
      else{ // remove form in visit
        $stmt = $mysqli->prepare($queryRemove);
        $stmt->bind_param("sss",$protocolid,$formid, $arr_update[0]);
        if($stmt->execute()){
          $affect_row = $stmt->affected_rows;
          $res = 1;
          if($res) addToLog("Remove form visit [$protocolid|$formid|".$arr_update[0]."]", $s_id);

        }
        else{
          error_log("proj_setting_a.php: ".$stmt->error);
        }
      }
    }//foreach


  		$stmt->close();
  		$rtn['res'] = $res;
  	}
    else if($u_mode == "remove_row_protocol_formid"){
    	$protocolid = getQS("protocolid");
      $formid = getQS("formid");
    //	error_log(print_r($lst_data));

      $query = "DELETE FROM i_protocol_form WHERE protocol_id=? AND form_id=?";
      $stmt = $mysqli->prepare($query);
      $stmt->bind_param("ss",$protocolid, $formid);
      if($stmt->execute()){
      			$affect_row = $stmt->affected_rows;
      			$res = 1;
      }
      else{
      			error_log("proj_setting_a.php: ".$stmt->error);
      }

      $query = "DELETE FROM i_protocol_form_visit WHERE protocol_id=? AND form_id=?";
      $stmt = $mysqli->prepare($query);
      $stmt->bind_param("ss",$protocolid, $formid);
      if($stmt->execute()){
            $affect_row = $stmt->affected_rows;
            $res = 1;
      }
      else{
            error_log("proj_setting_a.php: ".$stmt->error);
      }



      $stmt->close();
      if($res) addToLog("Remove protocol formid  [$protocolid|$formid]", $s_id);


    	$rtn['res'] = $res;
    }

  else if($u_mode == "select_list_visit"){ // select visit list data
    $txtsearch = getQS('txtsearch');
  	$txtsearch = "%$txtsearch%";
  	$txtrow = ""; $row_amt = 0;

  	$query ="SELECT group_id, visit_id, visit_name, visit_desc,
    visit_day, visit_day_before, visit_day_after, visit_order, visit_status
  	FROM p_visit_list
  	WHERE proj_id =? AND CONCAT(visit_id,',',visit_name) LIKE ?
  	ORDER BY visit_order ";

  //error_log("$sProjid,$txtsearch / $query");
  	$stmt = $mysqli->prepare($query);
  	$stmt->bind_param('ss',$sProjid,$txtsearch);

  	if($stmt->execute()){
  		$stmt->bind_result($group_id, $visit_id, $visit_name, $visit_desc,
      $visit_day, $visit_day_before, $visit_day_after, $visit_order, $visit_status);
  		while($stmt->fetch()) {
  			$txtrow .= addRowList_proj_visit($group_id, $visit_id, $visit_name, $visit_desc,
        $visit_day, $visit_day_before, $visit_day_after, $visit_order, $visit_status);
  			$row_amt++;
  		}//while
  	}
    $stmt->close();
  	$rtn['txtrow'] = $txtrow;
  	$rtn['row_amt'] = $row_amt;

  }

  else if($u_mode == "update_row_visit"){
  	$projid = getQS("projid");
  //	$lst_data = getQS("lst_data");
  	$lst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];
  //	error_log(print_r($lst_data));
  	$txtrow = "";

  	$query = "INSERT INTO p_visit_list (proj_id, group_id, visit_id, visit_name, visit_desc,
    visit_day, visit_day_before, visit_day_after, visit_order, visit_status) VALUES
  		(?,?,?,?,?,?,?,?,?,?) ON Duplicate key
  		UPDATE group_id=VALUES(group_id),visit_name=VALUES(visit_name),visit_desc=VALUES(visit_desc)
       ,visit_day=VALUES(visit_day),visit_day_after=VALUES(visit_day_after),visit_day_before=VALUES(visit_day_before)
       ,visit_order=VALUES(visit_order),visit_status=VALUES(visit_status)
      ";
  		$stmt = $mysqli->prepare($query);
  		$stmt->bind_param("ssssssssss",$projid, $lst_data['group_id'], $lst_data['visit_id'],$lst_data['visit_name'],$lst_data['visit_desc'],
      $lst_data['visit_day'], $lst_data['visit_day_before'],$lst_data['visit_day_after'],$lst_data['visit_order'],$lst_data['visit_status']
      );



  		if($stmt->execute()){
  			$affect_row = $stmt->affected_rows;
  			$res = 1;
  			$txtrow = addRowList_proj_visit($lst_data['group_id'], $lst_data['visit_id'],$lst_data['visit_name'],$lst_data['visit_desc'],
        $lst_data['visit_day'], $lst_data['visit_day_before'],$lst_data['visit_day_after'],$lst_data['visit_order'],$lst_data['visit_status']
        );
  		}
  		else{
  			error_log("proj_setting_a.php: ".$stmt->error);
  		}

  		$stmt->close();
  		if($res) addToLog("update project visit  [$projid|".$lst_data['visit_id']."|".$lst_data['group_id']."]", $s_id);

  		$rtn['txtrow'] = $txtrow;
  		$rtn['res'] = $res;
  	}

    else if($u_mode == "select_list_datafilter"){ // Data filter
      $txtsearch = getQS('txtsearch');
    	$txtsearch = "%$txtsearch%";
      $txtrow = ""; $row_amt = 0;



    	$query ="SELECT group_id, group_name, group_view_dataid, seq_no
    	FROM i_project_filter_list
    	WHERE proj_id =? AND CONCAT(group_id,',',group_name) LIKE ?
    	ORDER BY seq_no, group_id ";

    	$stmt = $mysqli->prepare($query);
    	$stmt->bind_param('ss',$sProjid,$txtsearch);
    //error_log()"$sProjid,$txtsearch / $query";
    	if($stmt->execute()){
    		$stmt->bind_result($group_id, $group_name, $group_view_dataid, $seq_no);
    		while($stmt->fetch()) {
    			$txtrow .= addRowList_proj_datafilter($group_id, $group_name, $group_view_dataid, $seq_no );
    			$row_amt++;
    		}//while


    	}
      else{
        $msg_error = "Fail to select";
      }
      $stmt->close();

    	$rtn['txtrow'] = $txtrow;
    	$rtn['row_amt'] = $row_amt;

    }
    else if($u_mode == "select_list_datafilter_group"){ // Data filter group
      $sProjid = getQS('projid');
      $sGroupid = getQS('groupid');
      $txtrow = "";

      $query ="SELECT item_group , item_group_type
      FROM i_project_filter_group
      WHERE proj_id =? AND group_id=?
      ORDER BY group_id ";

      $stmt = $mysqli->prepare($query);
      $stmt->bind_param('ss',$sProjid, $sGroupid);
      //error_log()"$sProjid,$txtsearch / $query";
      if($stmt->execute()){
      	$stmt->bind_result($item_group, $item_group_type);
      	while($stmt->fetch()) {
          $txtrow .= addRowList_datafilter_group($item_group, $item_group_type);
      	}//while
        $res = 1;
      }
      else{
        $msg_error = "Fail to select";
      }
      $stmt->close();

      $rtn['txtrow'] = $txtrow;

    }

    else if($u_mode == "select_list_datafilter_item"){ // item Data filter
      $sProjid = getQS('projid');
      $sGroupid = getQS('groupid');
      $sItemgroup = getQS('itemgroup');
      $txtrow = ""; $row_amt = 0;


      	$query ="SELECT item_group_no, data_id, data_equation, data_value
      	FROM i_project_filter_item
      	WHERE proj_id =? AND group_id=? AND item_group=?
      	ORDER BY item_group_no ";

      	$stmt = $mysqli->prepare($query);
      	$stmt->bind_param('sss',$sProjid,$sGroupid,$sItemgroup );
      //error_log()"$sProjid,$txtsearch / $query";
      	if($stmt->execute()){
      		$stmt->bind_result($item_group_no, $data_id, $data_equation, $data_value);
      		while($stmt->fetch()) {
            $res = 1;
            $txtrow .= addRowList_datafilter_item($item_group_no, $data_id, $data_equation, $data_value);
       		}//while
      	}
        $stmt->close();
    	$rtn['txtrow'] = $txtrow;

    }




    else if($u_mode == "update_row_datafilter"){
    	$projid = getQS("projid");
    //	$lst_data = getQS("lst_data");
    	$lst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];
    //	error_log(print_r($lst_data));
    	$txtrow = "";

    	$query = "INSERT INTO i_project_filter_list (proj_id, group_id,
    		group_name, seq_no, group_view_dataid) VALUES
    		(?,?,?,?,?) ON Duplicate key
    		UPDATE group_name=VALUES(group_name),seq_no=VALUES(seq_no),group_view_dataid=VALUES(group_view_dataid)";
    		$stmt = $mysqli->prepare($query);
    		$stmt->bind_param("sssss",$projid, $lst_data['group_id'], $lst_data['group_name'],
    		$lst_data['seq_no'], $lst_data['group_view_dataid']);

    		if($stmt->execute()){
    			$affect_row = $stmt->affected_rows;
    			$res = 1;
    			$txtrow = addRowList_proj_datafilter($lst_data['group_id'], $lst_data['group_name'], $lst_data['group_view_dataid'],
    		  $lst_data['seq_no'],"");

    		}
    		else{
    			error_log("proj_setting_a.php: ".$stmt->error);
    		}

    		$stmt->close();
    		if($res) addToLog("update project datafilter  [$projid|".$lst_data['group_id']."|".$lst_data['group_view_dataid']."]", $s_id);

    		$rtn['txtrow'] = $txtrow;
    		$rtn['res'] = $res;
    	}
      else if($u_mode == "update_row_datafilter_group"){
        $projid = getQS("projid");
        $groupid = getQS("groupid");
      //	$lst_data = getQS("lst_data");
        $lst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];
      //	error_log(print_r($lst_data));
        $txtrow = "";

        $query = "INSERT INTO i_project_filter_group (proj_id, group_id,
          item_group, item_group_type) VALUES
          (?,?,?,?) ON Duplicate key
          UPDATE item_group=VALUES(item_group),item_group_type=VALUES(item_group_type)";


          $stmt = $mysqli->prepare($query);
          $stmt->bind_param("ssss",$projid, $groupid, $lst_data['item_group'], $lst_data['item_group_type']);

          if($stmt->execute()){
            $affect_row = $stmt->affected_rows;
            $res = 1;
            $txtrow = addRowList_datafilter_group($lst_data['item_group'], $lst_data['item_group_type']);

          }
          else{
            error_log("proj_setting_a.php: ".$stmt->error);
          }

          $stmt->close();
          if($res) addToLog("update filter item group  [$projid|$groupid|".$lst_data['item_group']."|".$lst_data['item_group_type']."]", $s_id);

          $rtn['txtrow'] = $txtrow;
          $rtn['res'] = $res;
        }

        else if($u_mode == "update_row_datafilter_item"){
          $projid = getQS("projid");
          $groupid = getQS("groupid");
          $itemgroup = getQS("itemgroup");
        //	$lst_data = getQS("lst_data");
          $lst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];
        //	error_log(print_r($lst_data));

          if($lst_data['item_group_no'] == 'ADD'){

            	$query ="SELECT item_group_no
            	FROM i_project_filter_item
            	WHERE proj_id =? AND group_id=? AND item_group=?
            	ORDER BY item_group_no desc LIMIT 1";
            //  error_log("$projid,$groupid,$itemgroup / $query");
            	$stmt = $mysqli->prepare($query);
            	$stmt->bind_param('sss',$projid,$groupid,$itemgroup );
            //error_log()"$sProjid,$txtsearch / $query";
            	if($stmt->execute()){
            		$stmt->bind_result($item_group_no);
            		if($stmt->fetch()) {
             		}//while
            	}
              $stmt->close();

              if(is_null($item_group_no)){
                $item_group_no = 1;
              }
              else $item_group_no += 1;

              $lst_data['item_group_no'] = $item_group_no;
          }


          $txtrow = "";

          $query = "INSERT INTO i_project_filter_item (proj_id, group_id,item_group,
            item_group_no, data_id, data_equation, data_value) VALUES
            (?,?,?,?,?,?,?) ON Duplicate key
            UPDATE data_id=VALUES(data_id),data_equation=VALUES(data_equation), data_value=VALUES(data_value)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sssssss",$projid, $groupid, $itemgroup,
            $lst_data['item_group_no'], $lst_data['data_id'], $lst_data['data_equation'], $lst_data['data_value']);

            if($stmt->execute()){
              $affect_row = $stmt->affected_rows;
              $res = 1;
              $txtrow = addRowList_datafilter_item($lst_data['item_group_no'], $lst_data['data_id'], $lst_data['data_equation'], $lst_data['data_value']);

            }
            else{
              error_log("proj_setting_a.php: ".$stmt->error);
            }

            $stmt->close();
            if($res) addToLog("update i_project_filter_item  [$projid|$groupid|$itemgroup|".$lst_data['item_group_no']."|".$lst_data['data_id']."|".$lst_data['data_equation']."|".$lst_data['data_value']."]", $s_id);

            $rtn['txtrow'] = $txtrow;
            $rtn['item_group_no'] = $lst_data['item_group_no'];
            $rtn['res'] = $res;
      }
else if($u_mode == "remove_row_datafilter"){
      $projid = getQS("projid");
      $groupid = getQS("groupid");
      $arr_del = array('i_project_filter_list','i_project_filter_group', 'i_project_filter_item');
      foreach($arr_del as $table_del){
        $query = "DELETE FROM $table_del
        WHERE proj_id=? AND group_id=?";

          $stmt = $mysqli->prepare($query);
          $stmt->bind_param("ss",$projid, $groupid);

          if($stmt->execute()){
            $affect_row = $stmt->affected_rows;
            if($affect_row > 0){
              addToLog("remove [$table_del] $affect_row rows [$projid|$groupid", $s_id);
              $res = 1;
            }
          }
          else{
            error_log("proj_setting_a.php: ".$stmt->error);
          }

          $stmt->close();
      }//foreach

      $rtn['res'] = $res;
  }
      else if($u_mode == "remove_row_datafilter_group"){
            $projid = getQS("projid");
            $groupid = getQS("groupid");
            $itemgroup = getQS("itemgroup");

            $arr_del = array('i_project_filter_group', 'i_project_filter_item');
            foreach($arr_del as $table_del){
              $query = "DELETE FROM $table_del
              WHERE proj_id=? AND group_id=? AND item_group=?";

                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("sss",$projid, $groupid, $itemgroup);

                if($stmt->execute()){
                  $affect_row = $stmt->affected_rows;
                  if($affect_row > 0){
                    addToLog("remove [$table_del] $affect_row rows [$projid|$groupid", $s_id);
                    $res = 1;
                  }
                }
                else{
                  error_log("proj_setting_a.php: ".$stmt->error);
                }

                $stmt->close();
            }//foreach

            $rtn['res'] = $res;
        }
        else if($u_mode == "remove_row_datafilter_group_item"){
              $projid = getQS("projid");
              $groupid = getQS("groupid");
              $itemgroup = getQS("itemgroup");
              $itemgroupno = getQS("itemgroupno");
              $txtrow = "";

              $query = "DELETE FROM i_project_filter_item
              WHERE proj_id=? AND group_id=? AND item_group=? AND item_group_no=?";

                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("ssss",$projid, $groupid, $itemgroup, $itemgroupno);

                if($stmt->execute()){
                  $affect_row = $stmt->affected_rows;
                  $res = 1;

                }
                else{
                  error_log("proj_setting_a.php: ".$stmt->error);
                }

                $stmt->close();
                if($res) addToLog("remove i_project_filter_item  [$projid|$groupid|$itemgroup|$itemgroupno", $s_id);

                $rtn['res'] = $res;
          }


        else if($u_mode == "select_list_proj_group"){ // select proj group
                $sProjid = getQS('projid');
                $txtrow = ""; $row_amt = 0;
                	$query ="SELECT proj_id,proj_group_id,proj_group_name,proj_group_remark,proj_group_seq,is_disable
                	FROM p_project_group
                	WHERE proj_id =?
                	ORDER BY proj_group_seq ";

                	$stmt = $mysqli->prepare($query);
                	$stmt->bind_param('s',$sProjid);
                //error_log()"$sProjid,$txtsearch / $query";
                	if($stmt->execute()){
                		$stmt->bind_result($proj_id, $proj_group_id, $proj_group_name, $proj_group_remark, $proj_group_seq, $is_disable);
                		while($stmt->fetch()) {
                      $res = 1;
                      $txtrow .= addRowList_proj_group($proj_group_id, $proj_group_name, $proj_group_remark, $proj_group_seq, $is_disable);
                 		}//while
                	}
                  $stmt->close();
              	$rtn['txtrow'] = $txtrow;

          }

          else if($u_mode == "update_row_proj_group"){
            $projid = getQS("projid");
          //	$lst_data = getQS("lst_data");
            $lst_data = isset($_POST["lst_data"])?$_POST["lst_data"]:[];

            $txtrow = "";

            $query = "INSERT INTO p_project_group (proj_id,proj_group_id,proj_group_name,proj_group_remark,proj_group_seq,is_disable) VALUES
              (?,?,?,?,?,?) ON Duplicate key
              UPDATE proj_group_name=VALUES(proj_group_name),proj_group_remark=VALUES(proj_group_remark),proj_group_seq=VALUES(proj_group_seq)
               ,is_disable=VALUES(is_disable)";
              $stmt = $mysqli->prepare($query);
              $stmt->bind_param("ssssss",$projid, $lst_data['proj_group_id'], $lst_data['proj_group_name'],$lst_data['proj_group_remark'],$lst_data['proj_group_seq'],$lst_data['is_disable']);

              if($stmt->execute()){
                $affect_row = $stmt->affected_rows;
                $res = 1;
                $txtrow = addRowList_proj_group($lst_data['proj_group_id'], $lst_data['proj_group_name'],$lst_data['proj_group_remark'],$lst_data['proj_group_seq'],$lst_data['is_disable']);
              }
              else{
                error_log("proj_setting_a.php: ".$stmt->error);
              }

              $stmt->close();
              if($res) addToLog("update project group  [$projid|".$lst_data['proj_group_id']."]", $s_id);

              $rtn['txtrow'] = $txtrow;
              $rtn['res'] = $res;
            }

$mysqli->close();

}//$flag_auth != 0

 // return object
 $rtn['res'] = $res;
 $rtn['mode'] = $u_mode;
 $rtn['msg_error'] = $msg_error;
 $rtn['msg_info'] = $msg_info;

 $rtn['flag_auth'] = $flag_auth;



 // change to javascript readable form
 $returnData = json_encode($rtn);
 echo $returnData;


 function addRowList_proj_protocol($protocol_id, $group_id, $protocol_note, $protocol_version,
 $start_date, $stop_date){

	 $txtrow = "
				<div class='fl-wrap-row ph50 p-row-green ptxt-s10 div-setting-row ptxt-b' data-id='$protocol_id' >
				 <div class='fl-fix fl-mid pw50 pbtn btn-setting-edit ' alt='edit'><i class='fa fa-edit fa-2x'></i></div>
					<div class='fl-fix fl-mid pw200 s-group_id' >$group_id</div>
				 <div class='fl-fix fl-mid pw200 al-left s-protocol_id ' >$protocol_id</div>
				 <div class='fl-fix fl-mid pw100 s-protocol_version' >$protocol_version</div>
				 <div class='fl-fill fl-mid al-left s-protocol_note'>$protocol_note</div>
				 <div class='fl-fix fl-mid pw80 s-start_date' >$start_date</div>
				 <div class='fl-fix fl-mid pw80 s-stop_date' >$stop_date</div>
				 <div class='fl-fix fl-mid pw80 btn-view-form pbtn '> <i class='fas fa-file-invoice fa-lg mx-2'></i> Forms</div>
			 </div>
	 ";

	 return $txtrow;
 }


 function addRowList_proj_visit( $group_id, $visit_id, $visit_name, $visit_desc,
 $visit_day, $visit_day_before, $visit_day_after, $visit_order, $visit_status){

  $txtrow = "
       <div class='fl-wrap-row ph50 p-row-green p-row ptxt-s10 div-setting-row ptxt-b' data-id='$visit_id' >
        <div class='fl-fix fl-mid pw50 pbtn btn-setting-edit ' alt='edit'><i class='fa fa-edit fa-2x'></i></div>
         <div class='fl-fix fl-mid pw100 s-group_id' >$group_id</div>
        <div class='fl-fix fl-mid pw200 al-left s-visit_id ' >$visit_id</div>
        <div class='fl-fix fl-mid pw200 s-visit_name' >$visit_name</div>
        <div class='fl-fill fl-mid al-left s-visit_desc'>$visit_desc</div>
        <div class='fl-fix fl-mid pw80 s-visit_day' >$visit_day</div>
        <div class='fl-fix fl-mid pw80 s-visit_day_before' >$visit_day_before</div>
        <div class='fl-fix fl-mid pw80 s-visit_day_after' >$visit_day_after</div>
        <div class='fl-fix fl-mid pw80 s-visit_order' >$visit_order</div>
        <div class='fl-fix fl-mid pw80 s-visit_status' >$visit_status</div>
      </div>
  ";

  return $txtrow;
 }
 function addRowList_proj_datafilter($group_id, $group_name, $group_view_dataid, $seq_no){
	 $txtrow = "
				<div class='fl-wrap-row ph50 p-row-green ptxt-s10 div-setting-row ptxt-b' data-id='$group_id' >
				 <div class='fl-fix fl-mid pw50 pbtn btn-setting-edit ' alt='edit'><i class='fa fa-edit fa-2x '></i></div>
					<div class='fl-fix fl-mid pw200 s-group_id' >$group_id</div>
				 <div class='fl-fix fl-mid pw200 al-left s-group_name ' >$group_name</div>
         <div class='fl-fix fl-mid pw100 al-left s-seq_no'>$seq_no</div>
				 <div class='fl-fill fl-mid  fl-auto s-group_view_dataid' >$group_view_dataid</div>
         <div class='fl-fix fl-mid pw100 pbtn btn-view-filter'> View Filter </div>
         <div class='fl-fix fl-mid pw50'>
           <i class='fa fa-times fa-lg pbtn btn-delete-filter pbg-red ptxt-white' title='Remove Filter'></i>
           <i class='fa fa-spinner fa-spin spinner fa-lg' style='display:none;'></i>
         </div>

			 </div>

	 ";

	 return $txtrow;
 }

  function addRowList_datafilter_group($item_group, $item_group_type){
   $txtrow = "
        <div class='fl-wrap-row ph20 p-row-green ptxt-s10 div-filter-group'>
          <div class='fl-fix fl-mid pw50 pbg-red ptxt-white pbtn btn-remove-filter-group' ><i class='fa fa-times fa-lg ' title='Remove Filter'></i></div>
          <div class='fl-fix fl-mid pw200 item_group'>$item_group</div>
          <div class='fl-fix fl-mid pw200 item_group_type'>$item_group_type</div>
          <div class='fl-fix fl-mid pw80 pbtn btn-view-filter-group '><i class='fa fa-filter fa-lg ' title='View Filter Rule'></i> Filter Rule </div>
       </div>
   ";

   return $txtrow;
  }

 function addRowList_datafilter_item($item_group_no, $data_id, $data_equation, $data_value){
  $txtrow = "
       <div class='fl-wrap-row ph20 p-row-green ptxt-s10 div-item-filter ptxt-b'
       data-item_group_no='$item_group_no' >
        <div class='fl-fix fl-mid pw50 pbg-blue ptxt-white pbtn btn-edit-filter-item' ><i class='fa fa-edit fa-lg ' title='Edit Filter Item'></i></div>
        <div class='fl-fix fl-mid pw50 s-item_group_no'>$item_group_no</div>
        <div class='fl-fix fl-mid pw200 s-data_id'>$data_id</div>
        <div class='fl-fix fl-mid pw200 s-data_equation' >$data_equation</div>
        <div class='fl-fix fl-mid pw200 s-data_value' >$data_value</div>
        <div class='fl-fix fl-mid pw50 pbg-red ptxt-white pbtn btn-remove-filter-item' title='Remove item filter No. $item_group_no'><i class='fa fa-times fa-lg ' title='Remove Filter Item'></i></div>
        <div class='fl-fix fl-mid pw50 spinner' style='display:none;'></div>
      </div>
  ";

  return $txtrow;
 }

  function addRowList_proj_group( $proj_group_id, $proj_group_name, $proj_group_remark, $proj_group_seq,$is_disable){
   $txtrow = "
        <div class='fl-wrap-row ph50 p-row-green p-row ptxt-s10 div-setting-row ptxt-b' data-id='$proj_group_id' >
         <div class='fl-fix fl-mid pw50 pbtn btn-setting-edit ' alt='edit'><i class='fa fa-edit fa-2x'></i></div>
          <div class='fl-fix fl-mid pw100 s-proj_group_id' >$proj_group_id</div>
         <div class='fl-fix fl-mid pw200 al-left s-proj_group_name ' >$proj_group_name</div>
         <div class='fl-fill fl-mid al-left s-proj_group_remark' >$proj_group_remark</div>
         <div class='fl-fix fl-mid pw100  s-proj_group_seq'>$proj_group_seq</div>
         <div class='fl-fix fl-mid pw80 s-is_disable' >$is_disable</div>
       </div>
   ";

   return $txtrow;
  }
