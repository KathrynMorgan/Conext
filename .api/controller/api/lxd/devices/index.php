<?php

namespace Controller\Api\Lxd\Devices;

/**
 *
 */
class Index extends \Base\Controller
{
    public function beforeRoute(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $f3->set('plinker', new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db'),
                    'lxc_path' => $f3->get('LXC.path')
                ]));
            });
        } catch (\Exception $e) {
            $f3->response->json([
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
                'data'  => []
            ]);
        }
        
        $this->devices = new \Base\Model('devices');
    }

    /**
     *
     */
    public function index(\Base $f3, $params)
    {
        // GET | POST | PUT | DELETE
        $verb = $f3->get('VERB');
        
        // plinker client
        $client = $f3->get('plinker');
        
        /**
         * GET /api/lxd/devices
         */
        if ($verb === 'GET') {
            
            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $this->devices->findAll();
            ]);
        }
        
        /**
         * POST /api/lxd/devices
         */
        if ($verb === 'POST') {
            /*
            $body = json_decode($f3->get('BODY'), true);
            
            $errors = [];
            if (empty($body['name'])) {
                $errors['name'] = 'Profiles name cannot be empty'; 
            }

            if (!empty($errors)) {
                $f3->response->json([
                    'error' => $errors,
                    'code'  => 400,
                    'data'  => []
                ]);
            }
            
            // fix devices (cast to object)
            if (isset($body['devices'])) {
                $body['devices'] = (object) $body['devices'];
            }
            
            try {
                $result = [
                    'error' => '',
                    'code'  => 200,
                    'data'  => $client->lxd->profiles->create('local', $body)
                ];
            } catch (\Exception $e) {
                $result = [
                    'error' => $e->getMessage(),
                    'code'  => 422,
                    'data'  => []
                ];
            }
            
            $f3->response->json($result);
            */
        }
        
        /**
         * PUT /api/lxd/devices
         */
        /*
        if ($verb === 'PUT') {
            $item = json_decode($f3->get('BODY'), true);
            
            if (empty($item) || !is_numeric($item['id'])) {
               $f3->response->json([
                    'error' => 'Invalid PUT body, expecting item',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => []
            ]);
        }
        */

        /**
         * DELETE /api/lxd/devices
         */
        /*
        if ($verb === 'DELETE') {
            $item = json_decode($f3->get('BODY'), true);
            
            if (empty($item) || !is_numeric($item['id'])) {
               $f3->response->json([
                    'error' => 'Invalid DELETE body, expecting item',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => []
            ]);
        }
        */
    }
    
}
