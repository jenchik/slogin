<?php

$app['config'] = include_once __DIR__ . '/config.php';

$app['debug'] = true;

error_reporting(-1);

if (isset($app['config']['ini_set']) && is_array($app['config']['ini_set']))
    foreach ($app['config']['ini_set'] as $key => $val) {
        ini_set($key, $val);
    }

//for test!
$app->match('/hello', function ($params) use ($app) {
	return 'Hello ' . (isset($params['name']) ? $params['name'] : '');
});
