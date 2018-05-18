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
        if (file_exists('.dist/index.html')) {
            exit(\View::instance()->render('.dist/index.html'));
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
    
    public function foo(\Base $f3, $params)
    {
        // load spa if exists
        if (file_exists('.api/_dist/index.html')) {
            exit(\View::instance()->render('.api/_dist/index.html'));
        }

        //$this->user = new \Model\User($f3);

        //
        $f3->mset([
            'template' => '.api/view/template.php',
            'page'     => [
                'title' => 'Home',
                'body'  => \View::instance()->render('.api/view/index/index.php'),
            ],
        ]);
    }
}
