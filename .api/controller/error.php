<?php

namespace Controller;

/**
 * Index Controller.
 */
class Error extends \Base\Controller
{
    public function display(\Base $f3, $params)
    {
        //
        $f3->mset([
            'template' => 'src/view/template.php',
            'page'     => [
                'title' => $f3->get('ERROR.status'),
                'body'  => \View::instance()->render('src/view/error/'.$f3->get('ERROR.code').'.php'),
            ],
        ]);
    }
}
