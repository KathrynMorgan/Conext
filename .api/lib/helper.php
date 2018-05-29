<?php

namespace Lib;

class Helper extends \Prefab
{
    public function __construct(\Base $f3)
    {
        $this->f3 = $f3;
    }

    public function format_bytes($bytes = 0)
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2).' KiB';
        } elseif ($bytes < 1073741824) {
            return round($bytes / 1048576, 2).' MiB';
        } elseif ($bytes < 1099511627776) {
            return round($bytes / 1073741824, 2).' GiB';
        } else {
            return round($bytes / 1099511627776, 2).' TiB';
        }
    }

    public function short_number($n = 0)
    {
        // first strip any formatting;
        $n = (0 + str_replace(',', '', $n));

        // is this a number?
        if (!is_numeric($n)) {
            return false;
        }

        // now filter it;
        if ($n > 1000000000000) {
            return round(($n / 1000000000000), 2).'t';
        } elseif ($n > 1000000000) {
            return round(($n / 1000000000), 2).'b';
        } elseif ($n > 1000000) {
            return round(($n / 1000000), 2).'m';
        } elseif ($n > 1000) {
            return round(($n / 1000), 2).'k';
        }

        return number_format($n);
    }

    public function time_elapsed_string($ptime, $short = false)
    {
        $etime = time() - $ptime;

        if ($etime < 2) {
            return 'just now';
        }

        $plurals = [
            'year'  => 'years',
            'month' => 'months',
            'day'   => 'days',
            'hour'  => 'hours',
            'min'   => (!$short ? 'minutes' : 'mins'),
            'sec'   => (!$short ? 'seconds' : 'secs'),
        ];

        foreach ([
            365 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60  => 'month',
            24 * 60 * 60       => 'day',
            60 * 60            => 'hour',
            60                 => 'min',
            1				   => 'sec',
        ] as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);

                return $r.' '.($r > 1 ? $plurals[$str] : $str).' ago';
            }
        }
    }

    public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     * Attempts to get originating IP address of user,
     * Spoofable, but works load balancing and containers ect.
     * pulls first ip from multi level proxied HTTP_X_FORWARDED_FOR e.g "ip, ip, ip".
     */
    public function getIPAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (stristr($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);

            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return preg_replace("([^0-9\.])", '', $ip);
    }

    public function get_gravatar($email = null, $size = 100)
    {
        if ($email == null) {
            return '//secure.gravatar.com/avatar/?d=mm&secure=true';
        }

        return '//secure.gravatar.com/avatar/'.md5(trim($email)).'?default=mm&secure=true&size='.(int) $size;
    }

    public function str_color($str = null)
    {
        $hash = md5($str);

        $color[] = hexdec(substr($hash, 8, 2));
        $color[] = hexdec(substr($hash, 4, 2));
        $color[] = hexdec(substr($hash, 0, 2));

        foreach ($color as $k => $v) {
            if ($v < 128) {
                $color[$k] += 128;
            }
            $color[$k] = dechex($color[$k]);
        }

        return '#'.strtoupper(implode($color));
    }

    public function random_color()
    {
        return $this->str_color(uniqid(true));
    }

    public function strip_extra_spaces($str)
    {
        return preg_replace('/\s+/', ' ', $str);
    }

    /**
     * Validate json, throws exception or returns object/array.
     *
     * @param string $str
     * @param bool   $return_array
     *
     * @return mixed
     */
    public function json_validate($str, $return_array = false)
    {
        // decode the JSON data
        $result = json_decode($str, $return_array);

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE: {
                $error = ''; // JSON is valid
            } break;

            case JSON_ERROR_DEPTH: {
                $error = 'The maximum stack depth has been exceeded.';
            } break;

            case JSON_ERROR_STATE_MISMATCH: {
                $error = 'Invalid or malformed JSON.';
            } break;

            case JSON_ERROR_CTRL_CHAR: {
                $error = 'Control character error, possibly incorrectly encoded.';
            } break;

            case JSON_ERROR_SYNTAX: {
                $error = 'Syntax error, malformed JSON.';
            } break;

                // PHP >= 5.3.3
            case JSON_ERROR_UTF8: {
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            } break;

                // PHP >= 5.5.0
            case JSON_ERROR_RECURSION: {
                $error = 'One or more recursive references in the value to be encoded.';
            } break;

                // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN: {
                $error = 'One or more NAN or INF values in the value to be encoded.';
            } break;

            case JSON_ERROR_UNSUPPORTED_TYPE: {
                $error = 'A value of a type that cannot be encoded was given.';
            } break;

            default: {
                $error = 'Unknown JSON error occured.';
            } break;
        }

        if ($error !== '') {
            throw new \Exception($error);
            return false;
        }

        return $result;
    }

    public function http_response_code_text($domain)
    {
        $headers = @get_headers('http://'.$domain);

        if (empty($headers)) {
            return '<span class="label label-danger">503 ('.$this->response_code_text(503).')</span>';
        }

        $status = explode(' ', $headers[0])[1];

        if (strlen($status) > 0) {
            //set status color to warning if not 200
            if ($status == 200) {
                $labelColor = 'success';
            } elseif ($status == 404) {
                $labelColor = 'warning';
            } else {
                $labelColor = 'danger';
            }

            return '<span class="label label-'.$labelColor.'">'.$status.' ('.$this->response_code_text($status).')</span>';
        } else {
            return '<span class="label label-danger">503 ('.$this->response_code_text(503).')</span>';
        }
    }

    public function response_code_text($code)
    {
        switch ($code) {
            case 100: $text = 'Continue'; break;
            case 101: $text = 'Switching Protocols'; break;
            case 200: $text = 'OK'; break;
            case 201: $text = 'Created'; break;
            case 202: $text = 'Accepted'; break;
            case 203: $text = 'Non-Authoritative Information'; break;
            case 204: $text = 'No Content'; break;
            case 205: $text = 'Reset Content'; break;
            case 206: $text = 'Partial Content'; break;
            case 300: $text = 'Multiple Choices'; break;
            case 301: $text = 'Moved Permanently'; break;
            case 302: $text = 'Moved Temporarily'; break;
            case 303: $text = 'See Other'; break;
            case 304: $text = 'Not Modified'; break;
            case 305: $text = 'Use Proxy'; break;
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 402: $text = 'Payment Required'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 406: $text = 'Not Acceptable'; break;
            case 407: $text = 'Proxy Authentication Required'; break;
            case 408: $text = 'Request Time-out'; break;
            case 409: $text = 'Conflict'; break;
            case 410: $text = 'Gone'; break;
            case 411: $text = 'Length Required'; break;
            case 412: $text = 'Precondition Failed'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 414: $text = 'Request-URI Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            case 501: $text = 'Not Implemented'; break;
            case 502: $text = 'Bad Gateway'; break;
            case 503: $text = 'Service Unavailable'; break;
            case 504: $text = 'Gateway Time-out'; break;
            case 505: $text = 'HTTP Version not supported'; break;
            default:
                $text = $code;
                break;
        }

        return $text;
    }

    /**
     * simple encrypt string.
     *
     * iv is stored as part of the returned ciphertext, uses base91 from Plinker lib
     */
    public function encrypt($plaintext, $secret = '')
    {
        $mode = 'AES-256-CBC';
        $check = false;
        $ivlen = openssl_cipher_iv_length($mode);
        $iv = openssl_random_pseudo_bytes($ivlen, $check);

        if (!$check) {
            throw new Exception('Non-cryptographically strong algorithm used for iv generation. This IV is not safe to use.');
        }

        return \Base91\Base91::encode(base64_encode($iv).':'.base64_encode(openssl_encrypt($plaintext, $mode, $secret, 0, $iv)));
    }

    /**
     * simple decrypt string.
     */
    public function decrypt($ciphertext, $secret = '')
    {
        $mode = 'AES-256-CBC';

        list($iv, $ciphertext) = explode(':', \Base91\Base91::decode($ciphertext));
        $iv = base64_decode($iv);
        $ciphertext = base64_decode($ciphertext);

        return openssl_decrypt($ciphertext, $mode, $secret, 0, $iv);
    }

}
