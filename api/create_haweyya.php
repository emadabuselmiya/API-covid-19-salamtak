<?php
    //headers
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: PUT');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
    
    include_once('../core/user_upload_data.php');
    
    $userUpload = new userUpload($db);
    
    $haweyya = $_POST['haweyya'];
    $userId = $_POST['userId'];
    
    $userUpload->create($userId , $haweyya);
?>