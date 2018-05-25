<?php

namespace Controller;

/**
 * Index Controller
 */
class Index extends \Base\Controller
{
    public function index(\Base $f3, $params)
    {
        // load spa if exists
        if (file_exists('public/ui/index.html')) {
            exit(\View::instance()->render('public/ui/index.html'));
        }
        exit(header("HTTP/1.1 204 No Content"));
    }
    
    public function favicon(\Base $f3, $params)
    {
        // load spa if exists
        if (file_exists('ui/favicon.ico')) {
            \Web::instance()->send(
                'ui/favicon.ico', 'image/x-icon', 1024, false
            );
        }
    }
    
}
