<?php
/**
 * @class Response
 *
 * This class deals with the JSON response from AT&T. It provides simple methods to
 * determine whether the response is an error and extracts the
 * error message if so. If the response is not an error, it assumes it is a JSON
 * object and decodes it. Here's an example of how this class is used:
 *
 *     $response = $provider.sendSms('abc123', '415-555-2425', 'Test SMS')
 *
 *     if ($response.isError()) {
 *       return "Error! " + $response.error()
 *     } else {
 *       return "Success! " + response.data()
 *     }
 *
 * @cfg {Object/String} response An http response object or a string of JSON data
 */
class Response {
    private $response;
    private $headers;
    private $error = false;

    /**
     * @constructor
     * Creates new Response
     * @param {Object/String} response Either a response object or a string
     */
    public function __construct($response) {

        if ($response instanceof Exception) {
            // if it's an exception object then grab the error message
            $this->error = $response->getMessage();

        } else {

            if (isset($response['headers']) && $response['headers']['http_code'] >= 400) {
                if (in_array($response['headers']['http_code'], array(401, 403))) {
                    if (isset($response['body'])) {
                        $this->error = $this->parse_response($response['body']);
                    } else {
                        $this->error = $response['headers']['http_code'] . ": Unauthorized request";
                    }
                } else {
                    $this->error = $this->parse_response($response['body']);
                }
            } else {
                // Else parse the response, it could be either JSON or XML
                if (isset($response['body'])) {
                    $this->response = $this->parse_response($response['body']);
                } else {
                    $parsed = $this->parse_response(json_encode($response));
                    if (isset($response['error'])) {
                        $this->error = $parsed->error;
                    } else {
                        $this->response = $parsed;
                    }
                }
            }

            // Store the headers (curl info)
            if (isset($response['headers'])) {
                $this->headers = $response['headers'];
            }
        }
    }

    /**
     * Used to see whether or not the response was an error
     * @method isError
     */
    public function isError() {
        return $this->error ? true : false;
    }

    /**
     * Returns the error message
     * @method error
     */
    public function error() {
        return  $this->error;
    }

    /**
     * Returns the decoded data from the server (assuming there was no exception)
     * @method data
     */
    public function data() {
        return $this->response;
    }

    /**
     * Returns the headers from the server (assuming there was no exception)
     * @method headers
     */
    public function headers() {
        return $this->headers;
    }

    /**
     * Parses the data passed in.
     * It first tries json_decode and if it fails tries simplexml_load_string.
     * If both fail, the original value passed in is returned.
     *
     * @param {String} $parsed
     *
     * @method parse_response
     * @private
     */
    private function parse_response($body) {

        // Try to parse the response as JSON
        $parsed = json_decode($body);

        // If parsing as JSON failed, try parsing as XML...
        if (is_null($parsed)) {
            $parsed = simplexml_load_string("<xml>$body</xml>");
        }

        // If it's not JSON or XML, just return whatever was passed in.
        if ($parsed == FALSE) {
            return $body;
        }

        return $parsed;
    }
}
?>
