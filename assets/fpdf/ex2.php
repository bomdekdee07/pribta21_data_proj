<?php
    require('fpdf_protection.php');
    include("../php-mailer-master/PHPMailerAutoload.php");
    include_once("../../in_php_function.php");
    include("../../in_db_conn.php");

    $pdf = new FPDF_Protection();
    $pdf->SetProtection(array('print'), "test");
    $pdf->AddPage();
    $pdf->SetFont('Arial');
    $pdf->Write(10,'You can print me but not copy my text.');
    $tcpdf = $pdf->output('S');

    $message = "";
    $fm = "noreply@ihri.org";//"puritat.s@ihri.org"; // *** ต้องใช้อีเมล์ @gmail.com เท่านั้น ***
    $to = "puritat.bom@gmail.com"; // อีเมล์ที่ใช้รับข้อมูลจากแบบฟอร์ม
    
    $subj = "เปลี่ยนแปลงรหัสผ่าน";
    $data_check_have = "TEST01";
    $check_val = "0101";
    $uid = "TEST01";
    
    /* ------------------------------------------------------------------------------------------------------------- */
    // $message.= "UID: ".$data_check_have."\r\n";
    // $message.= "โปรดคลิกที่ลิ้งค์นี้ <b>http://localhost/pribta/pribta21/patient_system_remember_changepass.php?val=".$check_val."&uid=".$uid."</b>\r\n\r\n";
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
    $mail->Host = "smtp.office365.com";      // sets GMAIL as the SMTP server
    $mail->Port = 587;                   // set the SMTP port for the Outlook server
    $mail->Username = "noreply@ihri.org";//"puritat.s@ihri.org";  // Gmail username หรือหากท่านใช้ G-suite / WorkSpace ให้ใช้อีเมล์ @you@yourdomain แทน
    $mail->Password = "support@1";//"Ihri001!";    // Gmail password Ihri001!
    /* ------------------------------------------------------------------------------------------------------------- */
    
    $mail->setFrom($fm, 'IHRI Patient System.');
    $mail->addAddress($to, 'User');     // Add a recipient
    
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $subj;
    $mail->Body    = nl2br($mesg);
    $mail->addStringAttachment($tcpdf, 'file.pdf');
    $mail->WordWrap = 50;  
    //
    if(!$mail->Send()) {
        echo 'Message was not sent.';
        echo 'ยังไม่สามารถส่งเมลล์ได้ในขณะนี้ ' . $mail->ErrorInfo."<br>";
        echo !extension_loaded('openssl')?"Not Available":"Available";
        exit;
    } else {
        echo 'ส่งเมลล์สำเร็จ';
    }
?>
