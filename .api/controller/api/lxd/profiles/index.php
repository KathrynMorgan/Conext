<?php

namespace Controller\Api\Lxd\Profiles;

/**
 *
 */
class Index extends \Base\Controller
{
    public function beforeRoute(\Base $f3, $params)
    {
        // check auth
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                // set plinker client
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
         * GET /api/lxd/profiles
         */
        if ($verb === 'GET') {
            //
            $profiles = $client->lxd->profiles->list('local', function ($result) {
                return str_replace('/1.0/profiles/', '', $result);
            });
            
            // get state
            $result = [];
            foreach ($profiles as $i => $profile) {
            	$result[$i] = $client->lxd->profiles->info('local', $profile);
            }

            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $result
            ]);
        }
        
        /**
         * POST /api/lxd/profiles
         */
        if ($verb === 'POST') {
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
            if (!empty($body['devices'])) {
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
        }
        
        /**
         * PUT /api/lxd/profiles
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
         * DELETE /api/lxd/profiles
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
    
    /**
     *
     */
    public function item(\Base $f3, $params)
    {
        // GET | POST | PUT | DELETE
        $verb = $f3->get('VERB');
        
        // plinker client
        $client = $f3->get('plinker');
        
        /**
         * GET /api/lxd/profiles/@name
         */
        if ($verb === 'GET') {
            // get containers
            $result = $client->lxd->profiles->info('local', $params['name']);

            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $result
            ]);
        }
        
        /**
         * POST /api/lxd/profiles/@name
         */
        if ($verb === 'POST') {
            $body = json_decode($f3->get('BODY'), true);
            
            $errors = [];
            if (empty($params['name'])) {
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
            if (!empty($body['devices'])) {
                $body['devices'] = (object) $body['devices'];
            }
            
            try {
                $result = [
                    'error' => '',
                    'code'  => 200,
                    'data'  => $client->lxd->profiles->replace('local', $params['name'], $body)
                ];
            } catch (\Exception $e) {
                $result = [
                    'error' => $e->getMessage(),
                    'code'  => 422,
                    'data'  => []
                ];
            }
            
            $f3->response->json($result);
        }
        
        /**
         * PUT /api/lxd/profiles/@name
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
         * DELETE /api/lxd/profiles/@name
         */
        if ($verb === 'DELETE') {
            try {
                $result = [
                    'error' => '',
                    'code'  => 200,
                    'data'  => $client->lxd->profiles->delete('local', $params['name'])
                ];
            } catch (\Exception $e) {
                $result = [
                    'error' => $e->getMessage(),
                    'code'  => 422,
                    'data'  => []
                ];
            }
            
            $f3->response->json($result);
        }
    }

}
