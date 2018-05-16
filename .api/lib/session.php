<?php

namespace Lib;

use RedBeanPHP\R;

/**
 * Session class.
 */
class Session extends \Prefab
{
    public function __construct(\Base $f3)
    {
        $this->f3 = $f3;

        $db = $f3->get('db');

        if ($db['session'] === true) {
            // connect to redbean
            if (!R::testConnection()) {
                R::addDatabase(
                    'connection',
                    'mysql:host='.$db['host'].';dbname='.$db['name'],
                    $db['username'],
                    $db['password']
                );

                R::selectDatabase('connection');
                R::freeze($db['freeze']);
                R::debug($db['debug'], 2);
            }

            // bootstrap if needed
            if (!defined('TABLES')) {
                define('TABLES', json_encode(R::inspect()));
            }

            if (!in_array('sessions', json_decode(TABLES, true))) {
                R::exec("
                    SET NAMES utf8;
                    SET time_zone = '+00:00';
                    SET foreign_key_checks = 0;
                    SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

                    SET NAMES utf8mb4;

                    DROP TABLE IF EXISTS `sessions`;
                    CREATE TABLE `sessions` (
                      `sid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                      `access` int(11) NOT NULL,
                      `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
                      `ip` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
                      `agent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                      PRIMARY KEY (`sid`),
                      KEY `sid` (`sid`),
                      KEY `ip` (`ip`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            }

            // close standard session if open
            session_write_close();

            // Store sessions in database
            // Set handler to overide SESSION
            session_set_save_handler(
                [$this, '_open'],
                [$this, '_close'],
                [$this, '_read'],
                [$this, '_write'],
                [$this, '_destroy'],
                [$this, '_gc']
            );

            // cookie params
            session_set_cookie_params(
                strtotime('+1 month'),
                '/',
                '.'.$_SERVER['HTTP_HOST']
            );

            //
            session_name('SID');

            register_shutdown_function('session_write_close');
        }

        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.entropy_length', '512');
        ini_set('session.hash_function', 'sha256');

        // Start the session
        session_start();

        define('SID', session_id());
    }

    private function getIPAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return preg_replace("([^0-9\.])", '', $ip);
    }

    /**
     * Open.
     */
    public function _open()
    {
        return true;
    }

    /**
     * Close.
     */
    public function _close()
    {
        return true;
    }

    /**
     * Read.
     *
     * IP is checked against the session id, to prevent session hijacking
     */
    public function _read($id)
    {
        return R::getCell(
            'SELECT data FROM sessions WHERE sid = ? AND ip = ? LIMIT 1', [$id, $this->getIPAddress()]
        );
    }

    /**
     * Write.
     */
    public function _write($id, $data)
    {
        // Create time stamp
        $access = time();

        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-';

        // Set query
        R::exec('REPLACE INTO sessions VALUES (?, ?, ?, ?, ?)', [$id, $access, $data, $this->getIPAddress(), $user_agent]);

        // Return True
        return true;
    }

    /**
     * Destroy.
     *
     * Also directly called on logout:
     *
     * @see \App\Framework\Session::logout()
     */
    public function _destroy($id)
    {
        // Set query
        R::exec('DELETE FROM sessions WHERE sid = ?', [$id]);

        return true;
    }

    /**
     * Garbage Collection.
     */
    public function _gc($max)
    {
        // Calculate what is to be deemed old
        $old = time() - $max;

        // Set query
        R::exec('DELETE * FROM sessions WHERE access < ?', [$old]);

        return true;
    }
}
