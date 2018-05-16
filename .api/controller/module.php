<?php
namespace Controller;

/**
 * Index Controller.
 */
class Module extends \Base\Controller
{
    /**
     *
     */
    public function init(\Base $f3, $params)
    {
        //die('<pre>'.print_r($f3, true).'</pre>');

        //
        /*
        if (file_exists(getcwd().'/app/module/'.$params['module'].'/controller.php')) {
            $namespace = '\\Module\\'.ucfirst($params['module']).'\\Controller';
            $controller = new $namespace();
            if (!empty($params['action'])) {
                if (method_exists($controller, $params['action'])) {
                    return $controller->{$params['action']}($f3, $params);
                } else {
                    $f3->error(404);
                }
            } else {
                return $controller->index($f3, $params);
            }
        } else {
        */
            $namespace = '\\Controller\\Ams\\Controller';
            $controller = new $namespace();
        
            return $controller->index($f3, $params);
        
        
            if (!empty($params['module'])) {
                if (method_exists($controller, $params['module'])) {
                    return $controller->{$params['module']}($f3, $params);
                } else {
                    return $controller->index($f3, $params);
                }
            } else {
                return $controller->index($f3, $params);
            }
//        }
    }
}