<?php

require_once("../config.php");

$bool = "false";
if($_SESSION['token']) {
	$bool = "true";
}
echo "{\"authorized\": " . $bool . "}";

?>
