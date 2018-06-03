<?php

namespace Controller\Api\Lxd\Networks;

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
         * GET /api/lxd/networks
         */
        if ($verb === 'GET') {
            //
            $networks = $client->lxd->networks->list('local', function ($result) {
                return str_replace('/1.0/networks/', '', $result);
            });

            // get info
            $result = [];
            foreach ($networks as $i => $profile) {
            	$result[$i] = $client->lxd->networks->info('local', $profile);
            }

            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $result
            ]);
        }
        
        /**
         * POST /api/lxd/networks
         */
        if ($verb === 'POST') {
            $item = json_decode($f3->get('BODY'), true);
            
            if (empty($item)) {
               $f3->response->json([
                    'error' => 'Invalid POST body, expecting item',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            // fix config (cast to object)
            if (isset($item['config'])) {
                $item['config'] = (object) $item['config'];
            }

            try {
                try {
                    $result = $client->lxd->networks->create('local', $item);
                } catch (\Exception $e) {
                    throw $e;
                }

                $result = [
                    'error' => '',
                    'code'  => 200,
                    'data'  => $result
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
         * PUT /api/lxd/networks
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
         * DELETE /api/lxd/networks
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
         * GET /api/lxd/networks/@name
         */
        if ($verb === 'GET') {
            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $client->lxd->networks->info('local', $params['name'])
            ]);
        }
        
        /**
         * POST /api/lxd/networks/@name
         */
        if ($verb === 'POST') {
            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => []
            ]);
        }
        
        /**
         * PUT /api/lxd/networks/@name
         */
        if ($verb === 'PUT') {
            $item = json_decode($f3->get('BODY'), true);
            
            if (empty($item)) {
               $f3->response->json([
                    'error' => 'Invalid PUT body, expecting item',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }

            try {
                try {
                    $result = $client->lxd->networks->replace('local', $params['name'], $item);
                } catch (\Exception $e) {
                    throw $e;
                }

                $result = [
                    'error' => '',
                    'code'  => 200,
                    'data'  => $result
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
         * PATCH /api/lxd/networks/@name
         */
        if ($verb === 'PATCH') {
            /*
            $item = json_decode($f3->get('BODY'), true);
            
            if (empty($item) || !is_numeric($item['id'])) {
               $f3->response->json([
                    'error' => 'Invalid PATCH body, expecting item',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => []
            ]);
            */
        }
        
        /**
         * DELETE /api/lxd/networks/@name
         */
        if ($verb === 'DELETE') {
            try {
                try {
                    $result = $client->lxd->networks->delete('local', $params['name']);
                } catch (\Exception $e) {
                    throw $e;
                }

                $result = [
                    'error' => '',
                    'code'  => 200,
                    'data'  => $result
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
