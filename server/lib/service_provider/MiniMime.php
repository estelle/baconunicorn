<?php
/**
 * This class produces MIME content strings for multipart data
 * @class MiniMime
 */
class MiniMime {

  /**
   * Creates a unique boundary used to separate each part of the MIME multipart message
   * @property {String} split
   */
  private $split = "";
  /**
   * The array of the parts that will be sent in the message.  Use #content to implode the parts into a valid format needed for a MIME multipart message.
   * @property {String[]} contents
   */
  private $contents = array();

  /**
   * @constructor
   * Creates a new instance of MiniMime
   * @param {.} .
   */
  public function __construct() {
    $this->split = "----=_Part_0_" . rand() . "." . ((string) time());
  }

  /**
   * Adds content to the contents array.  Use the #content method to implode the array when posting the mime.
   * @param {Object} config
   * @param {String} config.type The value for the Content-Type header
   * @param {String} config.content_id The value for the Content-ID header
   * @param {String[]} config.headers An array of any additional headers to add
   *
   * @method add_content
   */
  public function add_content($configs) {
    $result = "Content-Type: {$configs['type']}";

    # Set the Content-ID header
    if (count($this->contents) == 0 && !isset($configs['content_id'])) {
      $result .= "\nContent-ID: <part0@sencha.com>";
      $result .= "\nContent-Disposition: form-data; name=\"root-fields\"";
    } else {
      $content_id = $configs['content_id'];
      if (!$content_id) {
        $content_id = "<part" . (count($this->contents) + 1) . "@sencha.com>";
      }
      $result .= "\nContent-ID: $content_id";
    }

    if (isset($configs['headers'])) {
      foreach ($configs['headers'] as $key => $value) {
        $result .= "\n$key: $value";
      }
    }

    $result .= "\n\n{$configs['content']}";
    if (substr($configs['content'], -1) != "\n") {
      $result .= "\n";
    }
    array_push($this->contents, $result);
  }

  /**
   *
   * Returns a string that will be used as the Content-Type for a MIME multipart message
   * @method header
   */
  public function header() {
    return 'multipart/form-data; type="application/json"; start="<part0@sencha.com>"; boundary="' . $this->split . '"';
  }

  /**
   *
   * Returns the content, correctly formated for a MIME multipart message, by imploding the contents array using #split that produces a unique boundary for each part.
   * @method content
   */
  public function content() {
    return "--$this->split\n" . implode("--$this->split\n", $this->contents) . "\n--$this->split--\n";
  }
}
?>
