<?php

namespace Lib;

class Flashbag extends \Prefab
{
    /*
     * @var object $f3
     */
    protected $f3;

    /*
     * @var string $session_key
     */
    private $session_key;

    /**
     * @param object $f3
     * @param string $session_key
     */
    public function __construct(\Base $f3, $session_key = '_flashbag_')
    {
        $this->f3 = $f3;
        $this->session_key = $session_key;
    }

    /**
     * @param string $key
     * @param array  $value
     */
    public function set($key = null, $value = [
        'msg'         => '',
        'dismissible' => true,
    ])
    {
        return $this->f3->set('SESSION.'.$this->session_key.'.'.$key, $value);
    }

    /**
     * @param string $key
     * @param bool   $clear
     */
    public function get($key = null, $clear = true)
    {
        $flashbag = [];

        if ($key !== null) {
            if ($this->f3->exists('SESSION.'.$this->session_key.'.'.$key, $flashbag)) {
                if ($clear) {
                    $this->clear($key);
                }

                return $flashbag;
            }
        } else {
            if ($this->f3->exists('SESSION.'.$this->session_key, $flashbag)) {
                if ($clear) {
                    $this->clear();
                }

                return $flashbag;
            }
        }

        return $flashbag;
    }

    /**
     * @param string $key
     */
    public function clear($key = null)
    {
        if ($key !== null) {
            return $this->f3->clear('SESSION.'.$this->session_key.'.'.$key);
        } else {
            return $this->f3->clear('SESSION.'.$this->session_key);
        }
    }

    /**
     * @param string $msg
     * @param bool   $dismissible
     */
    public function error($msg = null, $dismissible = true)
    {
        return $this->set('danger', [
            'msg'         => $msg,
            'dismissible' => $dismissible,
        ]);
    }

    /**
     * @param string $msg
     * @param bool   $dismissible
     */
    public function success($msg = null, $dismissible = true)
    {
        return $this->set('success', [
            'msg'         => $msg,
            'dismissible' => $dismissible,
        ]);
    }

    /**
     * @param string $msg
     * @param bool   $dismissible
     */
    public function info($msg = null, $dismissible = true)
    {
        return $this->set('info', [
            'msg'         => $msg,
            'dismissible' => $dismissible,
        ]);
    }

    /**
     * @param string $msg
     * @param bool   $dismissible
     */
    public function warning($msg = null, $dismissible = true)
    {
        return $this->set('warning', [
            'msg'         => $msg,
            'dismissible' => $dismissible,
        ]);
    }

    /**
     * @param string $msg
     * @param bool   $dismissible
     */
    public function danger($msg = null, $dismissible = true)
    {
        return $this->set('danger', [
            'msg'         => $msg,
            'dismissible' => $dismissible,
        ]);
    }

    /**
     * @param string $msg
     * @param bool   $dismissible
     */
    public function render()
    {
        return $this->display();
    }

    /**
     * @param string $msg
     * @param bool   $dismissible
     */
    public function display()
    {
        $return = null;
        foreach ((array) $this->get() as $type => $flash) {
            $return .= '<div class="alert alert-'.$type.(!empty($flash['dismissible']) ? ' alert-dismissible' : null).'" role="alert">';
            $return .= !empty($flash['dismissible']) ? '<button type="button" class="close" data-dismiss="alert"><i class="fa fa-times" aria-hidden="true"></i><span class="sr-only">Close</span></button>' : null;
            $return .= $flash['msg'];
            $return .= '</div>';
        }

        return $return;
    }
}
