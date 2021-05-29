<?php
include_once('../includes/config.php');
include_once('../core/user.php');

$user = new User($db);

class userUpload{
    //db stuff
    private $conn;
    private $table = 'user_upload_track';

    //post properties
    public $id;
	public $user_id;
    public $haweyya;
    public $send = array();

    
	//Constructor with db connection
    public function __construct($db){
        $this->conn = $db;
    }
 

    public function read(){
		
        //create query
        $query = "SELECT id, user_id, haweyya, contacts_notified, TO_CHAR(create_at,'DD/MM/RRRR HH:MI:SS AM') as create_at
					FROM user_upload_track
				   ORDER BY create_at DESC";

        //prepare statement       
        $stmt = oci_parse($this->conn, $query);

        //execute query
        oci_execute($stmt);

        $haweyya_arr = array();
        $result = $stmt;

        $num = oci_fetch_all($result , $res);

        if($num > 0){
			
			for ($i = 0; $i < $num; $i++){
				$info_arr = array($res['ID'][$i], $res['USER_ID'][$i], $res['HAWEYYA'][$i], $res['CONTACTS_NOTIFIED'][$i], $res['CREATE_AT'][$i]);
                array_push($haweyya_arr, $info_arr);
			}
        }
        return $haweyya_arr;
    }
	
    
    public function create($user_id, $haweyya){
		
        //create query
        $query = 'INSERT INTO user_upload_track (user_id, haweyya, contacts_notified) 
				       VALUES (:user_id , :haweyya , 0) RETURNING id INTO :id';
    
        //prepare statement
        $stmt = oci_parse($this->conn, $query);
    
        //clean data
        $user_id = htmlspecialchars(strip_tags($user_id));
        $haweyya = htmlspecialchars(strip_tags($haweyya));
       
        //binding of parameters
		oci_bind_by_name($stmt, ':user_id', $user_id);
		oci_bind_by_name($stmt, ':haweyya', $haweyya);
		oci_bind_by_name($stmt, ':id', $last_id,8);
		
		if(oci_execute($stmt)){
			oci_commit($this->conn);
			echo $last_id;
		}
    }
	
    
    public function callact_contact($upload_id){
		
		$user_id = $this->getUserByUploadID($upload_id);
        
		$this->update_notified($user_id);
		
		//create query
        $query = 'SELECT DISTINCT(bluetooth_contact.contact_id)
					FROM bluetooth_contact, track_data
				   WHERE track_data.id = bluetooth_contact.track_id
				     AND track_data.upload_id = :upload_id
					 AND track_data.user_id = :user_id';
        
		//prepare statement       
        $stmt = oci_parse($this->conn, $query);
        
        $user_id = htmlspecialchars(strip_tags($user_id));
		$upload_id = htmlspecialchars(strip_tags($upload_id));
		
        oci_bind_by_name($stmt, ':user_id', $user_id);
        oci_bind_by_name($stmt, ':upload_id', $upload_id);

        oci_execute($stmt);
        
        $contacts_arr = array();
        $result = $stmt;

        $num = oci_fetch_all($result , $res);
		
        if($num > 0){
			for ($i = 0; $i < $num; $i++){				
				array_push($contacts_arr, $res['CONTACT_ID'][$i]);
			}
        }
		
		foreach($contacts_arr as $item){			
            $this->updateStatusUser($item, 1);			
        }
		
		$this->notify($contacts_arr);
    }
        
        
    public function notify($contact){
		
		$a = $contact;
		
        for($i = 0 ; $i< count($a); $i++){
			
			//create query
            $query = 'SELECT token FROM users WHERE id = :id';

            //prepare statement
            $stmt = oci_parse($this->conn, $query);

            //clean data
            $this->contact = htmlspecialchars(strip_tags($this->contact));
                
            //binding of parameters
			oci_bind_by_name($stmt, ':id', $a[$i]);
			
            oci_execute($stmt);

            $result = $stmt;
            $num = oci_fetch_all($result , $res);
			
            if($num > 0){
				
				$post_arr = array();
				$aa = $res['TOKEN'][0];
				
				/*while($row = $result->fetch(PDO::FETCH_ASSOC)){
                    extract($row);
                    $aa= $token;
                }*/
                $this->send[$i] = $aa;				
            }                
        }
		
		$con = array();
        foreach($this->send as $a){
            if(!in_array($a, $con)){
                array_push($con, $a);
            }
        }

        define( 'API_ACCESS_KEY', 'AAAARPPBtPY:APA91bEGt4TFF4kT4tDRdK9SBN-95volNLHL-V1-FXuMq5e3IlqdwaWeLNdO1b9RdZUL1UMZ1VNpWYNSWA4Ll9BaXOSpA7e72UuwZ_u2rllkFMwMfJip44b9MSD3SuyTt7IDN9j8hdH0');
		
		$row = $con;
        for($i=0; $i<count($row); $i++){
            
			$key = $row[$i];
			
			$msg = array
                (
                'body' 	=> '',
                'title'	=> ''
                );
			
			$fields = array
                (
                'to'		=> 	$key,		
                'notification'	=> $msg
                );				
                
            $headers = array
                (
                'Authorization:key=' . API_ACCESS_KEY,
                'Content-Type:application/json'
                );
                        
            #Send Reponse To FireBase Server
			$ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ));
            $cresult = curl_exec($ch );
            echo $cresult;
			
			if ($cresult === FALSE) {
				die('Oops! FCM Send Error: ' . curl_error($ch));
			}			
            curl_close( $ch );
        }
    }
    
    
    public function update_notified($id){
        
		//create query
        $query = 'UPDATE user_upload_track set contacts_notified = 1 WHERE user_id = :id';
    
        //prepare statement
        $stmt = oci_parse($this->conn, $query);
    
        //clean data
        $id = htmlspecialchars(strip_tags($id));

        //binding of parameters
        oci_bind_by_name($stmt, ':id', $id);

        if(oci_execute($stmt)){
         //  echo json_encode("updated");
        }
    }
	
	
	public function updateStatusUser($id, $status){
		
        //create query
        $query = 'UPDATE users set status = :status WHERE id = :id AND status <> 2';
    
        //prepare statement
		$stmt = oci_parse($this->conn, $query);
    
        //clean data
        $id = htmlspecialchars(strip_tags($id));
        $status = htmlspecialchars(strip_tags($status));


        //binding of parameters
		oci_bind_by_name($stmt, ':id', $id);
		oci_bind_by_name($stmt, ':status', $status);

        if(oci_execute($stmt)){
			oci_commit($this->conn);
           // echo json_encode("updated status.");
        }
    }
	
	
	public function getUserByUploadID($id){
		
		//create query
        $query = 'SELECT user_id FROM user_upload_track where id = :id';
    
        //prepare statement
        $stmt = oci_parse($this->conn, $query);
    
        //clean data
        $id = htmlspecialchars(strip_tags($id));

        //binding of parameters
		oci_bind_by_name($stmt, ':id', $id);

        oci_execute($stmt);
		
        $result = $stmt;
		$num = oci_fetch_all($result , $res);
		
		if($num > 0){			
			$post_arr = array();
			$aa = $res['USER_ID'][0];
			return $aa;  
		}
    }
}
?>