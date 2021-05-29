<?php
include_once('../includes/config.php');

class User{
    //db stuff
    private $conn;
    private $table = 'users';

    //post properties
    public $contact;
    public $id;
    public $token;
    public $android_id;
	

    //Constructor with db connection
    public function __construct($db){
        $this->conn = $db;
    }
	
	
	public function read(){
        
		//create query
        $query = 'SELECT * FROM ' . $this->table;

        //prepare statement
        $stmt = oci_parse($this->conn, $query);

        oci_execute($stmt);
        return $stmt;
    }

	
    public function create($token, $android_id){

        //create query
        $query = 'INSERT INTO users (token, android_id) VALUES (:token, :android_id) RETURNING id INTO :id';

        //prepare statement        
		$stmt = oci_parse($this->conn, $query);
    
        //clean data
        $token = htmlspecialchars(strip_tags($token));
        $android_id = htmlspecialchars(strip_tags($android_id));

        //binding of parameters
        oci_bind_by_name($stmt, ':token', $token);
		oci_bind_by_name($stmt, ':android_id', $android_id);
		oci_bind_by_name($stmt, ':id', $last_id,8);
		
		if(oci_execute($stmt)){
			oci_commit($this->conn);
			echo json_encode($last_id);
		}
    }

    
	public function search_user($android_id){
        
		//create query
        $query = 'SELECT id FROM ' . $this->table . ' WHERE android_id = :android_id';

        //prepare statement
        $stmt = oci_parse($this->conn, $query);
    
        //clean data
        $android_id = htmlspecialchars(strip_tags($android_id));
       
        //binding of parameters
        oci_bind_by_name($stmt, ':android_id', $android_id);
		oci_execute($stmt);
		
        return $stmt;    
    }
    
    
	public function updateToken($token, $id){
        
		//create query
        $query = 'UPDATE "USERS" set token = :token WHERE ID = :id';
    
        //prepare statement
        $stmt = oci_parse($this->conn, $query);
    
        //clean data
        $token = htmlspecialchars(strip_tags($token));
        $id = htmlspecialchars(strip_tags($id));

        //binding of parameters
        oci_bind_by_name($stmt, ':token', $token);
		oci_bind_by_name($stmt, ':id', $id);
		$r = oci_execute($stmt);
        if(!$r){			
			$e = oci_error($stmt);  // For oci_execute errors pass the statement handle
		    echo htmlentities($e['message']);
        }else{
			oci_commit($this->conn);
            //echo json_encode('updated');
		}
    }
	
	
	public function statusUser($id){
		
        //$this->updateStatusUser();
        
		//create query
        $query = 'SELECT status FROM users WHERE id = :id';
    
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
			
			for ($i = 0; $i < $num; $i++){				
				$stat = $res['STATUS'][$i];
			}
        }
        echo $stat;
    }
	
	
	public function updateStatusUser(){
		
		//create query
        $query = 'UPDATE users SET status = 0 WHERE CURDATE() = ADDDATE(DATE(status_dt), INTERVAL 15 DAY)';
    
        //prepare statement
        $stmt = oci_parse($this->conn, $query);
		
		if(oci_execute($stmt)){
			oci_commit($this->conn);
		}
    }
}
?>