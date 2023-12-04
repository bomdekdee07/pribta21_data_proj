<?

$ENC_VI = ("TRCGOODDAYARCUCP");
$SEC_CODE = "UCP2TRAINING";


function encodeSingleLink($link)
{
  global $ENC_VI;
  global $SEC_CODE;

  $link = openssl_encrypt($link,"AES-256-CBC",$SEC_CODE,0,$ENC_VI);
  $link = str_replace("+","aBcD",$link);
  return $link; // link encode
}

function decodeSingleLink($link)
{
  global $ENC_VI;
  global $SEC_CODE;
  $link = str_replace(" ","+",$link);
  $link = str_replace("aBcD","+",$link);
  $x = openssl_decrypt($link,"AES-256-CBC",$SEC_CODE,0,$ENC_VI);
  return $x;
}

function createRandomCode($char_amt)
{
  $string = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-';
  $string_shuffled = str_shuffle($string);
  $random_str = substr($string_shuffled, 1, $char_amt);
  return $random_str;
}



?>
