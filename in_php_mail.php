<?

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require_once("assets/phpmailer/src/Exception.php") ;
require_once("assets/phpmailer/src/PHPMailer.php") ;
require_once("assets/phpmailer/src/SMTP.php") ;
//include_once('in_ts_get_error_mail.php') ; // get error email sending



function sendEmail($email_subject="No subject", $email_message="No message",
                   $emailListTO=array(),
                   $emailListCC=array(),
                   $emailListBCC=array()
                  ){
  $rtn = array("res"=>"0", "msg_info"=>"");
  $mail = new PHPMailer(true);
  try {
      //Server settings
      $mail->SMTPDebug = 0; // no debug
    //$mail->SMTPDebug = 2;                                // Enable verbose debug output
      $mail->isSMTP();                                      // Set mailer to use SMTP
      $mail->Host = 'smtp.office365.com';  // Specify main and backup SMTP servers
      $mail->SMTPAuth = true;                               // Enable SMTP authentication

      $mail->Username = 'noreply@ihri.org';    // SMTP username
      $mail->Password = 'support@1'; // SMTP password

      $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
      $mail->Port = 587 ;                                    // TCP port to connect to

/*
      "host"      => "smtp.office365.com",
      "port"      => 587,
      "auth"      => true,
      "secure"    => "tls",
      "username"  => "clientemail@office365.com",
      "password"  => "clientpass",
      "to"        => "myemail",
      "from"      => "clientemail@office365.com",
      "fromname"  => "clientname",
      "subject"   => $subject,
      "body"      => $body,
      "altbody"   => $body,
      "message"   => "",
      "debug"     => false
*/
      //Recipients
    //  $mail->setFrom('phanu@ihri.org', 'IHRI System');
     $mail->setFrom('noreply@ihri.org', 'IHRI System');
      //$emailListTO = array("aaa@mail.com"=>"Mr. AAA", "bbb@mail.com"=>"Mr. BBB");

      // Add a recipient
      //echo "amt of mailTO : ".sizeof($emailListTO);

      foreach($emailListTO as $person_email => $person_name){
      //   echo "add mail to : ".$person_email." : ".$person_name;
         $mail->addAddress($person_email, $person_name);
      }

      foreach($emailListCC as $person_email => $person_name){
         $mail->addCC($person_email, $person_name);
      }

      foreach($emailListCC as $person_email => $person_name){
         $mail->addBCC($person_email, $person_name);
      }

      //$mail->addAddress('phanu@trcarc.org', 'Phanu S.');     // Add a recipient
      //$mail->addAddress('ellen@example.com');               // Name is optional
      //$mail->addReplyTo('info@example.com', 'Information');
      //$mail->addCC('cc@example.com');
      //$mail->addBCC('bcc@example.com');

      //Attachments
      //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
      //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

      //Content
      $mail->isHTML(true); // Set email format to HTML
      $mail->CharSet = 'UTF-8';
      $mail->Subject = $email_subject;
      $mail->Body    = $email_message;
      //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

      $mail->send();
      //echo 'Message has been sent'.$mail->ErrorInfo;
      $rtn['res'] = '1';

  } catch (Exception $e) {
      //echo 'Message fail to sent.';
      $rtn['res'] = '0';
      $rtn['msg_info'] = "Send Mail Error : ".$mail->ErrorInfo; //return sendmail result to log
  }

  return $rtn;
}



?>
