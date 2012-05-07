<?php

    function exception_handler($exception) {
        error_log("Fatal error: " . $exception->getMessage());
    }
    set_exception_handler("exception_handler");

    /**
     * The Sencha_ServiceProvider_Base_Att class.
     *
     * This class provides reusable and extend-able server code written in PHP. The SDK server takes requests from the client side Att.Provider object and maps them to the corresponding server side method which takes care of sending the requests to the AT&T API Platform.
     *
     * You can create an instance directly like this:
     *
     *      $provider = new Sencha_ServiceProvider_Base_Att(array(
     *          "apiKey"            => "XXXXXX",
     *          "secretKey"         => "XXXXXX",
     *          "localServer"       => "http://127.0.0.1:8888",
     *          "shortCode"         => "XXXXXX",
     *          "apiHost"           => "https://api.att.com",
     *          "clientModelScope"  => "WAP,SMS,MMS,PAYMENT"
     *      ));
     *
     * You can also create an instance using the ProviderFactory class' init factory method.  Just make sure you also include the **provider** property and set it to **att**.
     *
     *      $provider = ProviderFactory::init(array(
     *          "provider"          => "att",
     *          "apiKey"            => "XXXXXX",
     *          "secretKey"         => "XXXXXX",
     *          "localServer"       => "http://127.0.0.1:8888",
     *          "shortCode"         => "XXXXXX",
     *          "apiHost"           => "https://api.att.com",
     *          "clientModelScope"  => "WAP,SMS,MMS,PAYMENT"
     *      ));
     *
     *
     * @class Sencha.ServiceProvider.Base.Att
     * @extends Base
     *
     * @cfg {String} apiKey The apiKey generated when creating an app in the AT&T Dev Connect portal.
     * @cfg {String} secretKey The secretKey generated when creating an app in the AT&T Dev Connect portal.
     * @cfg {String} localServer The url of the locally running server that is used to build the callback urls.
     * @cfg {String} apiHost The url endpoint through which all AT&T API requests are made.
     * @cfg {String} shortCode The shortCode generated when creating an app in the AT&T Dev Connect portal.
     * @cfg {String} clientModelScope The list of scopes that the application wants to gain access to when making API calls that use Autonomous Client.
     */
    class Sencha_ServiceProvider_Base_Att extends Base {

        private $client_id = "";
        private $client_secret = "";
        private $local_server = "";
        private $base_url = "";
        private $shortcode = "";
        private $clientModelScope = "";


        public function __construct($config) {

            if (!$config['apiKey']) throw new Exception("apiKey must be set");
            if (!$config['secretKey']) throw new Exception("secretKey must be set");
            if (!$config['localServer']) throw new Exception("localServer must be set");
            if (!$config['apiHost']) throw new Exception("apiHost must be set");
            if (!$config['shortCode']) throw new Exception("shortcode must be set");

            $this->client_id = $config['apiKey'];
            $this->client_secret = $config['secretKey'];
            $this->local_server = $config['localServer'];
            $this->base_url = $config['apiHost'];
            $this->shortcode = $config['shortCode'];
            $this->clientModelScope = $config['clientModelScope'];

            if (DEBUG) {
                Debug::init();
                Debug::write("\nAT&T Provider initialized.\n");
                Debug::dumpBacktrace();
                Debug::write("API endpoint: $this->base_url\nClient ID: $this->client_id\nClient Secret: $this->client_secret\nLocal Server: $this->local_server\nShortcode: $this->shortcode\n\n");
                Debug::end();
            }
        }

        /**
         * Generate the oauth redirect URL depending on the scope requested by the client.
         * The scope is specified as a parameter in the GET request and passed to the provider
         * library to obtain the appropriate OAuth URL
         *
         * @param {String} scope a comma separated list of services that teh app requires access to
         *
         * @method oauthUrl
         *
         */
        public function oauthUrl($scope) {
            if (is_array($scope)) {
                // $scope will be an array when called by the direct router
                $scope = $scope[0];
            }
            return "$this->base_url/oauth/authorize?scope=$scope&client_id={$this->client_id}&redirect_uri={$this->local_server}/att/callback";
        }

       /**
        * Retrieves an access token from AT&T once the user has authorized the application and has returned with an auth code
        *
        * @param {String} code The code
        *
        * @method getToken
        */
        public function getToken($code) {
            $url = "$this->base_url/oauth/access_token";
            $data = "grant_type=authorization_code&client_id={$this->client_id}&client_secret={$this->client_secret}&code=$code";
            return $this->form_post($url, $data);
        }


        /**
         * Refreshes an access token from AT&T, given a refresh token from a previous oAuth session
         *
         * @param {String} refresh_token The refresh token from a previous oAuth session
         *
         * @method refreshToken
         */
        public function refreshToken($refresh_token) {
            $url = "$this->base_url/oauth/access_token";
            $data = "grant_type=refresh_token&client_id={$this->client_id}&client_secret={$this->client_secret}&refresh_token=$refresh_token";
            return $this->form_post($url, $data);
        }


        /**
         *
         * Return information on a device
         *
         * @param {Array} data An array of Device Info options. Options should include:
         * @param {String} data.0 (token) The oAuth access token
         * @param {String} data.1 (tel) MSISDN of the device to query
         *
         * @method deviceInfo
         *
         */
        public function deviceInfo($data) {
            return $this->json_get("$this->base_url/1/devices/tel:$data[1]/info?access_token=$data[0]");
        }


        /**
         * Retrieves a client token from AT&T
         *
         * @method getClientCredentials
         */
        public function getClientCredentials() {
            error_log("GET NEW CLIENT_TOKEN FROM AT&T");
            error_log("API endpoint: $this->base_url");
            error_log("Client ID: $this->client_id");
            error_log("Client Secret: $this->client_secret");
            error_log("Local Server: $this->local_server");
            error_log("Shortcode: $this->shortcode");

            $url = "$this->base_url/oauth/access_token";
            $data = "grant_type=client_credentials&client_id={$this->client_id}&client_secret={$this->client_secret}&scope={$this->clientModelScope}";
            return $this->form_post($url, $data);
        }

        /**
         * Retrieves the current client token from the user's session.
         * If the client token does not exist in the session, a call
         * to getClientCredentials is made in order to get a new one
         * from AT&T
         *
         * @method getCurrentClientToken
         */
        public function getCurrentClientToken() {
            if(isset($_SESSION['client_token'])) {
                error_Log( "Checking for client_token in Session");
                $token =  $_SESSION['client_token'];
                error_Log(  "session client_token = " . $token);
            } else {
                error_Log( "No client_token in Session so fetching new client_token");
                $token = $this->getClientCredentials()->data()->access_token;
                error_Log(  "fetched new client_token = " . $token);
                $_SESSION['client_token'] = $token;
            }
            return $token;
        }

        /**
         * Return location info for a device
         *
         * @param {Array} data An array of Device Info options, which should include:
         *   @param {String} data.0 (token) The oAuth access token
         *   @param {String} data.1 (tel) MSISDN of the device to locate
         *   @param {Number} data.2 (requestedAccuracy) The requested accuracy (optional)
         *   @param {Number} data.3 (acceptableAccuracy) The acceptable accuracy (optional)
         *   @param {Number} data.4 (tolerance) The tolerance (optional)
         *
         * @method deviceLocation
         */
        public function deviceLocation($data) {

            $url = "$this->base_url/1/devices/tel:$data[1]/location?access_token=$data[0]";

            if (intval($data[2]) > 0) {
                $url = $url . "&requestedAccuracy=$data[2]";
            }

            if (intval($data[3]) > 0) {
                $url = $url . "&acceptableAccuracy=$data[3]";
            }

            if (strlen($data[4]) > 0) {
                $url = $url . "&tolerance=$data[4]";
            }

            return $this->json_get($url);
        }


        /**
         * Sends an SMS to a recipient
         *
         * @param {Array} data An array of SMS options. Options should include:
         * @param {String} data.0 (token) The oAuth access token
         * @param {String} data.1 (tel) The MSISDN of the recipient(s). Can contain comma separated list for multiple recipients.
         * @param {String} data.2 (message) The text of the message to send
         *
         * @method sendSms
         */
        public function sendSms($data) {
            $address = $data[1];
            if (strstr($address, ",")) {
                // If it's csv, split and iterate over each value prepending each value with "tel:"
                $address = split(",", $address);
                foreach ($address as $key => $value) {
                    $address[$key] = "tel:$value";
                }
            } else {
                $address = "tel:$address";
            }

            return $this->json_post("$this->base_url/rest/sms/2/messaging/outbox?access_token=$data[0]", array("Address" => $address, "Message" => $data[2]));
        }


        /**
         * Check the status of a sent SMS
         *
         * @param {Array} data An array of SMS options, which should include:
         * @param {String} data.0 (token) The oAuth access token
         * @param {String} data.1 (tel) The unique SMS ID as retrieved from the response of the sendSms method
         *
         * @method smsStatus
         */
        public function smsStatus($data) {
            return $this->json_get("$this->base_url/rest/sms/2/messaging/outbox/$data[1]?access_token=$data[0]");
        }

        /**
         * Retrieves a list of SMSs sent to the application's short code
         *
         * @param {Array} data An array of SMS options, which should include:
         * @param {String} data.0 (token) The oAuth access token
         *
         * @method receiveSms
         */
        public function receiveSms($data) {
            return $this->json_get("$this->base_url/rest/sms/2/messaging/inbox?access_token=$data[0]&RegistrationID=$this->shortcode&from=json");
        }

        /**
         * requestChargeAuth
         *
         * @param {Array} data An array of charge options.
         * @param {String} data.0 (token) The oAuth access token
         * @param {String} data.1 (type) The charge type
         * @param {String} data.2 (paymentDetails) The paymentDetails
         *
         * @method requestChargeAuth
         */
        public function requestChargeAuth($data) {

            $type = $data[1];
            $paymentDetails = $data[2];

            if($type == "SUBSCRIPTION") {
                $type = "Subscriptions";
            }

            if($type == "SINGLEPAY") {
                $type = "Transactions";
            }


            $paymentDetails->MerchantPaymentRedirectUrl = "{$this->local_server}/att/payment";


            $signed = $this->signPayload($paymentDetails);

            //var_dump($signed);

            $doc = $signed->data()->SignedDocument;

            $sig = $signed->data()->Signature;

            //echo"sig $sig **";

            $url = "$this->base_url/Commerce/Payment/Rest/2/$type?clientid={$this->client_id}&Signature=$sig&SignedPaymentDetail=$doc";

            $results = $this->json_getHeaders($url);

            $temp = array();

            $temp["adviceOfChargeUrl"] = $results["Location"];

            return $temp;
        }

        /**
         * Sign a document
         *
         * @method signPayload
         */
        public function signPayload($toSign) {
            $url = "$this->base_url/Security/Notary/Rest/1/SignedPayload?&client_id={$this->client_id}&client_secret={$this->client_secret}";
            return $this->json_post($url, $toSign);
        }

        /**
         * Queries the status of a transaction
         *
         * @param {String} access_token The oAuth access token
         * @param {String} type The type of transaction (ie TransactionAuthCode, etc.)
         * @param {String} transaction_auth_code
         *
         * @method transactionStatus
         */
        public function transactionStatus($data) {
            $url = "$this->base_url/Commerce/Payment/Rest/2/Transactions/$data[1]/$data[2]?access_token=$data[0]";
            return $this->json_get($url);
        }

        /**
         * Queries the status of a subscription
         *
         * @param {String} access_token The oAuth access token
         * @param {String} type The type of transaction (ie TransactionAuthCode, etc.)
         * @param {String} id
         *
         * @method subscriptionStatus
         */
        public function subscriptionStatus($data) {
          $url = "$this->base_url/Commerce/Payment/Rest/2/Subscriptions/$data[1]/$data[2]?access_token=$data[0]";
          return $this->json_get($url);
        }

        /**
         * Issues a refund for a transaction
         *
         * @param {String} access_token The oAuth access token
         * @param {String} transaction_id The id of the transaction
         * @param {String} details The json data
         *
         * @method refundTransaction
         */
        public function refundTransaction($data) {
            $url = "$this->base_url/Commerce/Payment/Rest/2/Transactions/$data[1]?Action=refund&access_token=$data[0]";
            return $this->json_put($url, $data[2]);
        }

        /**
         * Retrieves the subscription details
         *
         * @param {String} access_token The oAuth access token
         * @param {String} merchant_subscription_id The merchant subscription id
         * @param {String} consumer_id The consumer id
         *
         * @method subscriptionDetails
         */
        public function subscriptionDetails($data) {
            $url = "$this->base_url/Commerce/Payment/Rest/2/Subscriptions/$data[1]/Detail/$data[2]?access_token=$data[0]";
            return $this->json_get($url);
        }

        /**
         * Retrieves a notification object
         *
         * @param {String} access_token The oAuth access token
         * @param {String} notification_id The notification id
         *
         * @method getNotification
         */
        public function getNotification($data) {
            $url = "$this->base_url/Commerce/Payment/Rest/2/Notifications/$data[1]?access_token=$data[0]";
            return $this->json_get($url);
        }

        /**
         * Stops the notification from being sent to the notification callback
         *
         * @param {String} access_token The oAuth access token
         * @param {String} notification_id The notification id
         *
         * @method acknowledgeNotification
         */
        public function acknowledgeNotification($data) {
            $url = "$this->base_url/Commerce/Payment/Rest/2/Notifications/$data[1]?access_token=$data[0]";
            return $this->json_put($url, null);
        }

        /**
         * Sends an MMS to a recipient
         *
         * MMS allows for the delivery of different file types. Please see the developer documentation for an updated list:
         *  https://developer.att.com/developer/tierNpage.jsp?passedItemId=2400428
         *
         * @param {String} $data[0] (access_token) The oAuth access token
         * @param {String} $data[1] (tel) Comma separated list of MSISDN of the recipients
         * @param {String} $data[2] (file_name) The name of the file, eg logo.jpg
         * @param {String} $data[3] (subject) The subject line for the MMS
         * @param {String} $data[4] (priority) Can be "Default", "Low", "Normal" or "High"
         *
         * @method sendMms
         */
        public function sendMms($data) {

            try {
                $encoded_file = $this->base64Encode($data[2]);
            } catch (Exception $e) {
                $response = new Response(array("error" => "File Not Found"));
                return $response;
            }

            $response = $this->_sendMms($data[0], $data[1] , "image/jpeg", $data[2], $encoded_file, $data[3], $data[4]);
            return $response;

        }



        /**
         * Sends an MMS to a recipient
         *
         * @param {String} token The oAuth access token
         * @param {String} tel Comma separated list of MSISDN of the recipients
         * @param {String} file_mime_type The MIME type of the content, eg: image/jpg
         * @param {String} file_name The name of the file, eg logo.jpg
         * @param {Binary} endcoded_file The contents of the file, converted to Base64
         * @param {String} subject The subject line for the MMS
         * @param {String} priority Can be "Default", "Low", "Normal" or "High"
         *
         * @method _sendMms
         * @private
         */
        public function _sendMms($token, $tel, $file_mime_type, $file_name, $encoded_file,  $subject, $priority) {

            if (strstr($tel, ",")) {
                // if it's csv, split and iterate over each value prepending each value with "tel:"
                $tel = split(",", $tel);
                foreach ($tel as $key => $value) {
                    $tel[$key] = "\"tel:$value\"";
                }
                // json-encoded array
                $tel = "[" . join(",", $tel) . "]";
            } else {
                $tel = "\"tel:$tel\"";
            }

            //echo "$token, $tel, $file_mime_type, $file_name, $encoded_file, $priority, $subject";

            $mimeContent = new MiniMime();
            $mimeContent->add_content(array(
                "type" => "application/json",
                "content" => "{ 'Address' : $tel, 'Subject' : '$subject', 'Priority': '$priority' }"
            ));

            //var_dump($mimeContent);

            $mimeContent->add_content(array(
                "type" => $file_mime_type,
                "headers" => array(
                    "Content-Transfer-Encoding" => "base64",
                    "Content-Disposition" => "attachment; name=$file_name"
                ),
                "content_id" => "<$file_name>",
                "content" => $encoded_file
            ));

            //    var_dump($mimeContent);
            //    echo "$this->base_url/rest/mms/2/messaging/outbox?access_token=$token";
            return $this->json_post_mime("$this->base_url/rest/mms/2/messaging/outbox?access_token=$token", $mimeContent);
        }


        /**
         * Queries the status of a sent MMS
         *
         * @param {Hash} data A hash of SMS options, which should include:
         * @param {String} token The oAuth access token
         * @param {String} mms_id The ID of the MMS as received in the returned data when sending an MMS
         *
         * @method mmsStatus
         */
        public function mmsStatus($data) {
            return $this->json_get("$this->base_url/rest/mms/2/messaging/outbox/$data[1]?access_token=$data[0]");
        }

        /**
         * Sends a WAP Push to a device
         *
         * @param {String} token The oAuth access token
         * @param {String} tel A comma separated list of MSISDNs of the recipients
         * @param {String} message The message to send
         * @param {String} subject The subject of the Push message
         * @param {String} priority The priority of the message
         *
         * @method wapPush
         */
        public function wapPush($data) {

            $response = $this->_wapPush($data[0], $data[1], $data[2], $data[3], $data[4]);
            return $response;

        }


        /**
         * Sends a WAP Push to a device
         *
         * @param {String} token The oAuth access token
         * @param {String} tel A comma separated list of MSISDNs of the recipients
         * @param {String} message The message to send
         * @param {String} subject The subject of the Push message
         * @param {String} priority The priority of the message
         *
         * @method _wapPush
         * @private
         */
        public function _wapPush($token, $tel, $message, $subject, $priority) {
            $mimeContent = new MiniMime();

            $tel = split(",", $tel);
            $tel = join("</address><address>tel:", $tel);

            $mimeContent->add_content(array(
                "type" => "text/xml",
                "content" =>
                    "<wapPushRequest>\n" .
                    "  <addresses>\n" .
                    "     <address>tel:$tel</address>\n" .
                    "  </addresses>\n" .
                    "  <subject>$subject</subject>\n" .
                    "  <priority>$priority</priority>\n" .
                    "</wapPushRequest>"
            ));

            $mimeContent->add_content(array(
                "type" => "text/xml",
                "content" =>
                    "Content-Disposition: form-data; name=\"PushContent\"\n" .
                    "Content-Type: text/vnd.wap.si\n" .
                    "Content-Length: 12\n" .
                    "X-Wap-Application-Id: x-wap-application:wml.ua\n" .
                    "\n" .
                    $message
            ));

            return $this->json_post_mime("$this->base_url/1/messages/outbox/wapPush?access_token=$token", $mimeContent);
        }

        public function __call($name, $args) {
            preg_match("/^direct_(.*)$/", $name, $matches);
            if ($method = $matches[1]) {
                // $args is an array of the passed params (this is important to know since we're calling methods using call_user_func_array())
                // Also, return value must be cast to an object as it will then be merged into another object
                $response = $this->$method($args);
                if ($response instanceof Response) {
                    if ($response->isError()) {
                        return (object) array("error" => $response->error());
                    } else {
                        return (object) array("result" => $response->data());
                    }
                } else {
                    // For APIs like oauthUrl that only return a string and don't wrap a cURL response in a Response object, $response will be a string
                    return (object) array("result" => $response);
                }
            } else {
                return (object) array("error" => "No such method");
            }
        }
    }
?>
