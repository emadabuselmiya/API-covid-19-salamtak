<?php
include_once('../includes/config.php');
include_once('../core/bluetooth_contact.php');

$blue = new Bluetooth($db);

class track{
    
    //db stuff
    private $conn;
    private $table = 'track_data';

    //category properties
    public $id;
    public $user_id;
    public $time;   
    public $lat;
    public $lng;
	public $upload_id;
    public $contact;
    public $token;
    public $send = array();
	
    
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
	
	
	public function countUser(){
        
		//create query
        $query = 'SELECT count(*) as count FROM user' ;

        //prepare statement       
        $stmt = oci_parse($this->conn, $query);

        //execute query
        oci_execute($stmt);
        
        $result = $stmt;

        $num = oci_fetch_all($result , $res);

        if($num > 0){
			
            $post_arr = array();
			
			for ($i = 0; $i < $num; $i++){				
				$a = $res['COUNT'][$i];
			}
			
            /*while($row = $result->fetch(PDO::FETCH_ASSOC)){
                extract($row);               
                $a = $count;
            }*/
        }
        return $a;
    }
	
	
    public function create(){
        
		//create query
        $query = 'INSERT INTO ' . $this->table . ' (user_id, time, lat, lng, upload_id) VALUES (:user_id , :time , :lat, :lng, :upload_id) RETURNING id INTO :id';
		
		//prepare statement
        $stmt = oci_parse($this->conn, $query);
        
		//clean data
        $this->user_id   = htmlspecialchars(strip_tags($this->user_id));
        $this->time      = htmlspecialchars(strip_tags($this->time));
        $this->lat       = htmlspecialchars(strip_tags($this->lat));
        $this->lng       = htmlspecialchars(strip_tags($this->lng));
        $this->upload_id = htmlspecialchars(strip_tags($this->upload_id));

        //binding of parameters
		oci_bind_by_name($stmt, ':user_id', $this->user_id);
		oci_bind_by_name($stmt, ':time', $this->time);
		oci_bind_by_name($stmt, ':lat', $this->lat);
		oci_bind_by_name($stmt, ':lng', $this->lng);
		oci_bind_by_name($stmt, ':upload_id', $this->upload_id);
		oci_bind_by_name($stmt, ':id', $last_id,8);
           
        //execute the query
        if(oci_execute($stmt)){
			oci_commit($this->conn);
            return $last_id;
        }
        
		//print error if something goes wrong
		$e = oci_error($stmt);
        printf("Error %s. \n". $e['message']);
        return -1;
    }
    

    public function getContactTrackData($contact_id){
        
		$post_arr = array();
        
		$query = 'SELECT track_data.user_id, track_data.lat, track_data.lng, track_data.time 
				    FROM bluetooth_contact, track_data, users
				   WHERE track_data.id = bluetooth_contact.track_id
				     AND track_data.user_id = users.id
					 AND users.status = 2
					 AND bluetooth_contact.contact_id = :contact_id
				   ORDER BY track_data.user_id, track_data.time ASC';

        $stmt = oci_parse($this->conn, $query);
                
        $contact_id = htmlspecialchars(strip_tags($contact_id));
 
        oci_bind_by_name($stmt, ':contact_id', $contact_id);
    
        oci_execute($stmt);

        $result = $stmt;
		
		$num = oci_fetch_all($result , $res);

        if($num > 0){
            
			$post_arr = array();
			
			for ($i = 0; $i < $num; $i++){
				$post_item = array(
                    'user_id' => $res['USER_ID'][$i],
                    'lat' => $res['LAT'][$i],
                    'lng' => $res['LNG'][$i],
                    'time' => $res['TIME'][$i],
					'duration' => 0
                    );
				array_push($post_arr, $post_item);
			}
        }
		
		$startTime = 0;
        $previousTime = 0;
        $track_results['contact_point'] = array();
        $startItem = array();
		
        if(count($post_arr) > 0){
			
            $startItem = $post_arr[0];
            $startTime = $startItem['time'];

            for($i = 1; $i < count($post_arr); $i++){
				
                $curItem = $post_arr[$i];
                if($startItem['user_id'] == $curItem['user_id']){
                    $curTime = $curItem['time'];
                    $previousTime = $post_arr[$i-1]['time'];
                    $duration = $curTime - $startTime;
                }
				
                if($startItem['user_id'] != $curItem['user_id'] || $duration >= 3600000 || $i == (count($post_arr)-1)){
                   $startItem['duration'] =  $previousTime - $startTime;
                   array_push($track_results['contact_point'], $startItem);
                   $startItem = $curItem;
                   $startTime = $startItem['time'];

                }
            }
        }
		
        echo json_encode($track_results);          
    }
}
?>