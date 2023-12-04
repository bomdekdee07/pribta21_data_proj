<?
  include("in_session.php");
  include("in_db_conn.php");
  include_once("in_php_function.php");

  $sUid = getQS("uid");
  $sOpt = "";

  $query ="SELECT UL.pid,  UL.proj_id, P.proj_name
  FROM p_project_uid_list UL
  JOIN p_project P ON P.proj_id=UL.proj_id
  WHERE UL.uid =?  AND UL.uid_status != 10
  UNION
  select '', 'PURPOSE2', 'PURPOSE2'
  from p_data_result
  where uid = ?
  and data_id = 'cn_patient_note' 
  and data_result like '%purpo%'";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ss',$sUid, $sUid);
  //echo "$sID, $sProjid / $query";
  if($stmt->execute()){
    $stmt->bind_result($proj_pid, $proj_id, $proj_name);
    while($stmt->fetch()) {
      $sOpt.="<option value='$proj_id' data-pid='$proj_pid'> $proj_name [PID:$proj_pid]</option>";
    }
  }
  $stmt->close();

  if($proj_id != ""){
    $bind_param = "s";
    $array_val = array($proj_id);
    $html_master_visit_tp = "";

    $query = "SELECT visit_id,
      timepoint_name,
      timepoint_desc,
      seq_no,
      package_id
    from p_project_visit_timepoint
    where proj_id = ?
    ORDER BY seq_no;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($bind_param, ...$array_val);

    if($stmt->execute()){
      $result = $stmt->get_result();
      while($row = $result->fetch_assoc()){
        $html_master_visit_tp .= "<option value='".$row["visit_id"]."' data-seq='".$row["seq_no"]."' data-desc='".$row["timepoint_desc"]."' data-package='".$row["package_id"]."'>".$row["timepoint_name"]."</option>";
      }
    }
    $stmt->close();
  }
?>



<div class='fl-wrap-col fl-fill div_lab_visit_timepoint' data-uid='<? echo $sUid; ?>'>
  <div class='fl-wrap-row fl-fix px-2 ph30 ptxt-b ptxt-s12 bg-mdark1 ptxt-white'>
    <div class="fl-fix w-200"></div>
    <div class="fl-fill fl-mid">
      <i class="fa fa-flask fa-lg px-2" ></i> Project Lab Order
    </div>
    <div class="fl-fix w-200 fl-mid-right">
      <button class="btn btn-success font-s-1 fw-b" id="btnAddExtraVisitProject" style="padding: 1px 10px 1px 10px; display: none;">เพิ่ม Extra Visit</button>
      <i class='fa fa-spinner fa-spin fa-lg spinner' style='display:none;'></i>
    </div>
  </div>
  <div class='fl-wrap-row fl-fix fl-mid px-2 ph30 ptxt-s10 bg-msoft3'>
    <div class='fl-fill'></div>
    <div class='fl-fix pw150 ptxt-b ptxt-s12 txtproj-uid'>

    </div>
    <div class='fl-fix fl-mid pw200 ptxt-b ptxt-s12 txtproj-pid-info'>
      - Please select project to view -
    </div>
    <div class="fl-fix w-100 hide-purpose2">
      <input type="text" name="pid_custom_txt" class="input-group" style="height: 98%; width: 99px; text-align:center">
    </div>
    <div class="fl-fix w-20 hide-purpose2"></div>
    <div class='fl-fix pw300 fw-b'>
      <?
        if($sOpt == ""){
          echo "No project register.";
        }
        else {
          echo "
          Proj:
          <select id='sel_proj_id'>
            <option value='' selected disabled> - Please select -</option>
            $sOpt
          </select>
          ";
        }
      ?>
    </div>
    <div class="fl-fix w-5"></div>

    <!-- add PURPOSE2 -->
    <div class="fl-fix w-110 fw-b hide-purpose2">Add Duplicate Visit:</div>
    <div class="fl-fix w-180 hide-purpose2">
      <select id="master_visit_tp" name="master_visit_tp" style="width: 99%;">
        <? echo $html_master_visit_tp; ?>
      </select>
    </div>
    <div class="fl-fix w-10 hide-purpose2">
      <button class="btn btn-success" name="bt_add_master_visit_tp" title="Add" style="padding: 0px 3px 0px 3px;"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
    </div>
    <div class="fl-fix w-50 hide-purpose2"></div>

    <div class="fl-fix w-70 fw-b hide-purpose2">Add visit:</div>
    <div class="fl-fix w-120 hide-purpose2">
      <select id="sel_type_head" name="sel_type_head">
        <option value='S' selected>Incidence Screening</option>
        <option value='R'>Random Screening</option>
      </select>
    </div>
    <div class="fl-fix w-10 hide-purpose2"></div>
    <div class="fl-fix w-10 fw-b hide-purpose2">+</div>
    <div class="fl-fix w-40 hide-purpose2"><input type="text" name="week_add" class="input-group" style="height: 98%; width: 39px; text-align:center" readonly value="13"></div>
    <div class="fl-fix w-5 hide-purpose2"></div>
    <div class="fl-fix w-30 hide-purpose2">Week.</div>
    <div class="fl-fix w-5 hide-purpose2"></div>
    <div class="fl-fix w-10 hide-purpose2">
      <button class="btn btn-success" name="bt_add_visit_tp" title="Add" style="padding: 0px 3px 0px 3px;" disabled><i class="fa fa-plus-square" aria-hidden="true"></i></button>
    </div>
    <!-- add PURPOSE2 -->

    <div class='fl-fill'></div>
  </div>

  <div class='fl-wrap-row fl-fix fl-mid ph30 ptxt-s10 ptxt-b ptxt-white bg-mdark3'>
    <div class='fl-fill'></div>
      <div class='fl-fix pw150'>Visit ID</div>
      <div class='fl-fix pw100'>Date</div>
      <div class='fl-fix pw100'>Time</div>
      <div class='fl-fix pw100'>Timepoint</div>
      <div class='fl-fix pw100'>Lab Order ID</div>
      <div class='fl-fix pw100'>Lab Order Note</div>
      <div class='fl-fill'>View Lab Order / Lab Result</div>
      <div class='fl-fix w-150'>Status Lab Order</div>
    <div class='fl-fill'></div>
  </div>
  <div class='fl-wrap-row fl-fill ptxt-s10  bg-msoft1 '>
    <div class='fl-wrap-col fl-auto div_lab_visit_timepoint_detail'></div>
  </div>
  <div class='fl-wrap-row fl-mid fl-fill ptxt-s10 ptxt-white bg-msoft1 spinner' style='display:none;'>
    <i class='fa fa-spinner spinner fa-2x'></i>
  </div>
  <div class='fl-wrap-row fl-mid fl-fix ptxt-s10 ph20 bg-mdark2 ptxt-white '>
    This is lab order with visit and timepoint that create for project.
  </div>
</div>

<script>
$(document).ready(function(){
  $(".div_lab_visit_timepoint .hide-purpose2").hide();

  // Add master visit timepoint
  $(".div_lab_visit_timepoint [name=bt_add_master_visit_tp]").off("click");
  $(".div_lab_visit_timepoint [name=bt_add_master_visit_tp]").on("click", function(){
    var this_select_name = $(".div_lab_visit_timepoint [name=master_visit_tp]").find("option:selected").text();
    var this_select_val = $(".div_lab_visit_timepoint [name=master_visit_tp]").find("option:selected").val();
    var this_select_seq = $(".div_lab_visit_timepoint [name=master_visit_tp]").find("option:selected").attr("data-seq");
    var this_select_desc = $(".div_lab_visit_timepoint [name=master_visit_tp]").find("option:selected").attr("data-desc");
    var this_select_package = $(".div_lab_visit_timepoint [name=master_visit_tp]").find("option:selected").attr("data-package");
    let objDivMain = $('.div_lab_visit_timepoint');
    var sProjid = $(objDivMain).find('#sel_proj_id').val();
    var sUid = $(objDivMain).attr('data-uid');
    var txt_pid_cus = $(".div_lab_visit_timepoint [name=pid_custom_txt]").val();
    var sTimepointid = "";
    var sLabNote = "";
    let btnclick = $(this);
    
    if(confirm("คุณแน่ใจที่จะสร้าง master visit: ["+this_select_name+"] ซ้ำ?")){
      if(txt_pid_cus != ""){
        var aData = {
          u_mode: "add_proj_lab_order",
          uid:sUid,
          projid: sProjid,
          visitid: this_select_val,
          timepointid: sTimepointid,
          labnote: sLabNote,
          pid: txt_pid_cus
        };
        // console.log(aData);

        startLoad(btnclick, btnclick.next(".spinner"));
        callAjax("proj_lab_a.php",aData,function(rtnObj,aData){
          endLoad(btnclick, btnclick.next(".spinner"));
          if(rtnObj.res=='1'){
            $.notify('Create Lab Order ID: '+rtnObj.laborderid, 'success');
            let sUrl = 'lab_order_inc_main.php?uid='+aData.uid+'&coldate='+rtnObj.coldate+'&coltime='+rtnObj.coltime+'&is_doctor=1';
            $(objDivMain).parent().load(sUrl, function(responseTxt, statusTxt, xhr){
              if(statusTxt == "success"){
                $.notify('Load Lab Order', 'info');
              }
              if(statusTxt == "error")
                alert("Error: " + xhr.status + ": " + xhr.statusText);
            });
          }
          else{
            $.notify("Fail to add lab order.", "error");

          }
        });// call ajax
      }
      else{
        alert("กรุณากรอก PID");
      }
    }
  });

  $('.div_lab_visit_timepoint').off('click');
  $('.div_lab_visit_timepoint').off('change');

  $('.txtproj-uid').html($('.div_lab_visit_timepoint').attr('data-uid'));

  // Button Add extra visit
  $("#btnAddExtraVisitProject").off("click");
  $("#btnAddExtraVisitProject").on("click", function(){
    let objDivMain = $('.div_lab_visit_timepoint');
    let btnclick = $(this);
    let sProjid = $(objDivMain).find('#sel_proj_id').val();
    let sUid = $(objDivMain).attr('data-uid');
    let sPid = "";
    let sTimepointid = "";

    if (confirm('Are you sure?')) {
      // Get Visit id first master
      var aData = {
        u_mode: "first_master_visitid",
        projid: sProjid
      };

      // Get VisitID
      var sVisitid = "";
      $.ajax({
        url: "proj_lab_order_extra_ajax.php",
        method: "POST",
        cache: false,
        data: aData,
        success: function(sResult){
          if(sResult != "")
            sVisitid = sResult;
            // console.log(sVisitid);

            var aData = {
              u_mode: "count_order_note",
              uid: sUid
            };

            // Get count lab note
            let sLabNote = "";
            $.ajax({
              url: "proj_lab_order_extra_ajax.php",
              method: "POST",
              cache: false,
              data: aData,
              success: function(sResult){
                if(sResult != "")
                  sLabNote = "Extra visit"+sResult;
                  // console.log(sLabNote);

                  let objDivMain = $('.div_lab_visit_timepoint');
                  let btnclick = $(this);

                  var aData = {
                    u_mode: "add_proj_lab_order",
                    uid:sUid,
                    projid: sProjid,
                    visitid: sVisitid,
                    timepointid: sTimepointid,
                    labnote: sLabNote,
                    pid: sPid
                  };
                  // console.log(aData);

                  startLoad(btnclick, btnclick.next(".spinner"));
                  callAjax("proj_lab_a.php",aData,function(rtnObj,aData){
                    endLoad(btnclick, btnclick.next(".spinner"));
                    if(rtnObj.res=='1'){
                      $.notify('Create Lab Order ID: '+rtnObj.laborderid, 'success');
                      let sUrl = 'lab_order_inc_main.php?uid='+aData.uid+'&coldate='+rtnObj.coldate+'&coltime='+rtnObj.coltime+'&is_doctor=1';
                      $(objDivMain).parent().load(sUrl, function(responseTxt, statusTxt, xhr){
                        if(statusTxt == "success"){
                          $.notify('Load Lab Order', 'info');
                        }
                        if(statusTxt == "error")
                          alert("Error: " + xhr.status + ": " + xhr.statusText);
                      });
                    }
                    else{
                      $.notify("Fail to add lab order.", "error");

                    }
                  });// call ajax
              }
            });
          }
      });
    }
  });

  // Button Create lab order timpoint visit project
  $('.div_lab_visit_timepoint').on("click",".btn_new_lab_order",function(){
      let objDivMain = $('.div_lab_visit_timepoint');
      let btnclick = $(this);

      let sVisitid = btnclick.closest('.labrow').attr('data-visitid');
      let sTimepointid = btnclick.closest('.labrow').attr('data-timepointid');
      let sLabNote = btnclick.closest('.labrow').find('.txt-lab-note').val();
      let sProjid = $(objDivMain).find('#sel_proj_id').val();
      let sPid = $(objDivMain).find('#sel_proj_id').find(':selected').data('pid');
      let sUid = $(objDivMain).attr('data-uid');
      var txt_pid_cus = $(".div_lab_visit_timepoint [name=pid_custom_txt]").val();

      if(sProjid == "PURPOSE2"){
        if(txt_pid_cus != ""){
          var aData = {
            u_mode: "add_proj_lab_order",
            uid:sUid,
            projid: sProjid,
            visitid: sVisitid,
            timepointid: sTimepointid,
            labnote: sLabNote,
            pid: txt_pid_cus
          };
          startLoad(btnclick, btnclick.next(".spinner"));
          callAjax("proj_lab_a.php",aData,function(rtnObj,aData){
            endLoad(btnclick, btnclick.next(".spinner"));
            if(rtnObj.res=='1'){
              $.notify('Create Lab Order ID: '+rtnObj.laborderid, 'success');
              let sUrl = 'lab_order_inc_main.php?uid='+aData.uid+'&coldate='+rtnObj.coldate+'&coltime='+rtnObj.coltime+'&is_doctor=1';
              $(objDivMain).parent().load(sUrl, function(responseTxt, statusTxt, xhr){
                if(statusTxt == "success"){
                  $.notify('Load Lab Order', 'info');
                }
                if(statusTxt == "error")
                  alert("Error: " + xhr.status + ": " + xhr.statusText);
              });
            }
            else{
              $.notify("Fail to add lab order.", "error");

            }
          });// call ajax
        }
        else{
          alert("โปรดกรอก PID");
          $(".div_lab_visit_timepoint [name=pid_custom_txt]").focus();
        }
      }
      else{
        var aData = {
          u_mode: "add_proj_lab_order",
          uid:sUid,
          projid: sProjid,
          visitid: sVisitid,
          timepointid: sTimepointid,
          labnote: sLabNote,
          pid: sPid
        };

        startLoad(btnclick, btnclick.next(".spinner"));
        callAjax("proj_lab_a.php",aData,function(rtnObj,aData){
          endLoad(btnclick, btnclick.next(".spinner"));
          if(rtnObj.res=='1'){
            $.notify('Create Lab Order ID: '+rtnObj.laborderid, 'success');
            let sUrl = 'lab_order_inc_main.php?uid='+aData.uid+'&coldate='+rtnObj.coldate+'&coltime='+rtnObj.coltime+'&is_doctor=1';
            $(objDivMain).parent().load(sUrl, function(responseTxt, statusTxt, xhr){
              if(statusTxt == "success"){
                $.notify('Load Lab Order', 'info');
              }
              if(statusTxt == "error")
                alert("Error: " + xhr.status + ": " + xhr.statusText);
            });
          }
          else{
            $.notify("Fail to add lab order.", "error");

          }
        });// call ajax
      }
  });


   $('.div_lab_visit_timepoint').on("dblclick",".next-create-lab-order",function(){
      // alert("enterhere");
       $(this).closest('.labrow').find('.btn_new_lab_order').show();
       $(this).closest('.labrow').find('.txt-lab-note').show();

   });

  $('.div_lab_visit_timepoint').on("click",".btn_view_laborder",function(){
    let sColdate = $(this).closest('.labrow').attr('data-coldate');
    let sColtime = $(this).closest('.labrow').attr('data-coltime');
    let sUid = $(this).closest('.div_lab_visit_timepoint').attr('data-uid');

    let sUrl = 'lab_order_inc_main.php?uid='+sUid+'&coldate='+sColdate+'&coltime='+sColtime;
    showDialog(sUrl,"Lab Order: "+sUid+"|"+sColdate+"|"+sColtime,"100%","100%","",function(sResult){
    },false,"");

  });

// $('.div_lab_visit_timepoint,.btn_view_labresult').off('click');
  $('.div_lab_visit_timepoint').on("click",".btn_view_labresult",function(){
    let sColdate = $(this).closest('.labrow').attr('data-coldate');
    let sColtime = $(this).closest('.labrow').attr('data-coltime');
    let sUid = $(this).closest('.div_lab_visit_timepoint').attr('data-uid');

  let sUrl = 'lab_inc_result.php?uid='+sUid+'&coldate='+sColdate+'&coltime='+sColtime;
  showDialog(sUrl,"Lab Order: "+sUid+"|"+sColdate+"|"+sColtime,"100%","100%","",function(sResult){
    },false,"");

  });



  $('#sel_proj_id').off('change');
  $('#sel_proj_id').on('change',function(){
    // add visit tp
    var this_val = $(this).val();
    if(this_val == "PURPOSE2"){
      $(".div_lab_visit_timepoint .hide-purpose2").show();
    }
    else{
      $(".div_lab_visit_timepoint .hide-purpose2").hide();
    }

    // bt extra visit
    $("#btnAddExtraVisitProject").show();

    let objDivMain = $(this).closest('.div_lab_visit_timepoint');
    $(objDivMain).find('.txtproj-pid-info').html($( "#sel_proj_id option:selected" ).text());

    let sUID = $(objDivMain).attr('data-uid');
    let sProjid = $(this).val();

    var aData = {
        u_mode: "select_lab_visit_timepoint",
        uid:sUID,
        projid: sProjid
    };
    //  div_lab_visit_timepoint_detail

    startLoad($('.div_lab_visit_timepoint_detail'), $('.div_lab_visit_timepoint_detail').next(".spinner"));
    callAjax("proj_lab_a.php",aData,function(rtnObj,aData){
      endLoad($('.div_lab_visit_timepoint_detail'), $('.div_lab_visit_timepoint_detail').next(".spinner"));
      if(rtnObj.txtrow != ''){
      //  console.log("enterhere01");
        $(objDivMain).find('.div_lab_visit_timepoint_detail').html(rtnObj.txtrow);
      }
      else{
        $.notify("No record found.", "info");
        $(objDivMain).find('.div_lab_visit_timepoint_detail').html('<center>No record found.</center>');
      }
    });// call ajax
  });

  $('.div_lab_visit_timepoint').on("click",".btn_new_lab_order",function(){});
});

</script>
