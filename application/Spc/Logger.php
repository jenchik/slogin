<?php
/**
 * Created by PhpStorm.
 * User: evgenii
 * Date: 15.02.15
 * Time: 15:47
 */

namespace Spc;

/**
 *
 * for standart PSR-3 compatible
 */
use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{
    protected $_config;

    public function __construct(Application $app)
    {
        $config = $app['config'];
        if (!isset($config['logger']['LOG_ENABLE']) || !$config['logger']['LOG_ENABLE']) {
            // dummy
            $app['logger'] = new \Psr\Log\NullLogger();
            return;
        }

        $app['logger'] = $this;
        $this->_config = $config['logger'];
    }

    public function debug($message, array $context = [])
    {
        if (isset($this->_config['LOG_DEBUG']) && $this->_config['LOG_DEBUG']) {
            parent::debug($message, $context);
        }
    }

    public function emerg($message, array $context = [])
    {
        parent::emergency($message, $context);
    }

    public function warn($message, array $context = [])
    {
        parent::warning($message, $context);
    }

    public function log($level, $message, array $context = [])
    {
//		if($message instanceof \Exception)
//			$message = $this->parseException($message);

        if (!empty($context))
            foreach ($context as $key => $value) {
                $message .= "\n === $key ===: $value";
            }

        @file_put_contents($this->_config['LOG_FILE'], "[" . date('Y-m-d H:i:s') . "] [$level]: $message\n", FILE_APPEND);
        $this->log_to_console($message);
    }

    public function log_to_console($message)
    {
        if (isset($this->_config['LOG_TO_CONSOLE'])) {
            echo $message . "\n";
        }
    }

    protected function parseException(\Exception $exception)
    {
        return "{file:{$exception->getFile()}, line:{$exception->getLine()}, message:\"{$exception->getMessage()}\" trace:\"{$exception->getTraceAsString()}\"}";
    }
}
