<?
        //ส่วนของการเชื่อมต่อฐานข้อมูล MySQL
        include_once("in_session.php");
        include_once("in_php_function.php");
        include("in_db_conn.php");

        //ทำการเปิดไฟล์ CSV เพื่อนำข้อมูลไปใส่ใน MySQL
        $objCSV = fopen("temp_data5.csv", "r");
        while (($objArr = fgetcsv($objCSV, 10000, "Z")) !== FALSE) {
                //นำข้อมูลใส่ในตาราง member
                $strSQL = "INSERT INTO temp_data ";

                //ข้อมูลใส่ใน field ข้อมูลดังนี้
                $strSQL .=" ";
                $strSQL .="VALUES ";
                        
                        //ข้อมูลตามที่อ่านได้จากไฟล์ลงฐานข้อมูล
                $strSQL .="('".$objArr[0]."','".$objArr[1]."','".$objArr[2]."','".$objArr[3]."','".$objArr[4]."','".$objArr[5]."','".$objArr[6]."','".$objArr[7]."','".$objArr[8]."','".$objArr[9]."','".$objArr[10]."','".$objArr[11]."','".$objArr[12]."','".$objArr[13]."','".$objArr[14]."','".$objArr[15]."','".$objArr[16]."','".$objArr[17]."','".$objArr[18]."','".$objArr[19]."','".$objArr[20]."','".$objArr[21]."','".$objArr[22]."','".$objArr[23]."','".$objArr[24]."','".$objArr[25]."','".$objArr[26]."','".$objArr[27]."','".$objArr[28]."','".$objArr[29]."','".$objArr[30]."','".$objArr[31]."','".$objArr[32]."','".$objArr[33]."','".$objArr[34]."','".$objArr[35]."','".$objArr[36]."','".$objArr[37]."','".$objArr[38]."','".$objArr[39]."','".$objArr[40]."','".$objArr[41]."','".$objArr[42]."','".$objArr[43]."','".$objArr[44]."','".$objArr[45]."','".$objArr[46]."','".$objArr[47]."','".$objArr[48]."','".$objArr[49]."','".$objArr[50]."','".$objArr[51]."','".$objArr[52]."','".$objArr[53]."','".$objArr[54]."','".$objArr[55]."','".$objArr[56]."','".$objArr[57]."','".$objArr[58]."','".$objArr[59]."','".$objArr[60]."','".$objArr[61]."','".$objArr[62]."','".$objArr[63]."','".$objArr[64]."','".$objArr[65]."','".$objArr[66]."','".$objArr[67]."','".$objArr[68]."','".$objArr[69]."','".$objArr[70]."','".$objArr[71]."','".$objArr[72]."','".$objArr[73]."','".$objArr[74]."','".json_decode($objArr[75])."','".$objArr[76]."','".$objArr[77]."','".$objArr[78]."','".$objArr[79]."','".$objArr[80]."','".$objArr[81]."','".$objArr[82]."','".$objArr[83]."','".$objArr[84]."','".$objArr[85]."','".$objArr[86]."','".$objArr[87]."','".$objArr[88]."','".$objArr[89]."','".$objArr[90]."','".$objArr[91]."','".$objArr[92]."','".$objArr[93]."','".$objArr[94]."') ";

                //ให้ข้อมูลอยู่ในรูปแบบที่อ่านได้ใน phpmyadmin (By.SlayerBUU Credits พี่ไผ่)
                $stmt = $mysqli->prepare($strSQL);
                if($stmt->execute()){echo "Import Done.";}
        }
        // echo $strSQL."<br>";
        fclose($objCSV);
?>