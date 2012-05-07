<?php
error_reporting(E_ALL);
require("dBug.php");

/* accepts json encoded mms from at & t */
echo "starting...<br>";
new dBug($_REQUEST);

$fh = fopen("tmp/receivingmms.log","a+");
$dateTime = date('Y/m/d G:i:s');
fwrite($fh,"\n\n***** $dateTime getting post\n");

if(isset($_REQUEST)){
	$poststring = implode("\n",$_REQUEST);
	fwrite($fh,"poststring: ".$poststring."\n");
	foreach($_REQUEST as $key=>$value){
		fwrite($fh, "key: $key, value: $value\n");
		
	}

} else {
	fwrite($fh, "no request obj\n\n");
}



fclose($fh);
?>
