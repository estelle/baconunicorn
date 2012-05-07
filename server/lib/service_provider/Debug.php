<?php
/**
* This class provides useful methods for debugging
* @class Debug
*/
class Debug {
  private static $fp;

  /**
   *
   * initialize the debugger
   * @param {Number} $needsPointer whether a file pointer needs to be returne or not (defaults to 0)
   * @return {FilePointer} Returns a file pointer if needed
   * @method init
   */
  public static function init($needsPointer = 0) {
    self::$fp = fopen(DEBUG_LOGGER, "a");

    // Return the file pointer if needed, eg for a cURL request
    if ($needsPointer) {
      return self::$fp;
    }
  }

  /**
   *
   * Writes debug information to the debug file
   * @param {String} $output the string to write
   * @method write
   */
  public static function write($output) {
    fwrite(self::$fp, $output);
  }

  /**
   *
   * Writes debug information to the debug file
   * @param {String} $nl the newline character. Defaults to '\n\
   * @method dumpBacktrace
   */
  public static function dumpBacktrace($nl = "\n") {
    $str = $nl . "*******************************************";
    $str .= $nl . "Debug backtrace begin" . $nl;
    foreach (debug_backtrace() as $key => $value) {
      // Place any functions to ignore in the array below
      if (!in_array($value['function'], array("dumpBacktrace", "__call", "call_user_func_array"))) {
        // Skip if it's a direct_ function; eg. direct_deviceInfo, etc.
        if (!stristr($value['function'], "direct_")) {
          $str .= $nl . "function: {$value['function']}; file: {$value['file']}; line: {$value['line']}";
        }
      }
    }
    $str .= $nl . $nl . "Debug backtrace end";
    $str .= $nl . "*******************************************" . $nl;
    self::write($str);
  }

  /**
   *
   * Closes the debug file
   * @method end
   */
  public static function end() {
    fclose(self::$fp);
  }
}
?>
