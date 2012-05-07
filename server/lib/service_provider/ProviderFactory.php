<?php
/**
 *
 * @class ProviderFactory
 */
class ProviderFactory {
  /**
   * This is a Factory method which instantiates a Sencha ServiceProvider subclass
   * depending on the 'provider' config option.
   *
   * @param {Object} config
   * @param {String} config.provider The provider to use when instantiating a Sencah ServiceProvider subclass.  Currently only 'att' is a valid provider.
   * @method init
   */
  public static function init($config) {
    if (!$config['provider']) throw new Exception("provider must be set");

    $classname = "Sencha_ServiceProvider_Base_" . ucwords($config['provider']);
    return new $classname($config);
  }
}
?>
