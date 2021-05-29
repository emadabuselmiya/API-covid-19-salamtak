<?php

include_once('../includes/config.php');
include_once('../core/user.php');

$user = new User($db);

class Bluetooth{
    //db stuff
    private $conn;
    private $table = 'bluetooth_contact';

    //post properties
    public $id;
    public $track_id;
    public $contact_id;

    
	//Constructor with db connection
    public function __construct($db){
        $this->conn = $db;
    }

    
	public function read(){
        
		//create query
        $query = 'SELECT * FROM ' . $this->table;
        
		//prepare statement       
        $stmt = oci_parse($this->conn, $query);
        
		//execute query
        oci_execute($stmt);
        return $stmt;
    }

    
    public function read_contact($user_id){
        
		//create query
        $query = 'SELECT user_id, time, lat, lng, track_id, contact_id FROM ' . $this->table . ' 
				  LEFT JOIN track_data ON track_id = id where user_id = :user_id';
        
        //prepare statement       
        $stmt = oci_parse($this->conn, $query);
		
		//clean data
        $user_id = htmlspecialchars(strip_tags($user_id));
       
        //binding of parameters
		oci_bind_by_name($stmt, ':user_id', $user_id);
		
        //execute query
        oci_execute($stmt);
        return $stmt;
    }
	

    public function create(){
		
        //create query
        $query = 'INSERT INTO ' . $this->table . ' (contact_id, track_id) VALUES (:contact_id , :track_id)';
        
		//prepare statement
        $stmt = oci_parse($this->conn, $query);
        
        //clean data
        $this->track_id = htmlspecialchars(strip_tags($this->track_id));
        $this->contact_id = htmlspecialchars(strip_tags($this->contact_id));
       
        //binding of parameters
		oci_bind_by_name($stmt, ':track_id', $this->track_id);
		oci_bind_by_name($stmt, ':contact_id', $this->contact_id);
    
        //execute the query
		if(oci_execute($stmt)){
            return true;
        }
        //print error if something goes wrong
		$e = oci_error($stmt);
        printf("Error %s. \n". $e['message']);
        return false;
    }
	
    
    public function send_contact(){
        
		//create query
        $query = 'SELECT contact_id from bluetooth_contact LEFT JOIN track_data ON track_id = id where user_id = :id';
        
        //prepare statement       
        $stmt = oci_parse($this->conn, $query);
    
        //clean data
        $this->id = htmlspecialchars(strip_tags($this->id));
    
        //binding of parameters
        oci_bind_by_name($stmt, ':id', $this->id);
    
        //execute query
		oci_execute($stmt);
        
		$result = $stmt;
		$num = oci_fetch_all($result , $res);
        
		if($num > 0){
            
			$post_arr = array();
			for ($i = 0; $i < $num; $i++){
				array_push($post_arr, $res['CONTACT_ID'][$i]);
			}
            
			/*while($row = $result->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                array_push($post_arr, $contact_id);
            }*/
			
            $contact = array_unique($post_arr);
            return $contact;
        }
    }
}
?>