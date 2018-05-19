<?php

namespace Controller;

/**
 * Index Controller.
 */
class Index extends \Base\Controller
{
    public function index(\Base $f3, $params)
    {
        // load spa if exists
        if (file_exists('public/ui/index.html')) {
            exit(\View::instance()->render('public/ui/index.html'));
        }

        //$this->user = new \Model\User($f3);

        //
        $f3->mset([
            'template' => $f3->get('AUTOLOAD').'view/template.php',
            'page'     => [
                'title' => 'Home',
                'body'  => \View::instance()->render('.api/view/index/index.php'),
            ],
        ]);
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
