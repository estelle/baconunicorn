<?php

require_once("../config.php");

if ($_GET['code']) {

    # Once the user has logged-in with their credentials, they get re-directed to this URL
    # with a 'code' parameter. This is exchanged for an access token which can be used in any
    # future calls to the AT&T APIs

    $code = trim($_GET['code']);

    # First, make sure to send the correct content-type
    header('Content-Type: text/html');

    if (!$code) {

        echo  REDIRECT_HTML_PRE . '{"success": false,"msg": "No auth code "}'. REDIRECT_HTML_POST;

    } else {

        $response = $provider->getToken($code);

        if ($response->isError()) {

            echo  REDIRECT_HTML_PRE . '{"success": false,"msg": "Could not fetch auth token"}'. REDIRECT_HTML_POST;

        } else {

            # Store the auth token in the session for use in future API calls
            $_SESSION['token'] = $response->data()->access_token;
            $_SESSION['refresh_token'] = $response->data()->refresh_token;

            echo  REDIRECT_HTML_PRE . '{"success": true,"msg": "Process Callback"}' . REDIRECT_HTML_POST;

        }
    }
}
?>
