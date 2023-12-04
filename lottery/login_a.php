<?
include_once("in_session.php");
include("in_db_conn.php");
include_once("in_php_function.php");

$username = getQS("username");
$pass = getQS("password");
$sMode = getQS("mode");

if($sMode == "login"){
	if($username == "" || $pass==""){
		exit();
	}

	$bind_value = "s";
	$array_val = array($username);
	$data_check_pass = "";
	$aRes = "0";

	$query ="SELECT s_id, name, password
	FROM staff
	WHERE user = ?;";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param($bind_value, ...$array_val);

	if($stmt->execute()){
		$stmt->bind_result($s_id, $name, $password);
		while($stmt->fetch()){
			$data_check_pass = $password;
			$_SESSION["s_id"] = $s_id;
			$_SESSION["name"] = $name;
		}
	}
	$mysqli->close();
	$mysqli->close();

	if($data_check_pass != ""){
		if(password_verify($pass, $data_check_pass)){
			$aRes = "1";
		}
		else{
			unset($_SESSION["s_id"]);
			unset($_SESSION["name"]);
		}
	}
}
else if($sMode=="logout"){
	unset($_SESSION["s_id"]);
	unset($_SESSION["name"]);
	unset($_SESSION);
	session_destroy();
	$aRes = "1";
}

$returnData = json_encode($aRes);
echo($returnData);

?>