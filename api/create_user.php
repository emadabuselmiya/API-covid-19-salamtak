<?php
    //headers
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: PUT');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
	
	
	//initializing our api
    include_once('../core/user.php');
   
    //instantiate post
    $post = new User($db);	

    $token = $_POST["token"];
    $android_id = $_POST["android_id"];

    $post->token = $token;
    $post->android_id = $android_id;
	
	$result = $post->search_user($android_id);
 
	//get the row count
    $num = oci_fetch_all($result , $res);

    if($num > 0){
		
		$id = $res['ID'][0];
		$post->updateToken($token, $id);
		echo json_encode($id);
			
    } else{
		$post -> create($token, $android_id);
    }
?>