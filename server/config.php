<?php
error_reporting(E_ALL);
session_start();

define("DEBUG", "0");
define("DEBUG_LOGGER", "tmp/att-php.log");
require("dBug.php");


ini_set("memory_limit","12M");
$fh = fopen("tmp/log.txt","a+");
fwrite($fh,"in config\n");
fclose($fh);

# The root URL starts off the Sencha Touch application. On the desktop, any Webkit browser
# will work, such as Google Chrome or Apple Safari. It's best to use desktop browsers
# when developing and debugging your application, due to the superior developer tools, such
# as the Web Inspector.

# Set up the ATT library with the Client application ID and secret. These have been
# given to you when you registered your application on the AT&T Developer site.
$provider = ProviderFactory::init(array(

    "provider"          => "att",

    # apiKey and secretKey are from AT&T Dev Connect.
    # localServer is the address of the locally running server.
    #   This is used when a callback URL is
    #   required when making a request to the AT&T APIs.
    # shortCode is the SMS number used when sending and receiving SMS and MMS messages.
    # apiHost is the main endpoint through which all API requests are made.
    # clientModelScope is the string of api scopes your application wants access to.


    "apiKey"            => "4d95839555684dae0773c84f8d401112",
    "secretKey"         => "46962d961aa0984b",
    "localServer"       => "http://www.bananecom/baconunicorn/server",
    "shortCode"         => "80712765",
    "apiHost"           => "https://api.att.com",
    "clientModelScope"  => "SMS,MMS"

));
new dBug( $class);
new dBug($provider);


function __autoload($class) {
  require_once("lib/service_provider/$class.php");
}


#Minimal HTML page to wrap the postMessage to the parent during iframe, redirects in OAuth and Payment.
define("REDIRECT_HTML_PRE", "<!DOCTYPE html><html><head><script>window.parent.postMessage('");

define("REDIRECT_HTML_POST", "', '*');</script></head><body></body></html>");



?>