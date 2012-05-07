<?php
session_start();
require("../server/dBug.php");
include ("config.php");
include ($oauth_file);
    
$subject = "80712765";

new dBug($_REQUEST);
new dBug($_SESSION);
?>
<html>
<body>
<form method="post" name="sendMMS" enctype="multipart/form-data">
<button type="submit" name="sendMMS" value="Post Photo">Post Photo</button></td>
</form>
<?php

if (!empty($_REQUEST["sendMMS"])) {

	$fullToken["accessToken"]	=	$accessToken;
	$fullToken["refreshToken"]	=	$refreshToken;
	$fullToken["refreshTime"]	=	$refreshTime;
	$fullToken["updateTime"]	=	$updateTime;
	
	$fullToken = check_token($FQDN,$api_key,$secret_key,$scope,$fullToken,$oauth_file);

	$accessToken	=	$fullToken["accessToken"];
	new dBug($accessToken);

	/* just put in static short code */
	$addresses_url = "Address=tel:4157108526&";
	if ( $addresses_url != "" ){

	$server=substr($FQDN,8);
       
	$host="ssl://$server";
	$port="443";
	$fp = fsockopen($host, $port, $errno, $errstr);

	if (!$fp) {
	    echo "with fp errno: $errno \n";
	    echo "errstr: $errstr\n";
	    return $result;
	}
	new dBug($addresses_url);
	//Boundary for MIME part
      $boundary = "----------------------------".substr(md5(date("c")),0,10);

        //Form the first part of MIME body containing address, subject in urlencided format
      $sendMMSData = $addresses_url.'Subject='.urlencode($subject);

	//Form the MIME part with MIME message headers and MIME attachment
      $data = "";
      $data .= "--$boundary\r\n";
      $data .= "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\nContent-Disposition: form-data; name=\"root-fields\"\r\nContent-ID: <startpart>\r\n\r\n".$sendMMSData."\r\n";
      $data .= "--$boundary\r\n";
      $data .= "Content-Disposition: attachment; name=\"coupon.jpg\"\r\n";
      $data .= "Content-Type:image/png\r\n";
      $data .= "Content-ID: <share_image.png>\r\n";
      $data .= "Content-Transfer-Encoding: binary\r\n\r\n";
      $data .= join("", file("share_image.png"))."\r\n";
      $data .= "--$boundary--\r\n";

	// Form the HTTP headers
	$header = "POST $FQDN/rest/mms/2/messaging/outbox?access_token=".$accessToken." HTTP/1.0\r\n";
	$header .= "Content-type: multipart/form-data; type=\"application/x-www-form-urlencoded\"; start=\"<startpart>\"; boundary=\"$boundary\"\r\n";
	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Host: $server\r\n";
	$dc = strlen($data); //content length
	$header .= "Content-length: $dc\r\n\r\n";

	$httpRequest = $header.$data;
	fputs($fp, $httpRequest);


	$sendMMS_response="";
	while(!feof($fp)) {
	    $sendMMS_response .= fread($fp,1024);
	}
	fclose($fp);
	
	new dBug($sendMMS_response);	
	
	$responseCode=trim(substr($sendMMS_response,9,4));//get the response code.
	new dBug($responseCode);

	/*
	  If URL invocation is successful print the mms ID,
	  else print the error msg
	*/
	if($responseCode>=200 && $responseCode<=300)
	{
	    $splitString=explode("{",$sendMMS_response);
	    $joinString="{".implode("{",array($splitString[1],$splitString[2]));
	    $jsonObj = json_decode($joinString,true);
	    $msghead="Message Id";
	    $mmsID=$jsonObj['Id']; 
	    $_SESSION["mms2_mmsID"] = $mmsID;

	    ?>
	      <div class="successWide">
		 <strong>SUCCESS:</strong><br />
		 Message ID <?php echo $mmsID; ?>
		 </div><?php

	} else {
	  $_SESSION["mms2_mmsID"] = null;
	    //print "The Request was Not Successful";
	    ?>
	    <div class="errorWide">
	    <strong>ERROR in mms response:</strong><br />
	    <?php echo $sendMMS_response;  ?>
	    </div>

<?php 
	}
  }
      if(!empty($invalid_addresses )){
	$_SESSION["mms2_mmsID"] = null;
	?>
	<div class="errorWide">
	<strong>ERROR: Invalid numbers</strong><br />
	<?php 
	foreach ( $invalid_addresses as $invalid_address ){
	  echo $invalid_address."<br/>";
	}  
	?>
	</div>
	<?php 
      }
}
?>

</body></html>

<?php

function RefreshToken($FQDN,$api_key,$secret_key,$scope,$fullToken){

  $refreshToken=$fullToken["refreshToken"];
  $accessTok_Url = $FQDN."/oauth/token";

  //http header values
  $accessTok_headers = array(
			     'Content-Type: application/x-www-form-urlencoded'			     );

  //Invoke the URL
  $post_data="client_id=".$api_key."&client_secret=".$secret_key."&refresh_token=".$refreshToken."&grant_type=refresh_token";

  $accessTok = curl_init();
  curl_setopt($accessTok, CURLOPT_URL, $accessTok_Url);
  curl_setopt($accessTok, CURLOPT_HTTPGET, 1);
  curl_setopt($accessTok, CURLOPT_HEADER, 0);
  curl_setopt($accessTok, CURLINFO_HEADER_OUT, 0);
  //curl_setopt($accessTok, CURLOPT_HTTPHEADER, $accessTok_headers);
  curl_setopt($accessTok, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($accessTok, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($accessTok, CURLOPT_POST, 1);
  curl_setopt($accessTok, CURLOPT_POSTFIELDS,$post_data);
  $accessTok_response = curl_exec($accessTok);
  $currentTime=time();

  $responseCode=curl_getinfo($accessTok,CURLINFO_HTTP_CODE);
  if($responseCode==200){
    $jsonObj = json_decode($accessTok_response);
    $accessToken = $jsonObj->{'access_token'};//fetch the access token from the response.
    $refreshToken = $jsonObj->{'refresh_token'};
    $expiresIn = $jsonObj->{'expires_in'};

    $refreshTime=$currentTime+(int)($expiresIn); // Time for token refresh
    $updateTime=$currentTime + ( 24*60*60); // Time to get for a new token update, current time + 24h 

    $fullToken["accessToken"]=$accessToken;
    $fullToken["refreshToken"]=$refreshToken;
    $fullToken["refreshTime"]=$refreshTime;
    $fullToken["updateTime"]=$updateTime;
                        
  }
  else{
    $fullToken["accessToken"]=null;
    $fullToken["errorMessage"]=curl_error($accessTok).$accessTok_response;


  }
  curl_close ($accessTok);
  return $fullToken;

}
function GetAccessToken($FQDN,$api_key,$secret_key,$scope){

  $accessTok_Url = $FQDN."/oauth/token";

  //http header values
  $accessTok_headers = array(
			     'Content-Type: application/x-www-form-urlencoded'
			     );

  //Invoke the URL
  $post_data = "client_id=".$api_key."&client_secret=".$secret_key."&scope=".$scope."&grant_type=client_credentials";

  $accessTok = curl_init();
  curl_setopt($accessTok, CURLOPT_URL, $accessTok_Url);
  curl_setopt($accessTok, CURLOPT_HTTPGET, 1);
  curl_setopt($accessTok, CURLOPT_HEADER, 0);
  curl_setopt($accessTok, CURLINFO_HEADER_OUT, 0);
  //  curl_setopt($accessTok, CURLOPT_HTTPHEADER, $accessTok_headers);
  curl_setopt($accessTok, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($accessTok, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($accessTok, CURLOPT_POST, 1);
  curl_setopt($accessTok, CURLOPT_POSTFIELDS,$post_data);
  $accessTok_response = curl_exec($accessTok);
  
  $responseCode=curl_getinfo($accessTok,CURLINFO_HTTP_CODE);
  $currentTime=time();
  /*
   If URL invocation is successful fetch the access token and store it in session,
   else display the error.
  */
  if($responseCode==200)
    {
      $jsonObj = json_decode($accessTok_response);
      $accessToken = $jsonObj->{'access_token'};//fetch the access token from the response.
      $refreshToken = $jsonObj->{'refresh_token'};
      $expiresIn = $jsonObj->{'expires_in'};

      $refreshTime=$currentTime+(int)($expiresIn); // Time for token refresh
      $updateTime=$currentTime + ( 24*60*60); // Time to get for a new token update, current time + 24h

      $fullToken["accessToken"]=$accessToken;
      $fullToken["refreshToken"]=$refreshToken;
      $fullToken["refreshTime"]=$refreshTime;
      $fullToken["updateTime"]=$updateTime;
      
    }else{
 
    $fullToken["accessToken"]=null;
    $fullToken["errorMessage"]=curl_error($accessTok).$accessTok_response;

  }
  curl_close ($accessTok);
  return $fullToken;
}
function SaveToken( $fullToken,$oauth_file ){

  $accessToken=$fullToken["accessToken"];
  $refreshToken=$fullToken["refreshToken"];
  $refreshTime=$fullToken["refreshTime"];
  $updateTime=$fullToken["updateTime"];
      

  $tokenfile = $oauth_file;
  $fh = fopen($tokenfile, 'w');
  $tokenfile="<?php \$accessToken=\"".$accessToken."\"; \$refreshToken=\"".$refreshToken."\"; \$refreshTime=".$refreshTime."; \$updateTime=".$updateTime."; ?>";
  fwrite($fh,$tokenfile);
  fclose($fh);
}

function check_token( $FQDN,$api_key,$secret_key,$scope, $fullToken,$oauth_file){

  $currentTime=time();

  if ( ($fullToken["updateTime"] == null) || ($fullToken["updateTime"] <= $currentTime)){
    $fullToken=GetAccessToken($FQDN,$api_key,$secret_key,$scope);
    if(  $fullToken["accessToken"] == null ){
      //      echo $fullToken["errorMessage"];
    }else{
      //      echo $fullToken["accessToken"];
      SaveToken( $fullToken,$oauth_file );
    }
  }
  elseif ($fullToken["refreshTime"]<= $currentTime){
    $fullToken=RefreshToken($FQDN,$api_key,$secret_key,$scope, $fullToken);
    if(  $fullToken["accessToken"] == null ){
      //      echo $fullToken["errorMessage"];
    }else{
      //      echo $fullToken["accessToken"];
      SaveToken( $fullToken,$oauth_file );
    }
  }
  
  return $fullToken;
  
}

?>
