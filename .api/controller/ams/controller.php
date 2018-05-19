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
        /*
        $module = $this->ams->findOrCreate([
            'module' => $params['module'],
            'version' => $params['version'],
            'source' => '<?php echo json_encode(["Hello World"]); ?>',
            'config' => json_encode([
                
            ]),
            'headers' => json_encode([
                'Content-Type' => 'application/json'
            ]),
            'auth' => ''
        ]);
        */
        
        $module = $this->ams->find('module = ? AND version = ?', [$params['module'], $params['version']]);
        
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
            if (!empty($module->headers)) {
                $module->headers = json_decode($module->headers, true);
                foreach ((array) $module->headers as $key => $value) {
                    header($key.': '.$value);
                }
                unset($key, $value);
            }
            
            // check auth (jwt)
            if (!empty($module->auth) && $module->auth === 'jwt') {
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
                $module->config = json_decode($module->config, true);
                extract($module->config);
            }
            
            // set source into scope
            $source = @$module->source;
            
            // GET | POST | PUT | DELETE
            $verb = $f3->get('VERB');
            
            // unset module variable
            unset($module);
            
            return eval('?>'.@$source);
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