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
        exit(json_encode($data, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION));
    }

    public function html()
    {
        if (!empty($this->f3->get('template'))) {
            echo \View::instance()->render($this->f3->get('template'));
        }
    }
}
