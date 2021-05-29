<!DOCTYPE html>
<?php
   header('Access-Control-Allow-Origin: *');
   // header('Content-Type: application/json');
?>
<head>

<title>Data Contacts</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

	<script type="text/javascript">
	    
	    function sendToContacts(curl){
            alert('Sending Notification .');

             $.ajax({
	            type: 'GET',
	            url: curl,
	            success: function(result){
	                console.log('hello');
	                alert('hello');
	            },
	            error: function(XMLHttpRequest, textStatus, errorThrown) { 
                        alert("Status: " + textStatus); alert("Error: " + errorThrown); 
                }
            });
	    }
	</script>

<style>
    table {
      font-family: arial, sans-serif;
      border-collapse: collapse;
      width: 100%;
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
</head>
<body>
<h1>Data Uploaded By Users :</h1>
<?php
    include_once('../core/user_upload_data.php');
    $userUpload = new userUpload($db);
    $aa = array();
    $aa = $userUpload->read();

?>

<table>
<td>Haweyya ID </td><td>State Notification</td><td>Send Notification</td><td>Time</td>
<?php
    foreach($aa as $item){
?>
<tr><td>

<?php $url = 'http://apps.moh.gov.ps/corona_tracking/api/sendNotif_contact.php?upload_id='.$item[0];
echo $item[2];

if($item[3] != 0){
$state = 'Contacts Notified';
}else{
$state = 'Contacts not Notified';
}

        echo '</td><td>'.$state.'</td><td><a href="" onclick="sendToContacts(\''.$url.'\')">  Send to contacts</a></td><td>'. $item[4] .'</td></tr>';
    }
?>
    </table>
</body>