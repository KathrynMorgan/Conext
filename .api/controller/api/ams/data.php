<?php

namespace Controller\Api\Ams;

/**
 *
 */
class Data extends \Base\Controller
{
    public function beforeRoute(\Base $f3, $params)
    {
        // check auth
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
        
        $this->ams = new \Base\Model('ams');
    }

    /**
     *
     */
    public function index(\Base $f3, $params)
    {
        // GET | POST | PUT | DELETE
        $verb = $f3->get('VERB');
        
        if ($verb === 'GET') {
            $items = $this->ams->findAll();
            
            // force float 1.0 precision
            array_walk($items, function (&$value, $key) {
                if ($value['version'] === 1) {
                    $value['version'] = sprintf("%.1f", $value['version']);
                }
            });
        
            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => array_values($items)
            ]);
        }
        
        if ($verb === 'POST') {
            $item = json_decode($f3->get('BODY'), true);
           
            if (empty($item) || !is_numeric($item['id'])) {
               $f3->response->json([
                    'error' => 'Invalid POST body, expecting item',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            // new
            if ($item['id'] == -1) {
                $module = $this->ams->findOrCreate([
                    'module' => $item['module'],
                    'version' => $item['version']
                ]);
            } 
            // update
            else {
                $module = $this->ams->findOrCreate([
                    'id' => $item['id']
                ]);
                $module->module = $item['module'];
                $module->version = $item['version'];
            }

            $module->endpoint = $item['endpoint'];
            $module->source = $item['source'];
            $module->header = $item['header'];
            $module->auth = $item['auth'];
            $module->config = $item['config'];
            
            $this->ams->store($module);
            
            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => []
            ]);
        }
        
        if ($verb === 'PUT') {
            
        }
        
        if ($verb === 'DELETE') {
            $item = json_decode($f3->get('BODY'), true);
            
            if (empty($item) || !is_numeric($item['id'])) {
               $f3->response->json([
                    'error' => 'Invalid DELETE body, expecting item',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            $item = $this->ams->load($item['id']);
            
            $this->ams->trash($item);
            
            $f3->response->json([
                'error' => '',
                'code'  => 204,
                'data'  => []
            ]);
        }

    }

}
