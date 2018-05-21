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

if (!function_exists('getallheaders')) { 
  function getallheaders() { 
    $headers = array (); 
    foreach ($_SERVER as $name => $value) { 
      if (substr($name, 0, 5) == 'HTTP_') { 
        $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value; 
      } 
    } 
    return $headers; 
  } 
} 

// load routes
$f3->config('.api/routes.ini');

// enable cors
if ($f3->get('CORS.enabled')) {
  $f3->copy('HEADERS.Origin', 'CORS.origin');
  $f3->set('CORS.headers', 'authorization, origin, x-requested-with, x-request-token, content-type');
  $f3->set('CORS.credentials', true);
  $f3->set('CORS.expose', true);
  $f3->set('CORS.ttl', 300);
}

// add hive
$f3->set('f3', $f3);

// load libs
//$f3->set('session', \Lib\Session::instance($f3));
$f3->set('cache', \Cache::instance());
$f3->set('response', \Lib\Response::instance($f3));
$f3->set('helper',   \Lib\helper::instance($f3));
//$f3->set('flashbag', \Lib\flashbag::instance($f3));

// run app
$f3->run();
