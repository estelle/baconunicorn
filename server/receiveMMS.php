<?php
   
require("dBug.php");
$fh = fopen("tmp/receivingmms.log","a+");
$dateTime = date('Y/m/d G:i:s');
fwrite($fh,"\n\n***** $dateTime getting post\n");

/* json/client file dirs */
$client_data_dir = "/home/ba/banane.com/html/baconunicorn/client/assets/data/";
$client_data_dir_images = $client_data_dir ."gallery/images/";
$webdir = "images/";
$json_file = $client_data_dir . "gallery.json";
$json_db = json_decode(file_get_contents($json_file));

   
$path_is = __FILE__;
$folder = dirname($path_is);
$folder = $folder. "/MoMessages";
if(!is_dir($folder))
  {
    exit();
  }
$db_filename = $folder . "/". "mmslistner.db";
$post_body = file_get_contents('php://input');
//$post_body = file_get_contents( "test_mms.txt");
fwrite($fh, $post_body."\n\n");

if ( file_exists( $db_filename) ){
  $messages = unserialize(file_get_contents($db_filename)); 
 }else{
  $messages = null;
 }

$local_post_body = $post_body;
$ini = strpos($local_post_body,"<SenderAddress>tel:+");
if ($ini == 0 )
  {
    exit();
  }else{
  preg_match("@<SenderAddress>tel:(.*)</SenderAddress>@i",$local_post_body,$matches);
  $message["address"] = $matches[1];
  preg_match("@<subject>(.*)</subject>@i",$local_post_body,$matches);
  $message["subject"] = $matches[1];
  $message["date"]= date("D M j G:i:s T Y");
  
	/* do json version */
  $json_message["senderAddress"] = $message["address"];
  $json_message["date"] = $message["date"];
  $json_message["text"] = $message["subject"];
 }

if( $messages !=null ){
  $last=end($messages);
  $message['id']=$last['id']+1;
 }else{
    $message['id'] = 0;
 }

mkdir($folder.'/'.$message['id']);

$boundaries_parts = explode("--Nokia-mm-messageHandler-BoUnDaRy",$local_post_body);

foreach ( $boundaries_parts as $mime_part ){
  if ( preg_match( "@BASE64@",$mime_part )){
    $mm_part = explode("BASE64", $mime_part );
    $filename = null;
    $content_type =null;
    if ( preg_match("@Filename=([^;^\n]+)@i",$mm_part[0],$matches)){
      $filename = trim($matches[1]);
    }
    if ( preg_match("@Content-Type:([^;^\n]+)@i",$mm_part[0],$matches)){
      $content_type = trim($matches[1]);
    }
    if ( $content_type != null ){
      if ( $filename == null ){
				preg_match("@Content-ID: ([^;^\n]+)@i",$mm_part[0],$matches);
				$filename = trim($matches[1]);    
      }
      if ( $filename != null ){
				//Save file 
				if($content_type == "image/jpeg" || $content_type == "image/png"){
					new dBug($filename);
					new dBug($json_full_filename);
					$fileA = split(" ",$filename);
					new dBug($fileA);
					$filename = $fileA[0];
				}

				$base64_data = base64_decode($mm_part[1]);
				$full_filename = $folder.'/'.$message['id'].'/'.$filename;
				$json_full_filename = $client_data_dir_images . $filename;

				if (!$file_handle = fopen($full_filename, 'w')) {
					echo "Cannot open file ($full_filename)";
					exit;
				}
				$json_file_handle = fopen($json_full_filename, 'w');
				
				fwrite($file_handle, $base64_data);
				fclose($file_handle);

				fwrite($json_file_handle, $base64_data);
				fclose($json_file_handle);
	
				if ( preg_match( "@image@",$content_type ) && ( !isset($message["image"]))){
					$message["image"]=$message['id'].'/'.$filename;
					$json_message["path"] = $webdir . $filename;
				}
				if ( preg_match( "@text@",$content_type ) && ( !isset($message["text"]))){
					$message["text"]=$message['id'].'/'.$filename;
					// not interested json-wise
					
				}
      }
    }
  }
}

if( $messages !=null ){
  $messages_stored=array_push($messages,$message);
  if ( $messages_stored > 10 ){
    $old_message = array_shift($messages);
    // remove old message folder 
  }
  
    /* ab  mon may 7th
  	do same for json client side - pop into array, stringify, overwrite old db file
  	would be better w/ a real db
  */

  
 }else{
    $messages = array($message);
 }

if($json_message != null){
  new dBug($json_message);
  fwrite($fh,"in write to db\n");
  array_push($json_db->imageList,$json_message);
  $json_db->totalNumberOfImagesSent = count($json_db->imageList);
  
  $json_db_str = json_encode($json_db);
  $jsf = fopen($json_file, "w");
  fwrite($jsf,$json_db_str);
  fclose($jsf);
}

$fp = fopen($db_filename, 'w+') or die("I could not open $filename.");
fwrite($fp, serialize($messages));
fclose($fp);
//print_r($messages);


?>
