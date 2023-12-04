<?
    include("assets/php-mailer-master/PHPMailerAutoload.php");
	include_once("in_php_function.php");
    include("in_db_conn.php");

    $email = getQS("email");
    $uid = getQS("uid");
    $check_val = getQS("pval");

	$query = "select uid from patient_info 
    where uid = ?
    and passwd = ?;";
	
    $stmt = $mysqli->prepare($query);
    $stmt -> bind_param("ss", $uid, $check_val);

    if($stmt->execute()){
        $stmt->bind_result($uid);
        while($stmt->fetch()){
            $data_check_have = $uid;
        }
    }

    $stmt->close();
    $mysqli->close();

	if($data_check_have != ""){
        $message = "";
        $fm = "puritat.s@ihri.org"; // *** ต้องใช้อีเมล์ @gmail.com เท่านั้น ***
        $to = "puritat.bom@gmail.com"; // อีเมล์ที่ใช้รับข้อมูลจากแบบฟอร์ม
        
        $subj = "เปลี่ยนแปลงรหัสผ่าน";
        
        /* ------------------------------------------------------------------------------------------------------------- */
        $message.= "UID: ".$data_check_have."\r\n";
        $message.= "โปรดคลิกที่ลิ้งค์นี้ <b>http://localhost/pribta/pribta21/patient_system_remember_changepass.php?val=".$check_val."&uid=".$uid."</b>\r\n\r\n";
        $message.= "ขอแสดงความนับถือ\r\n";
        $message.= "สถาบันเพื่อการวิจัยและนวัตกรรมด้านเอชไอวี";
        /* ------------------------------------------------------------------------------------------------------------- */
        
        $mesg = $message;
        
        $mail = new PHPMailer();
        $mail->SMTPDebug = 0;
        $mail->CharSet = "utf-8"; 
        
        /* ------------------------------------------------------------------------------------------------------------- */
        /* ตั้งค่าการส่งอีเมล์ โดยใช้ SMTP ของ Gmail */
        // $mail->IsSMTP();
        // $mail->Mailer = "smtp";
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->SMTPAuth = true;                  // enable SMTP authentication
        $mail->SMTPSecure = "tls";                 // sets the prefix to the servier
        $mail->Host = "smtp.live.com";      // sets GMAIL as the SMTP server
        $mail->Port = 587;                   // set the SMTP port for the GMAIL server
        $mail->Username = "puritat.s@ihri.org";  // Gmail username หรือหากท่านใช้ G-suite / WorkSpace ให้ใช้อีเมล์ @you@yourdomain แทน
        $mail->Password = "Ihri001!";    // Gmail password
        /* ------------------------------------------------------------------------------------------------------------- */
        
        $mail->setFrom($fm, 'IHRI Patient System.');
        $mail->addAddress($to, 'User');     // Add a recipient
        
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subj;
        $mail->Body    = nl2br($mesg);
        $mail->WordWrap = 50;  
        //
        if(!$mail->Send()) {
            echo 'Message was not sent.';
            echo 'ยังไม่สามารถส่งเมลล์ได้ในขณะนี้ ' . $mail->ErrorInfo;
            exit;
        } else {
            echo 'ส่งเมลล์สำเร็จ';
        }
	}
	else{
        false;
	}
?>