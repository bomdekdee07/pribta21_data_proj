<?
include_once("in_session.php");
include_once("in_php_function.php");
$sIsAdmin = getSS("sysadmin");
$sIsProjAdmin = getSS("projadmin");
$sSid = getSS("s_id");
$sClinicId = getSS("clinic_id");

$sMenu = "";

//System Admin Menu
if($sIsAdmin=="1"){
	$sMenu = "<i class='btnlink fabtn fas fa-project-diagram fa-2x' data-link='setting_main_project' title='Project Settings'></i> 
<i class='btnlink fabtn fas fa-clinic-medical fa-2x' data-link='setting_main_clinic' title='Clinic Settings'></i> 
<i class='btnlink fabtn fas fa-users fa-2x' data-link='setting_main_user' title='User Settings'></i> 
<i class='btnlink fabtn fas fa-link fa-2x' data-link='setting_main_page' title='Page Settings'></i> 
<i class='btnlink fabtn fas fa-puzzle-piece fa-2x' data-link='setting_main_section' title='Section Settings'></i>
<i class='btnlink fabtn fas fa-layer-group fa-2x' data-link='module_main' title='Module Settings'></i>

<i class='btnlink fabtn fas fa-cubes fa-2x' data-link='supply_management_main' title='Supply Settings'></i>
";
}



//Project Admin Menu
$sMenu .= "<i class='btnlink fabtn fas fa-cog fa-2x' data-link='user_setting_inc_main' title='User Setting'></i><i class='btndlglink fabtn fas fa-key fa-2x' data-h='380' data-w='300' data-link='user_inc_change_pass' title='Change Password'></i> ";


echo($sMenu);
?>

