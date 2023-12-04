<?
/* Project Thumbnail list  */

include("in_session.php");
include_once("in_php_function.php");

include("in_db_conn.php");

$s_id = getQS("s_id");
if($s_id == ""){
   if(isset($_SESSION["s_id"])){
     $s_id =$_SESSION["s_id"];
   }
}


$sCode = "";

	$query =" SELECT P.proj_id, P.proj_name,P.proj_desc, P.is_enable,  P.proj_group_amt, COUNT(PUL.uid) as uid_amt
	FROM p_project P
	LEFT JOIN p_project_uid_list PUL
	ON (P.proj_id = PUL.proj_id AND PUL.uid_status  IN (1,2))
	WHERE P.is_enable = 1  AND P.proj_id IN
	(select proj_id from p_staff_auth where s_id=? AND allow_view=1)
	GROUP BY P.proj_id
	ORDER BY P.proj_id
	";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s", $s_id);

	if($stmt->execute()){
	  $stmt->bind_result($proj_id,$proj_name,$proj_desc, $proj_enable, $proj_group_amt,$uid_amt );
	  while ($stmt->fetch()) {
      /*
		   $sCode .= "
			 <div class='fl-float fabtn proj-list-item roundcorner' data-projid='$proj_id'>
				 <div>[$proj_id] $proj_name</div>
				 <div class='smallfont'>Total Group: $proj_group_amt</div>
				 <div class='smallfont'>Total PID: $uid_amt</div>
			 </div>
			 ";
*/
       $proj_enable = ($proj_enable == 1)?"<i class='fa fa-folder-open fa-sm' title='Project is opened.'></i>":"<i class='fa fa-folder fa-sm' title='Project is closed.'></i>";
       $sCode .= "
       <div class='fl-float my-4 mx-4 roundcorner bg-mdark1 pbtn proj-list-itm' data-projid='$proj_id'>
         <div class='mx-1 my-1 fl-wrap-col pw200 '>
           <div class='fl-wrap-row ph50'>
             <div class='fl-fix pw50 fl-mid bg-mdark1 ptxt-white'>
               <i class='fa fa-clipboard-list fa-lg' title='$proj_desc'></i>
             </div>
             <div class='fl-fill '>
                <div class='fl-wrap-row ph30 px-2  bg-mdark3 ptxt-white ptxt-b'>
                  $proj_id
                </div>
                <div class='fl-wrap-row ph20 px-2  ptxt-s10 ptxt-b bg-msoft2'>
                  $proj_name
                </div>
             </div>
           </div>
           <div class='fl-wrap-row ph30'>
              <div class='fl-fix fl-mid pw50 bg-mdark1 ptxt-white'>
                   $proj_enable
              </div>
              <div class='fl-fix pw150 pbg-white'>

                  <i class='fa fa-layer-group fa-sm ml-2' title='Project Group Amount'></i> $proj_group_amt
                  <i class='fa fa-users fa-sm ml-2' title='Total PID in Project'></i> $uid_amt

              </div>
           </div>
         </div>

       </div>
       ";
	  }
	}

	$mysqli->close();
?>




<div class='fl-wrap-row fl-mid ptxt-white bg-mdark1 ph30 ptxt-b'>
  <i class="fas fa-clipboard-list fa-lg mr-2"></i> PROJECT LIST
</div>
<div class='fl-fill fl-auto proj-list'>
<?
if($sCode != "")
echo $sCode;
else
echo "<center><b>No Project Found.</b></center>";
?>
</div>
