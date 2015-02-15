<?php

return [
		'db.options' => [
			//'dsn' => 'pgsql:host=127.0.0.1;dbname=slogin',
            'dbname' => 'slogin',
            'host' => '127.0.0.1',
            'port' => '5432',
			'user' => 'sl-user',
			'password' => '111111',
			//'charset' => 'UTF-8',
		],
        'logger' => [
            'LOG_ENABLE' => true,
            'LOG_FILE' => LOG_DIR . '/app.log',
            'LOG_DEBUG' => $this['debug'],
            //'LOG_TO_CONSOLE' => false,
        ],
		'ini_set' => [
			'display_errors' => 1,
		],
	];
