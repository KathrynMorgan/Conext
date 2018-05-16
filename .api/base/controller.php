<?php

namespace Base;

class Controller extends \Prefab
{
    public function beforeRoute(\Base $f3, $params)
    {
    }

    public function afterRoute(\Base $f3, $params)
    {
        $f3->response->html();
    }
}
