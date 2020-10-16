<?php 
class API
	{
		private $Version = "1.0";
		public $conn,$oid,$name,$comment;


		function __construct()
		{
			

			$this->conn = mysqli_connect("localhost","rt327","rt327","rt327_annotationsdb");

			if(!$this->conn){
				 Consolelog("Connection Failed: ". mysqli_connect_error());
				 http_response_code(500);
				 header("HTTP/1.1 500 - Internal server error (e.g. database connection failed)");
			}
		}

		function __destruct()
		{
			mysqli_close($this->conn);
		}

		public function handleRequest(){
				switch($_SERVER['REQUEST_METHOD'])
				{	case "POST":$this->create(); break;
					case "GET": $this->read(); break;
					case "PUT": $this->update(); break;
					case "DELETE": $this->delete(); break;
					default: 
						Consolelog("No Request Method");
					break;
				}
			}	

		

		function create(){	
			if(isset($_POST['oid'])&&(isset($_POST['comment']))){
				$this->oid =urlencode($_POST['oid']);
				$this->commment= urlencode($_POST['comment']);
				$this->name= urlencode($_POST['name']);
				if($this->CheckData($this->oid,$this->name)){
					$sql ="INSERT INTO `maintable`(`oid`, `name`, `comment`) VALUES ('".$this->oid."','".$this->name."','".$this->comment."')";
					if($result=mysqli_query($this->conn,$sql)){
						$LAST_ID = mysqli_insert_id($this->conn);
						$this->html = json_encode(array("id"=>$LAST_ID),JSON_PRETTY_PRINT);
						http_response_code(201);
						header('Content-Type: application/json');
					}
				}
			}
		}

		function read(){	
			if(isset($_GET['oid'])&&($_GET['oid']<33)){
				$this->oid = urlencode($_GET['oid']);
				$sql = "SELECT `name`,`comment`FROM `maintable` WHERE `oid` = '".$this->oid."' LIMIT 0 , 30";
				$result = mysqli_query($this->conn,$sql);
				if(!($result==null)){

					while ($row = mysqli_fetch_assoc($result)){
            			$array[]=$row;
           			}
           			$mainArray =["version"=>$this->Version,"notes"=>$array];       
					$this->html = json_encode($mainArray,JSON_PRETTY_PRINT);
					http_response_code(200);
					header('Content-Type: application/json');
				} else {
					http_response_code(204);
				}
			}
			else {
				ConsoleLog("Data Invalid");
				http_response_code(400);
			}
		}

		function update(){ConsoleLog("update");}

		function delete(){ConsoleLog("delete");}

		function CheckData($O,$N){
		    $hold = false;
		    $Ocheck = false;
		    $Ncheck = false;
		   

		    if(strlen($O)<=32){$Ocheck = true;}

		    if(strlen($N)<=64){$Ncheck = true;}

		    if($Ocheck&&$Ncheck){$hold = true;}

		 	return  $hold;  
		}
	}

	function Wrap($str,$tag){
		return "<".$tag.">".$str."</".$tag.">";
	}

	function ConsoleLog($str){
		echo Wrap("console.log(\"".$str."\")","script");
	}

	$API = new API();
	$API->handleRequest();
	if((http_response_code()==200) || (http_response_code() == 201)){echo $API->html;}
?>