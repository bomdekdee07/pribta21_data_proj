<?
  $query ="SELECT IMP.section_id,
  IMP.module_id, IMP.option_code,
  IMP.allow_view, IMP.allow_insert, IMP.allow_update, IMP.allow_delete, IMP.is_admin
  FROM i_staff_clinic ISC
  JOIN i_module_permission IMP ON IMP.section_id=ISC.section_id
  WHERE ISC.s_id =? AND ISC.clinic_id =?
  ORDER BY IMP.section_id ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ss',$sID, $sClinicID);

  if($stmt->execute()){
   $stmt->bind_result($section_id,$module_id, $option_code,
   $allow_view, $allow_insert, $allow_update, $allow_delete, $is_admin);
   while($stmt->fetch()) {
    if(!isset($module_auth["$section_id-$module_id"])){
     $module_auth["$section_id:$module_id:$option_code"] = array();
    }
    $module_auth["$section_id:$module_id:$option_code"]["allow_view"] = $allow_view;
    $module_auth["$section_id:$module_id:$option_code"]["allow_insert"] = $allow_insert;
    $module_auth["$section_id:$module_id:$option_code"]["allow_update"] = $allow_update;
    $module_auth["$section_id:$module_id:$option_code"]["allow_delete"] = $allow_delete;
    $module_auth["$section_id:$module_id:$option_code"]["is_admin"] = $is_admin;
   }
  }
  else{
   error_log($stmt->error);
  }
  $stmt->close();
?>