<?php
namespace Controller\Ams;

/**
 * AMS Controller.
 */
class Controller extends \Base\Controller
{
    /**
     *
     */
    /*
    public function __construct()
    {
        parent::__construct();

        // load core models
        $this->user     = new \Framework\Model('user');
        $this->page     = new \Framework\Model('page');
        $this->menu     = new \Framework\Model('menu');
        $this->tasks    = new \Framework\Model('tasks');
        $this->module   = new \Framework\Model('module');
        $this->servers  = new \Framework\Model('servers');
        $this->objects  = new \Framework\Model('objects');
        $this->snippet  = new \Framework\Model('snippet');
        $this->template = new \Framework\Model('template');
        $this->settings = new \Framework\Model('settings');
        $this->tasksource = new \Framework\Model('tasksource');
        //
        $this->helper     = \Framework\Helper::instance();
    }
    */
    
    /**
     *
     */
    public function index(\Base $f3, $params)
    {
        $this->ams = new \Base\Model('ams');
        
        // load module
        $module = $this->ams->findOne('module = ? AND version = ?', [$params['module'], $params['version']]);
        
        // not found
        if (empty($module->id)) {
            $f3->error(404);
        }

        ob_start();
        echo (function ($module) use ($f3, $params) {
            /**
             * Pre-setup - extract and set variables for source to use
             */
            // set headers
            if (!empty($module->header)) {
                // define allowed headers, if None dont set
                $headers = [
                    'JSON' => 'application/json;charset=utf-8',
                    'HTML' => 'text/html;charset=utf-8',
                    'TEXT' => 'text/plain;charset=utf-8',
                    'JS'   => 'text/javascript;charset=utf-8',
                    'XML'  => 'application/xml;charset=utf-8'
                ];
                if (array_key_exists($module->header, $headers)) {
                    header('Content-Type: '.$headers[$module->header]);
                }
                unset($headers);
            }
            
            // check auth (JWT)
            if (!empty($module->auth) && $module->auth === 'JWT') {
                try {
                    \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                        
                    });
                } catch (\Exception $e) {
                    $f3->response->json([
                        'error' => $e->getMessage(),
                        'code'  => $e->getCode(),
                        'data'  => []
                    ]);
                }
            }
            
            // extract config
            if (!empty($module->config)) {
                $module->config = (array) json_decode($module->config, true);
                extract($module->config);
            }
            
            // set source into scope
            $source = @$module->source;
            
            // GET | POST | PUT | DELETE
            $verb = $f3->get('VERB');
            
            // unset module variable
            unset($module);
            
            try {
                return eval('?>'.@$source);
            } catch (\ParseError $e) {
                header('Content-Type: text/plain');
                return "API Error:\n".$e."\n";
            }
        })($module);
        exit(ob_get_clean());
        
        
        // url: /1.0/customers/123
        
        // url: /1.0/customers/edit/a/b/c/d
        
        // lookup version    = 1.0
        
        // lookup module     = customers
                
        // eval code:       
        //  - action         = edit
        //  - sub_action     = a
        //  - sub_action_id  = b
        //  - id             = c
        //  - sub_id         = d
    }

}