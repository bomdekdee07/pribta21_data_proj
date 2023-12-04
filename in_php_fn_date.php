<?

//Date function

// get Date from DB and change to date format d-M-y
function changeToThaiDate($dateData)
{
  $dateVal = explode("-", $dateData);
  $dateVal[0] = $dateVal[0] + 543;
  $value = $dateVal[2]."/".$dateVal[1]."/".$dateVal[0];
  return $value;
}


function getDBDateThai($dateData) // eg. change from 2021-01-01  to 1 ม.ค. 2564
{
  $txtDate = "";
  if($dateData != "0000-00-00" && $dateData != "0000-00-00 00:00:00" && $dateData !== NULL){


    // Thai Month
    $mtn_arr = array();
    $mtn_arr[] = "ทั้งหมด";
    $mtn_arr[] = "ม.ค.";
    $mtn_arr[] = "ก.พ.";
    $mtn_arr[] = "มี.ค.";
    $mtn_arr[] = "เม.ย.";
    $mtn_arr[] = "พ.ค.";
    $mtn_arr[] = "มิ.ย.";
    $mtn_arr[] = "ก.ค.";
    $mtn_arr[] = "ส.ค.";
    $mtn_arr[] = "ก.ย.";
    $mtn_arr[] = "ต.ค.";
    $mtn_arr[] = "พ.ย.";
    $mtn_arr[] = "ธ.ค.";

    $num_date = (int) (new DateTime($dateData))->format('d');
    $num_month = (int) (new DateTime($dateData))->format('m');
    $thai_year = (int)(new DateTime($dateData))->format('Y') + 543;
    $txtDate = $num_date." ".$mtn_arr[$num_month]." $thai_year";
  }
  return $txtDate;
}


function getToday()
{
  $txtDate = (new DateTime())->format('Y-m-d');
  return $txtDate;
}

function getDateToString($dateObj)
{
  $txtDate = $dateObj->format('Y-m-d');
  return $txtDate;
}

// increment or decrement day in date_param  | date_param (2021-01-01), day_amt (40 or -40)
function addDayToDate($date_param, $day_amt) {
  $date=date_create($date_param);
  date_add($date,date_interval_create_from_date_string("$day_amt days"));
  return date_format($date,"Y-m-d");
}


?>
