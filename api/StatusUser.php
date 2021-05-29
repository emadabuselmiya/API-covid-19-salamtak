<?php
	//headers
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: application/json');
	header('Access-Control-Allow-Methods: PUT');
	header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

	include_once('../core/user.php');

	$user = new User($db);
	$data = $_POST['userId'];

	if($data != -1){
		$results = $user->statusUser($data);
	}else
		echo "0";
?>