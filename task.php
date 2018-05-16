<?php
if (php_sapi_name() != 'cli') {
    header('HTTP/1.0 403 Forbidden');
    exit('No web access, this is a cli script!');
}

require 'vendor/autoload.php';

// init fatfree instance
$f3 = \Base::instance();

// load config
$f3->config('config.ini');

$task = new Plinker\Tasks\Runner([
    'database'    => $f3->get('db'),
    'debug'       => true,
    'log'         => true,
    'sleep_time'  => 2,
    'tmp_path'    => './.plinker',
    'auto_update' => 86400
]);

$task->daemon('Queue');
