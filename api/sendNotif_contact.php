<?php        
	//headers
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: application/json');
	header('Access-Control-Allow-Methods: PUT');
	header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

	include_once('../core/user_upload_data.php');
	$userUpload = new userUpload($db);	
	
	if(isset($_GET['upload_id'])) {
		
		$userUpload->callact_contact($_GET['upload_id']);
		
	}else {
		$query = 'SELECT id FROM user_upload_track
				   WHERE contacts_notified = 0
					 AND EXISTS (SELECT id FROM users WHERE id = user_id AND status = 2)
					 AND TRUNC(SYSDATE) - TRUNC(create_at) <= USER_STATUS_INTERVAL
				   ORDER BY create_at';
				   
		$stmt = oci_parse($this->conn, $query);
		
		oci_execute($stmt);
		
		$result = $stmt;
		$num = oci_fetch_all($result , $res);

		if($num > 0){			
			for ($i = 0; $i < $num; $i++){
				$userUpload->callact_contact($res['ID'][$i]);
			}
		}
	}
?>