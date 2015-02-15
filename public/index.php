<?php

use Spc\Application;

defined('SPC_ENV')
|| define('SPC_ENV', (getenv('SPC_ENV') ? getenv('SPC_ENV') : 'development'));

define('LOG_DIR', __DIR__ . '/../logs');
define('CONF_DIR', __DIR__ . '/../config');
define('APP_DIR', __DIR__ . '/../application');
define('VENDOR_DIR', __DIR__ . '/../vendor');

$loader = require_once VENDOR_DIR . '/autoload.php';

$app = new Application();
$app['composer'] = $loader;

if (SPC_ENV == 'development')
    require_once CONF_DIR . '/dev.php';
else
    require_once CONF_DIR . '/prod.php';

new \Spc\Logger($app);
new \Spc\PgCall($app);
new \Spc\Auth($app);
new \Spc\Controller($app);

$app->run();
