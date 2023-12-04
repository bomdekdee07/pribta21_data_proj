<?
  include_once("in_php_function.php");
  $sName = urlencode(getQS("name"));
  $sMode=getQS("u_mode");
  $sResult = "";
  $sHtml = "";
  if($sMode=="TEST"){
    $sResult = json_decode("[[\"rec_id\",\"type\",\"updated_datetime\",\"status\",\"nationality\",\"cardType\",\"username\",\"gender\",\"name\",\"birth\",\"hn\",\"an\",\"stepType\",\"insurance\",\"hospital_name\",\"dayZeroSwab\",\"admittedAt\",\"createdAt\",\"illness\",\"food\",\"medicineAllergy\",\"phone\",\"weight\",\"height\",\"career\",\"address_zone\",\"address\",\"gmap\",\"bed\",\"remark\",\"patient_link\"],[\"611a1814f627a24840721865\",\"patient\",\"8/24/2021, 1:40:51 PM\",\"DISCHARGE\",\"ไทย\",\"บัตรประชาชน\",\"3130600035071\",\"ชาย\",\"ประสาร ทับทอง\",\"1964-11-22T00:00:00\",null,null,null,\"สิทธิหลักประกันสุขภาพแห่งชาติ\",\"ศูนย์บริการสาธารณสุข 7 บุญมี ปุรุราชรังสรรค์\",\"2021-08-13T00:00:00\",\"\",\"2021-08-16T00:00:00\",\"ไม่มีอาการ\",\"อาหารทั่วไป\",\"\",\"0614340937\",85,170,\"อาชีพอิสระ,รับจ้าง\",\"427/34 ซ.ปรีชา 1 สาธุ 57\",\"บางโพงพาง ยานนาวา กรุงเทพมหานคร\",\"\",\"\",\"ไปอยู่รังสิต\",\"https://hibkkcare.bangkok.go.th/member/patient/611a1814f627a24840721865\"],[\"6102af0e4e8dc649e71bdf7c\",\"patient\",\"8/24/2021, 1:40:51 PM\",\"REFER\",\"ไทย\",\"บัตรประชาชน\",\"3120400204359\",\"ชาย\",\"ประสาร สุภาพ\",\"1939-05-08T00:00:00\",null,null,null,\"สิทธิหลักประกันสุขภาพแห่งชาติ\",\"ศูนย์บริการสาธารณสุข 43 มีนบุรี\",\"2021-07-29T00:00:00\",\"2021-07-29T00:00:00\",\"2021-07-29T00:00:00\",\"\",\"อาหารทั่วไป\",\"\",\"0633627504\",null,168,\"ว่างงาน\",\"วัดสุขใจ\",\"ทรายกองดิน คลองสามวา กรุงเทพมหานคร\",\"\",\"\",\"ผู้ป่วยอยู่นอกพื้นที่ และส่งต่อให้ศูนย์บริการสาธารณสุข 64 คลองสามวา\",\"https://hibkkcare.bangkok.go.th/member/patient/6102af0e4e8dc649e71bdf7c\"],[\"60fe5f3ca24b0dc4a2ad65e8\",\"patient\",\"8/24/2021, 1:40:51 PM\",\"DISCHARGE\",\"ไทย\",\"บัตรประชาชน\",\"3100200188368\",\"ชาย\",\"ประสาร สารานิกรณ์\",\"1930-02-21T00:00:00\",\"015841\",null,\"Step Up\",\"สิทธิหน่วยงานรัฐอื่นๆ\",\"พริบตาแทนเจอรีน สาขา 1\",\"2021-07-14T00:00:00\",\"2021-07-15T00:00:00\",\"2021-07-26T00:00:00\",\"เริ่มมีอาการวันที่ 5/7/2021 มีไข้ ปวดหัว ปวดเมื่อย ไอ มีโรคประจำตัว โรคหัวใจ ความดันต่ำ\",\"อาหารทั่วไป\",\"ไม่มี\",\"0851362719\",40,150,\"ว่างงาน\",\"49/36 ม9\",\"บางพูด ปากเกร็ด นนทบุรี\",\"\",\"\",\"คนไข้เสียชีวิตที่ รพ บุษราคัม [อยู่บุษราคัมตั้งแต่ก่อนลงทะเบียนเข้าระบบ]\",\"https://hibkkcare.bangkok.go.th/member/patient/60fe5f3ca24b0dc4a2ad65e8\"],[\"60fc60da950054c3d336b7d9\",\"patient\",\"8/24/2021, 1:40:51 PM\",\"REFER\",\"ไทย\",\"บัตรประชาชน\",\"3101700624796\",\"ชาย\",\"ประสาร ศุภเสริฐ\",\"1948-06-17T00:00:00\",\"013157\",\"\",\"Step Up\",\"\",\"พริบตาแทนเจอรีน สาขา 1\",\"\",\"\",\"2021-07-25T00:00:00\",\"\",\"\",\"\",\"0969929668\",null,null,\"\",\"1424/78 ซ ประชาสงเคาระห์16 ถนนประชาสงเคราะห์\",\"ดินแดง ดินแดง กรุงเทพมหานคร\",\"\",\"\",\"ไป admit รพ. แล้ว\",\"https://hibkkcare.bangkok.go.th/member/patient/60fc60da950054c3d336b7d9\"],[\"60fab865347b63c3eb6e5e4d\",\"patient\",\"8/24/2021, 1:40:51 PM\",\"DISCHARGE\",\"ไทย\",\"บัตรประชาชน\",\"3720500005433\",\"ชาย\",\"ประสาร ประพันพัฒน์\",\"1956-06-21T00:00:00\",\"011420\",null,\"Step Up\",\"สิทธิหลักประกันสุขภาพแห่งชาติ\",\"พริบตาแทนเจอรีน สาขา 1\",\"2021-07-23T00:00:00\",\"2021-07-23T00:00:00\",\"2021-07-23T00:00:00\",\"\",\"\",\"\",\"0620433988\",54,159,\"อาชีพอิสระ,รับจ้าง\",\"รอแพทย์\",\"คลองจั่น บางกะปิ กรุงเทพมหานคร\",\"\",\"\",\"ผู้ป่วยได้เตียง รพ ศรีประจันต์ แล้ว\",\"https://hibkkcare.bangkok.go.th/member/patient/60fab865347b63c3eb6e5e4d\"]]");
  }else{
    $sUrl =  "http://".$_SERVER["HTTP_HOST"].":3000/patient?name=".$sName;
    $sUrl =  "http://localhost:3000/patient?name=".$sName;

    try{
      $ch = curl_init();
      curl_setopt( $ch, CURLOPT_URL, $sUrl );
      //curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
      curl_setopt( $ch, CURLOPT_POST, false );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
      $content = curl_exec( $ch );
      curl_close($ch);

      

      if($content==""){
        echo("No data found");
      }else{
        //$sResult = $content;  
        $sResult = json_decode($content);
      }
      
    }catch(Exception $ex){
      error_log($ex);
    }
  }
  

  if($sResult !=""){
    $aCol = array();
    foreach ($sResult[0] as $key => $sCol) {
      $aCol[$sCol] = $key;
      if($key==0){
        $sHtml.="<div class='fl-wrap-row data-row h-30 lh-15 row-color-2'>
            <div class='fl-fix w-200 fl-mid'>ID</div>
            <div class='fl-fix w-100 fl-mid'>Status</div>
            <div class='fl-fill fl-mid'>Name</div>
            <div class='fl-fill fl-mid'>Illness</div>
            <div class='fl-fix w-100'>dayZeroSwab</div>
            <div class='fl-fix w-100'>admittedAt</div>
            <div class='fl-fix w-100 fl-mid'>Phone</div>
            <div class='fl-fix w-300 fl-mid'>Hospital</div>
            <div class='fl-fix w-80 fl-mid lh-15'>Link</div>
          </div>";
      }

    }

    foreach ($sResult as $key => $aRow) {
      if($key!=0){
        //Skip header row;
        /*
[\"rec_id\",\"type\",\"updated_datetime\",\"status\",\"nationality\",\"cardType\",\"username\",\"gender\",\"name\",\"birth\",\"hn\",\"an\",\"stepType\",\"insurance\",\"hospital_name\",\"dayZeroSwab\",\"admittedAt\",\"createdAt\",\"illness\",\"food\",\"medicineAllergy\",\"phone\",\"weight\",\"height\",\"career\",\"address_zone\",\"address\",\"gmap\",\"bed\",\"remark\",\"patient_link\"]
*/
        $sSwab = str_replace("T00:00:00","",$aRow[$aCol["dayZeroSwab"]]);
        $sAdmit = str_replace("T00:00:00","",$aRow[$aCol["admittedAt"]]);

        $sHtml.="<div class='fl-wrap-row data-row h-40 lh-15 row-color row-hover'>
          <div class='fl-wrap-col w-200 fs-small lh-20'>
            <div class='fl-fix h-20'>".$aRow[$aCol["rec_id"]]."</div>
            <div class='fl-fix h-20 fw-b'>ID:".$aRow[$aCol["username"]]."</div>
          </div>
          <div class='fl-fix w-100 fl-mid'>".$aRow[$aCol["status"]]."</div>
          <div class='fl-fill lh-30'>".$aRow[$aCol["name"]]."</div>
          <div class='fl-fill fs-small fl-auto'>".$aRow[$aCol["illness"]]."</div>
          <div class='fl-fix w-100'>".$sSwab."</div>
          <div class='fl-fix w-100'>".$sAdmit."</div>
          <div class='fl-fix w-100'>".$aRow[$aCol["phone"]]."</div>
          <div class='fl-fix w-300 lh-15'>".$aRow[$aCol["hospital_name"]]."</div>
          <div class='fl-fix w-80 fl-mid lh-15'><a target='_blank' href='".$aRow[$aCol["patient_link"]]."'><i>Link</i></a></div>
        </div>";
      }
    }

    echo($sHtml);
  }


?>