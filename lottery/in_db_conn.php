<?

// $dbhost = '127.0.0.1';
// $dbuser = 'iclinic';
// $dbpass = 'cinilci14';
// $dbname = 'iclinic';
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = 'P@ssw0rd';
$dbname = 'lottery';

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}
mysqli_set_charset($mysqli,'utf8');
?>