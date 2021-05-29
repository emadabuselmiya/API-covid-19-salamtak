<?php
    //headers
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: PUT');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
    
    include_once('../core/bluetooth_contact.php');
    include_once('../core/track_data.php');
    include_once('../core/user_upload_data.php');
	
	//instantiate track
    $track = new track($db);
    $blue = new Bluetooth($db);
	$userUpload = new userUpload($db);
	
	$upload_id = 0;
	
	$user_id = -1;
    
    $data = json_decode(file_get_contents("php://input"));
	
	$upload_id = $data->upload_id;
	
	$track_data = $data->track_data;
    
	foreach($track_data as $i){
		
		$user_id = $i->user_id;
		$track->user_id = $i->user_id;
		$track->time = $i->time;
		$track->lat = $i->lat;
		$track->lng = $i->lng;		
		$track->upload_id = $upload_id;
		
		$last_id = $track -> create();
	
		if($last_id != -1){
			
			$contact_ids = $item->contact_ids;
			
			foreach($contact_ids as $i){
				
				$blue->contact_id = $i;
				$blue->track_id = $last_id;
				$blue->create();
			}
		}
	}
	
	$userUpload->updateStatusUser($user_id, 2);
	
    echo json_encode(true);
?>