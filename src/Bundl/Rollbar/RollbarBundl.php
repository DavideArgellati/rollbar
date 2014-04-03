<?php
/**
 * Created by PhpStorm.
 * User: tom.kay
 * Date: 03/04/2014
 * Time: 16:09
 */

namespace Bundl\Rollbar;

use Cubex\Bundle\Bundle;
use Cubex\Events\EventManager;
use Cubex\Events\IEvent;
use Cubex\Foundation\Config\Config;
use Cubex\Foundation\Container;
use Cubex\Log\Log;
use Psr\Log\LogLevel;

class RollbarBundl extends Bundle
{
  protected $_logLevel;

  public function init($initialiser = null)
  {
    $config          = Container::config()->get("rollbar", new Config());
    $token           = $config->getStr("post_server_item", null);
    $this->_logLevel = $config->getStr("log_level", LogLevel::WARNING);

    // installs global error and exception handlers
    \Rollbar::init(array('access_token' => $token));

    EventManager::listen(
      EventManager::CUBEX_LOG,
      [$this, "log"]
    );
  }

  public function log(IEvent $e)
  {
    $level = $e->getStr('level');
    if(Log::logLevelAllowed($level, $this->_logLevel))
    {
      // "critical", "error", "warning", "info", "debug"
      switch($level)
      {
        case LogLevel::EMERGENCY:
        case LogLevel::ALERT:
        case LogLevel::CRITICAL:
          $level = 'critical';
          break;
        case LogLevel::ERROR:
          $level = 'error';
          break;
        case LogLevel::WARNING:
          $level = 'warning';
          break;
        case LogLevel::DEBUG:
          $level = 'debug';
          break;
        case LogLevel::NOTICE:
        case LogLevel::INFO:
        default:
          $level = 'info';
          break;
      }
      \Rollbar::report_message($e->getStr('message'), $level);
    }
  }
}