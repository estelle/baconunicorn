<?php
/*
	this script's path should be entered in the app definition of at&t dev portal
	i.e. banane.com/baconunciorn/server/receiveMMS.php
	
	to implement,
		1. create write-able log in tmp/receivingmms.log
		2. create writeable gallery.json in sencha's data/assets/gallery dir.
		3. grab test data file test_mms.txt and un-comment (will make life lots easier)
		4. test by going direct to server/receiveMMS.php, will print out json_message, then check out the client
		"client/"
		should display the image in test object (a blue tree)
		
		That should be it- feel free to disable the writing to the MoMmsImages dir, 
		but found it useful to debug.
	-ab mon may 7th
*/
   
require("dBug.php");
/* great debug lib for PHP, available on this domain -ab mon may 7th */

/* make sure to make writeable on local sys */
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
				
				/* 
					ab mon may 7th
					bet you're wondering why this is here... the content type is looped through earlier,
					and this is where at&t put this filename stuff. thing is, it's a long string
					and badly pattern matched, and doesn't write to o/s well. 
					I wrote a new one ("@Filename=: ((.)*\;))) but while it printed out, wasn't sure why didn't work with other processes.
					Instead, pop into array delimited by spaces
					and grab first element. Should probably tighten up reg-ex's instead, of course.
				
				*/
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
 }else{
    $messages = array($message);
 }

  /* ab  mon may 7th
  	do same for json client side - pop into array, stringify, overwrite old db file
  	would be better w/ a real db
  */
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
