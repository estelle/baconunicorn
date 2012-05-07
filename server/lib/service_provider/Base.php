<?php
/**
 *
 * Base class for common methods
 * @class Base
 *
 */
abstract class Base {

    /**
     *
     * Uses curl to make an http request
     *
     * @param {String} $type
     * @param {String} $url
     * @param {String} $contentType
     * @param {String} $postfields
     *
     * @return {Response} The response from the http request
     * @method makeRequest
     */
    private function makeRequest($type, $url, $contentType, $postfields = null) {
        try {
            $curl = curl_init($url);
            $options = array(
                CURLOPT_HTTPHEADER => array("Content-Type: $contentType", "Accept: application/json"),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false
            );
            curl_setopt_array($curl, $options);
            if ($type === "POST" || $type === "PUT") {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
            }

            if ($type === "PUT") {
                if($postfields === 'null') {
                    curl_setopt($curl, CURLOPT_PUT, 1);
                } else {
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                }
            }

            // If debugging, set the options before executing the request
            if (DEBUG) {
                $fp = Debug::init(1);
                curl_setopt($curl, CURLOPT_VERBOSE, true);
                curl_setopt($curl, CURLOPT_STDERR, $fp);
            }
            $curl_response = curl_exec($curl);
            $curl_info = curl_getinfo($curl);

            // If debugging, capture the response body after the request has been sent, but before the curl instance is closed
            if (DEBUG) {
                if ($type === "POST") {
                    Debug::write("\n-------------------post data----------------------\n\n$postfields\n");
                }

                Debug::write("\n-------------------response body----------------------\n\n$curl_response\n");
                Debug::dumpBacktrace();
                curl_close($curl);
                Debug::end();
            } else {
              curl_close($curl);
            }

            return new Response(array(
                "body" => $curl_response,
                "headers" => $curl_info
            ));

        } catch (Exception $e) {
            return new Response($e);
        }
    }

    /**
     *
     * Uses curl to make an http request and returns the HEADERS from the request
     *
     * @param {String} $type
     * @param {String} $url
     * @param {String} $contentType
     * @param {String} $postfields
     *
     * @return {String[]} An array of the headers returned from the http request
     * @method makeRequestIncludeHeaders
     */
    private function makeRequestIncludeHeaders($type, $url, $contentType, $postfields = null) {
        try {
            $curl = curl_init($url);
            $options = array(
                CURLOPT_HTTPHEADER => array("Content-Type: $contentType", "Accept: application/json"),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_MAXREDIRS => 0,
                CURLOPT_HEADER => true
            );
            curl_setopt_array($curl, $options);
            if ($type === "POST") {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
            }

            // If debugging, set the options before executing the request
            if (DEBUG) {
                $fp = Debug::init(1);
                curl_setopt($curl, CURLOPT_VERBOSE, true);
                curl_setopt($curl, CURLOPT_STDERR, $fp);
            }
            $curl_response = curl_exec($curl);
            $curl_info = curl_getinfo($curl);

            // Extract headers from response
            $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
            preg_match_all($pattern, $curl_response, $matches);
            $headers_string = array_pop($matches[0]);
            $headersRaw = explode("\r\n", str_replace("\r\n\r\n", '', $headers_string));


            // Convert headers into an associative array
            $headers = array();
            foreach ($headersRaw as $header) {
                preg_match('#(.*?)\:\s(.*)#', $header, $matches);
                $headers[$matches[1]] = $matches[2];
            }

            return $headers;

        } catch (Exception $e) {
            return new Response($e);
        }
    }


    /**
     *
     * base64_encode a file
     * @param {String} $file_name The name of the file to encode
     * @throws Exception
     * @return {String} The encoded file
     * @method base64Encode
     */
    public function base64Encode($file_name) {
        if (file_exists($file_name)) {
            $file_binary = fread(fopen($file_name, "r"), filesize($file_name));
            return base64_encode($file_binary);
        } else {
            throw new Exception("File Not Found");
        }
    }


    /**
     *
     * Calls makeRequest passing GET as the type and application/json as the contentType
     * @param {String} $url the url of the request to make
     * @return {Response} The response from the http request
     * @method json_get
     */
    public function json_get($url) {
        return $this->makeRequest("GET", $url, "application/json");
    }


    /**
     *
     * Calls makeRequestIncludeHeaders passing GET as the type and application/json as the contentType
     * @param {String} $url the url of the request to make
     * @return {String[]} An array of the headers returned from the http request
     * @method json_getHeaders
     */
    public function json_getHeaders($url) {
        return $this->makeRequestIncludeHeaders("GET", $url, "application/json");
    }


    /**
     *
     * Calls makeRequest passing POST as the type and application/json as the contentType
     * @param {String} $url the url of the request to make
     * @param {String} $data the data to pass in the post
     * @return {Response} The response from the http request
     * @method json_post
     */
    public function json_post($url, $data) {
        return $this->makeRequest("POST", $url, "application/json", json_encode($data));
    }

    /**
     *
     * Calls makeRequest passing PUT as the type and application/json as the contentType
     * @param {String} $url the url of the request to make
     * @param {String} $data the data to pass in the put
     * @return {Response} The response from the http request
     * @method json_put
     */
    public function json_put($url, $data) {
        return $this->makeRequest("PUT", $url, "application/json", json_encode($data));
    }

    /**
     *
     * Calls makeRequest passing POST as the type and application/x-www-form-urlencoded as the contentType
     * @param {String} $url the url of the request to make
     * @param {String} $data the data to pass in the post
     * @return {Response} The response from the http request
     * @method form_post
    */
    public function form_post($url, $data) {
        return $this->makeRequest("POST", $url, "application/x-www-form-urlencoded", $data);
    }

    /**
     *
     * Special version of the json_post method in which the content is MIME encoded
     * @param {String} $url the url of the request to make
     * @param {String} $mimeContent the mimeContent to pass in the post
     * @return {Response} The response from the http request
     * @method json_post_mime
     */
    protected function json_post_mime($url, $mimeContent) {
        return $this->makeRequest("POST", $url, $mimeContent->header(), $mimeContent->content());
    }
}

?>
