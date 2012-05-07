 <?php
 require("dBug.php");

$fh = fopen("tmp/receivingmms.log","a+");
$dateTime = date('Y/m/d G:i:s');
fwrite($fh,"\n\n***** $dateTime getting post\n");



$path_is = __FILE__;
$folder = dirname($path_is);
$folder = $folder. "/MoMessages";
if(!is_dir($folder))
  {
    echo "MoMessages folder is missing";
    exit();
  }
$db_filename = $folder . "/". "mmslistner.db";

$client_data_dir = "/home/ba/banane.com/html/baconunicorn/client/assets/data/";
$client_data_dir_images = $client_data_dir ."gallery/images/";
$webdir = "assets/data/gallery/images/";
$json_file = $client_data_dir . "gallery.json";
$json_db = json_decode(file_get_contents($json_file));
new dBug($json_db);

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
  	echo "No sender";
  	fwrite($fh,"no sender\n\n");
    exit();
  }else{
  preg_match("@<SenderAddress>tel:(.*)</SenderAddress>@i",$local_post_body,$matches);
  $message["address"] = $matches[1];
  preg_match("@<subject>(.*)</subject>@i",$local_post_body,$matches);
  $message["subject"] = $matches[1];
  $message["date"]= date("D M j G:i:s T Y");
  echo "yes sender and message";
  fwrite($fh,"sender & message");
  
  $json_message["senderAddress"] = $message["address"];
  $json_message["date"] = $message["date"];
  $json_message["text"] = $message["subject"];
/*
	"path":"MoMmsImages/4444948.jpeg",
	"senderAddress":"8588228604",
	"date":"11/30/11, 2:54:08 PM PST",
	"text":"This is a leaf."
	*/
 }

/*if( $messages !=null ){
  $last=end($messages);
  $message['id']=$last['id']+1;
 }else{
    $message['id'] = 0;
 }*/

//mkdir($folder.'/'.$message['id']);
$boundaries_parts = explode("--Nokia-mm-messageHandler-BoUnDaRy",$local_post_body);


foreach ( $boundaries_parts as $mime_part ){
  if ( preg_match( "@BASE64@",$mime_part )){
    $mm_part = explode("BASE64", $mime_part );
    $filename = null;
    $content_type =null;
    if ( preg_match("@Filename=(.*)\;@i",$mm_part[0],$matches)){
      $filename = trim($matches[1]);
    }
    if ( preg_match("@Content-Type:(.*)\;@i",$mm_part[0],$matches)){
      $content_type = trim($matches[1]);
    }
    if ( $content_type != null ){
      if ( $filename == null ){
				preg_match("@Content-ID: ([^;^\n]+)@i",$mm_part[0],$matches);
				$filename = trim($matches[1]);    
      }
      if ( $filename != null ){
				//Save file 
				echo "the filename: ";
				new dBug($filename);
				fwrite($fh, "the filename: $filename\n\n");
			
				$base64_data = base64_decode($mm_part[1]);
				$json_full_filename = $client_data_dir_images . $filename;
			
		/*		if (!$file_handle = fopen($full_filename, 'w')) {
					echo "Cannot open file ($full_filename)";
					exit;
				}*/
				if (!$json_fh = fopen($json_full_filename, 'w')) {
					echo "Cannot open file ($full_filename)";
					fwrite($fh, "the filename: $full_filename\n\n");

					exit;
				}
			
//				fwrite($file_handle, $base64_data);
				fwrite($json_fh, $base64_data);
				
//				fclose($file_handle);
				fclose($json_fh);
			
				if ( preg_match( "@image@",$content_type ) && ( !isset($message["image"]))){
					$message["image"]=$message['id'].'/'.$filename;
					
					
					$json_message["path"] = $webdir.$filename;
				}
				if ( preg_match( "@text@",$content_type ) && ( !isset($message["text"]))){
					$message["text"]=$message['id'].'/'.$filename;
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
  new dBug($json_message);
  fwrite($fh,"the json file: ".implode("\n",$json_message)."\n");
  array_push($json_db->imageList,$json_message);
  $json_db_str = json_encode($json_db);
  $jsf = fopen($json_file, "w");
  fwrite($jsf,$json_db_str);
  fclose($jsf);
 }else{
    $messages = array($message);
 }


fclose($fh);

?>
