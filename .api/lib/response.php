<?php

namespace Lib;

class Response extends \Prefab
{
    public function __construct(\Base $f3)
    {
        $this->f3 = $f3;
    }

    public function json($data = null)
    {
        header('Content-Type: application/json;charset=utf8');
        exit(json_encode($data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
    }

    public function html()
    {
        if (!empty($this->f3->get('template'))) {
            echo \View::instance()->render($this->f3->get('template'));
        }
    }

    public function xml($data = null)
    {
    }

    public function csv($data = null)
    {
    }
}
