<?php
// set timezone
date_default_timezone_set('UTC');

// move up a directory
chdir('../');

// composers autoloader
require_once 'vendor/autoload.php';

// init fatfree instance
$f3 = \Base::instance();

// load config
$f3->config('config.ini');

/**
 * Its Plinker!
 */
if (isset($_SERVER['HTTP_PLINKER'])) {
  // init plinker server
  exit((new \Plinker\Core\Server(['secret' => $f3->get('AUTH.secret')]))->listen());
}

/**
 * Its Webapp!
 */

// load routes
$f3->config('.api/routes.ini');

// enable cors
if ($f3->get('CORS.enabled')) {
  $f3->copy('HEADERS.Origin', 'CORS.origin');
  $f3->set('CORS.headers', 'authorization, origin, x-requested-with, x-request-token, content-type');
  $f3->set('CORS.credentials', true);
  $f3->set('CORS.expose', true);
  $f3->set('CORS.ttl', 600);
}

// add hive
$f3->set('f3', $f3);

// load libs
if ($f3->get('db.session')) {
  $f3->set('session', \Lib\Session::instance($f3));
}
$f3->set('cache',    \Cache::instance());
$f3->set('response', \Lib\Response::instance($f3));
$f3->set('helper',   \Lib\helper::instance($f3));

// run app
$f3->run();
