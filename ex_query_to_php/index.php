<?
    include("in_db_conn.php");

    $data_table = array();
    $query = "SELECT uid, uic, fname from patient_info limit 100";
    $stmt = $mysqli->prepare($query);
    
    if($stmt->execute()){
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $data_table[$row["uid"]]["uid"] = $row["uid"];
            $data_table[$row["uid"]]["uic"] = $row["uic"];
            $data_table[$row["uid"]]["name"] = $row["fname"];
        }
        // print_r($data_table);
    }

    $htmlBind = "";
    $htmlBind .= '<h2>HTML Table</h2>
                    <table>
                        <tr>
                            <th>Uid</th>
                            <th>Uic</th>
                            <th>Name</th>
                        </tr>';

    foreach($data_table as $key => $val){
        $htmlBind .= "  <tr>
                            <td>
                                ".$val["uid"]."
                            </td>
                            <td>
                                ".$val["uic"]."
                            </td>
                            <td>
                                ".$val["name"]."
                            </td>
                        </tr>";
    }

    $htmlBind .= "</table>";
?>

<style>
    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 90%;
    }

    td, th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }

    tr:nth-child(even) {
        background-color: #dddddd;
    }
</style>

<? echo $htmlBind; ?>