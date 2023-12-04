<?
	include_once("in_php_function_test.php");
?>

<div  class=''>
	<input type='button' id='btnCreateData' value='Create' />
	<a id='btnDownload' style='display:none'>Download</a>
</div>
<div id='txtMSG'></div>
<script lang="javascript" src="assets/js/xlsx.full.min.js"></script>
<script>
var aData=[];
$(function(){
	
	$("#btnCreateData").on("click",function(){
		startCSV();
	});

	async function startCSV(){
        const aStatus = [];
        aStatus.push("RECOVERED");
        aStatus.push("REFER");
        aStatus.push("DISCHARGE");
        aStatus.push("DEATH");
        aStatus.push('PENDING');
        aStatus.push("NEGATIVE");
        aStatus.push("POSITIVE");
        aStatus.push("URGENT");
        bIsOn = true;

		try{
			iStep = 0;
			do{
				$("#txtMSG").append("Begin step:"+iStep);
				const [rowPI, userBadData] = await getPatientInfo(iStep,"NEGATIVE");	
				const nArr = [];seq=0;
				for (const row of [...rowPI]) {
		            const iKey = row[0];
		            const iValue = row[1];
		            //testKeys.push(iKey);
		            if (seq === 0){
		                const cols = Object.entries(iValue).map(ie => ie[0]);
		                aData.push(cols);
		            }else{
		                aData.push(Object.entries(iValue).map(ie => ie[1]));
		            }
		            seq++;
				}
				if(seq<100) bIsEnd = false;
				$("#txtMSG").append("End step:"+iStep);
				iStep = iStep + 100;	
			}while(bIsEnd);
			$("#txtMSG").append("OUT");
			createCSV();
		}catch{

		}
		
	}

	async function getPatientInfo(iPage=0,sStatus=""){
		sUrl = "";
		if(sStatus=="PENDING"){
			sUrl = `https://api-hibkkcare.bangkok.go.th/users?hospital=60f92f35c06493bc482dfd6c&status_null=true&type=patient&_start=${iPage}&_limit=100`;
		}else{
			sUrl = `https://api-hibkkcare.bangkok.go.th/users?hospital=60f92f35c06493bc482dfd6c&status=${sStatus}&type=patient&_start=${iPage}&_limit=100`;
		}
		
		const tf = new Map();
		await postData(sUrl)
		  .then(data => {
		  	//aData = aData.concat(data);
		  	data.forEach(aR=>{
		  		const dd = {
		  			"rowid": aR._id,
                    "type" : aR.type,
                    "updated_datetime": "NOW",
                    "status": aR.status,
                    "nationality" : aR.nationality,
                    "cardType" : aR.cardType,
                    "username" : aR.username,
                    "gender" : aR.gender,
                    "name" : esctxt(aR.name)+" "+esctxt(aR.surname),
                    "birth" : fixDate(aR.birth),
                    "hn" : aR.hn,
                    "an" : aR.an,
                    "stepType" : aR.stepType,
                    "insurance" : aR.insurance,
                    "hospital_name" : ((aR.hospital!=undefined)?aR.hospital.name:""),
                    "dayZeroSwab" : fixDate(aR.dayZeroSwab),
                    "admittedAt" : fixDate(aR.admittedAt),
                    "createdAt" : fixDate(aR.createdAt),
                    "illness" : esctxt(aR.illness),
                    "food" : esctxt(aR.food),
                    "medicineAllergy" : esctxt(aR.medicineAllergy),
                    "phone" : aR.phone,
                    "weight" : aR.weight,
                    "height" : aR.height,
                    "career" : aR.career,
                    "address_zone" : esctxt(Boolean(aR.address)==false?"":aR.address.zone),
                    "address" : esctxt(Boolean(aR.address)==false?"":aR.address.subdistrict)+" "+esctxt(Boolean(aR.address)==false?"":aR.address.district)+" "+esctxt(Boolean(aR.address)==false?"":aR.address.province),
                    "gmap" : "",
                    "bed" : "",
                    "remark" : esctxt(aR.remark),
                    "patient_link" : `https://hibkkcare.bangkok.go.th/member/patient/${aR.id}`,

		  		}
                tf.set(dd.rowid,dd);

            });
		     // JSON data parsed by `data.json()` call
		    
		});

		return[tf,undefined];

	}
	function createXLS(){
		var wb = XLSX.utils.book_new();
		wb.Props = {
                Title: "I Created by Using SheetJS",
                Subject: "XLS from CSV",
                Author: "Ratchapong Kanaprach",
                CreatedDate: new Date(2017,12,19)
        };
        wb.SheetNames.push("NONAME");
        var ws = XLSX.utils.aoa_to_sheet(aData);
        wb.Sheets["NONAME"] = ws;
		var wbout = XLSX.write(wb, {bookType:'xlsx',  type: 'binary'});


		var encodedUri = ("data:text/csv;charset=utf-8,"+wbout);
		$("#btnDownload").show();
		$("#btnDownload").attr("href",encodedUri);
		$("#btnDownload").attr("download", "my_data.xlsx");

		//saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}), 'test.xlsx');

	}

	function s2ab(s) { 
        var buf = new ArrayBuffer(s.length); //convert s to arrayBuffer
        var view = new Uint8Array(buf);  //create uint8array as viewer
        for (var i=0; i<s.length; i++) view[i] = s.charCodeAt(i) & 0xFF; //convert to octet
        return buf;    
	}

	function createCSV(){
		//let csvContent = "data:text/csv;charset=utf-8,";

		let csvContent = "data:text/csv;charset=utf-8,";

		aData.forEach(function(rowArray) {
		    let row = rowArray.join(",");
		    csvContent += row + "\r\n";
		});
		var encodedUri = encodeURI(csvContent);
		//link.setAttribute("href", encodedUri);
		//link.setAttribute("download", "my_data.csv");
		//link.setAttribute("text","HELLO");
		$("#btnDownload").show();
		$("#btnDownload").attr("href",encodedUri);
		$("#btnDownload").attr("download", "my_data.csv");

	}

	/*
	const rows = [
	    ["name1", "city1", "some other info"],
	    ["name2", "city2", "more info"]
	];


	let csvContent = "data:text/csv;charset=utf-8,";
	rows.forEach(function(rowArray) {
	    let row = rowArray.join(",");
	    csvContent += row + "\r\n";
	});

	//or the shorter way (using arrow functions):


	let csvContent = "data:text/csv;charset=utf-8," 
	    + rows.map(e => e.join(",")).join("\n");


	var encodedUri = encodeURI(csvContent);
	window.open(encodedUri);
	// OR
	
	
	var encodedUri = encodeURI(csvContent);
	var link = document.createElement("a");
	//link.setAttribute("href", encodedUri);
	//link.setAttribute("download", "my_data.csv");
	//link.setAttribute("text","HELLO");
	$("#btnDownload").attr("href",encodedUri);
	$("#btnDownload").attr("download", "my_data.csv");
	
	//document.body.appendChild(link); // Required for FF
	*/
	
	


})	

async function postData(url = '', data = {}) {
  // Default options are marked with *
  const response = await fetch(url, {

    method: 'GET', // *GET, POST, PUT, DELETE, etc.
    mode: 'cors', // no-cors, *cors, same-origin
    cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
    credentials: 'same-origin', // include, *same-origin, omit
    headers: {
    	'Content-Type': 'application/json',
        "accept": "application/json, text/plain, */*",
        "accept-language": "en-US,en;q=0.9,th;q=0.8",
        "authorization": `Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjYwZmJjNzQ1OGEzN2ZlYzM4Y2RlMjIzMyIsImlhdCI6MTYyOTc3NjY4MCwiZXhwIjoxNjMyMzY4NjgwfQ.eKlnPzYy4PZ84Sa9zb1WkxrOswYFAbMTV3mfsHstHuc`,
        "sec-ch-ua": "\"Chromium\";v=\"92\", \" Not A;Brand\";v=\"99\", \"Microsoft Edge\";v=\"92\"",
        "sec-ch-ua-mobile": "?0",
        "sec-fetch-dest": "empty",
        "sec-fetch-mode": "cors",
        "sec-fetch-site": "same-site"
    },
    body: null,
    referrer: "https://hibkkcare.bangkok.go.th/",
    referrerPolicy: "strict-origin-when-cross-origin"
  });
  return response.json(); // parses JSON response into native JavaScript objects
}

function esctxt(stext){
	sResult=stext
    if(stext==undefined || stext==null){
        return "";
    }else{
        sResult=stext.trim().replace(/\n/g, " ");
    }

    return sResult;
};

function fixDate(sDate){
    if(sDate==undefined || sDate==null) return "";
    if(sDate.length == 0){
        return sDate;
    }else{
        //"1993-01-31T17:00:00.000Z"
        aDate = sDate.split("T");
        sDate=aDate[0];
        aDate = aDate[1].split(":");

        if(aDate[0]>=17){
            var date = new Date(sDate);
            date.setDate(date.getDate()+1);
            sMonth = (date.getMonth()+1);
            sMonth = (sMonth<10)?"0"+sMonth:sMonth;
            sDay = (date.getDate());
            sDay = (sDay<10)?"0"+sDay:sDay;

            return date.getFullYear()+"-"+sMonth+"-"+sDay+"T00:00:00";
        }else{
            return sDate+"T00:00:00";
        }

        
    }
}

</script>