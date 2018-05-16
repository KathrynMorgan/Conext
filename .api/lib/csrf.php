<?php

namespace Lib;

final class Csrf extends \Prefab
{
    /*
     * @var $f3
     */
    protected $f3;

    public function __construct(\Base $f3)
    {
        $this->f3 = $f3;
    }

    /**
     * Generate token.
     */
    private function generateToken()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(32));
        } elseif (function_exists('mcrypt_create_iv')) {
            return bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            return hash('sha256', str_shuffle(uniqid(true).microtime(true)));
        }
    }

    /**
     * Checks passed token against one in sesssion.
     *
     * @param string $csrf       - Value to compare with current
     * @param bool   $single_use - Token is refreshed everytime
     * @param string $target     - Token is targeted to a specific form/action
     *
     * @return bool
     */
    public function check($csrf = null, $single_use = true, $target = null)
    {
        if (!function_exists('hash_equals')) {
            function hash_equals($str1, $str2)
            {
                if (strlen($str1) != strlen($str2)) {
                    return false;
                } else {
                    $res = $str1 ^ $str2;
                    $ret = 0;
                    for ($i = strlen($res) - 1; $i >= 0; $i--) {
                        $ret |= ord($res[$i]);
                    }

                    return !$ret;
                }
            }
        }

        // check both passed and session are not empty
        if (is_null($csrf) || $this->f3->devoid('SESSION.csrf.secret')) {
            return false;
        }

        // no target
        if ($target === null) {
            $token = hash_hmac(
                'sha256',
                $this->f3->get('SESSION.csrf.tokens.default.secret'),
                $this->f3->get('SESSION.csrf.tokens.default.secret')
            );
            // expire current
            if ($single_use) {
                $this->f3->clear('SESSION.csrf.tokens.default');
            }
            // check
            return hash_equals($csrf, $token);
        }
        // targeted
        else {
            // hash target to get array key
            $key = hash('sha256', $target);
            // pre check
            if ($this->f3->devoid('SESSION.csrf.tokens.'.$key.'.secret')) {
                return false;
            }
            $token = hash_hmac(
                'sha256',
                $target,
                $this->f3->get('SESSION.csrf.tokens.'.$key.'.secret')
            );
            // expire current
            if ($single_use) {
                $this->f3->clear('SESSION.csrf.tokens.'.$key);
            }
            // check
            return hash_equals($csrf, $token);
        }
    }

    /**
     * Generate and set csrf token/s into session.
     *
     * Supports single use and by targeting a specific form or action.
     *
     * @param bool   $single_use - Token is refreshed everytime
     * @param string $target     - Token is targeted to a specific form/action
     *
     * @return string
     */
    public function set($single_use = true, $target = null)
    {
        // set secret token,
        if ($single_use || $this->f3->devoid('SESSION.csrf.secret')) {
            $this->f3->set('SESSION.csrf.secret', $this->generateToken());
        }

        // no target
        if ($target === null) {
            $token = hash_hmac(
                'sha256',
                $this->f3->get('SESSION.csrf.secret'),
                $this->f3->get('SESSION.csrf.secret')
            );
            $this->f3->set(
                'SESSION.csrf.tokens.default',
                [
                    'secret' => $this->f3->get('SESSION.csrf.secret'),
                    'token'  => $token,
                ]
            );
        }
        // targeted
        else {
            $token = hash_hmac(
                'sha256',
                $target,
                $this->f3->get('SESSION.csrf.secret')
            );
            $this->f3->set(
                'SESSION.csrf.tokens.'.hash('sha256', $target),
                [
                    'secret' => $this->f3->get('SESSION.csrf.secret'),
                    'token'  => $token,
                ]
            );
        }

        // set single use working token into variables
        header('X-CSRF-TOKEN: '.$token);
        $this->f3->set('csrf', $token);

        return $token;
    }
}
