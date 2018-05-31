<?php

namespace Controller\Api\Lxd\Containers;

/**
 *
 */
class Snapshots extends \Base\Controller
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
         * GET /api/lxd/containers/@name/snapshots
         */
        if ($verb === 'GET') {
            //
            try {
                $result = $client->lxd->containers->snapshots->list('local', $params['name'], function ($result) use ($params, $client) {
                    $return = [];
                    foreach ($result as $snapshot) {
                        $snapshot = str_replace('/1.0/containers/'.$params['name'].'/snapshots/', '', $snapshot);  
                        $return[] = $client->lxd->containers->snapshots->info('local', $params['name'], $snapshot);
                    }
                    return $return;
                });

                $response = [
                    'error' => null,
                    'code'  => 200,
                    'data'  => $result
                ];
            } catch (\Exception $e) {
                $response = [
                    'error' => $e->getMessage(),
                    'code'  => $e->getCode(),
                    'data'  => []
                ];
            }

            $f3->response->json($response);
        }
        
        /**
         * POST /api/lxd/containers/@name/snapshots
         */
        if ($verb === 'POST') {
            $body = json_decode($f3->get('BODY'), true);
            //
            $result = $client->lxd->containers->snapshots->create('local', $params['name'], $body);
            
            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => $result
            ]);
        }
        
        /**
         * PUT /api/lxd/containers/@name/snapshots
         */
        if ($verb === 'PUT') {
            $body = json_decode($f3->get('BODY'), true);
            //
            $result = $client->lxd->containers->snapshots->restore('local', $params['name'], $body['name']);
            
            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => $result
            ]);
        }
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
         * GET /api/lxd/containers/@name/snapshots/@snapshot
         */
        if ($verb === 'GET') {
            //
            try {
                $result = $client->lxd->containers->snapshots->info('local', $params['name'], $params['snapshot']);

                $response = [
                    'error' => null,
                    'code'  => 200,
                    'data'  => $result
                ];
            } catch (\Exception $e) {
                $response = [
                    'error' => $e->getMessage(),
                    'code'  => $e->getCode(),
                    'data'  => []
                ];
            }

            $f3->response->json($response);
        }
        
        /**
         * POST /api/lxd/containers/@name/snapshots/@snapshot
         */
        if ($verb === 'POST') {
            $body = json_decode($f3->get('BODY'), true);
            //
            try {
                $result = $client->lxd->containers->snapshots->rename('local', $params['name'], $params['snapshot'], ['name' => $body['name']]);

                $response = [
                    'error' => null,
                    'code'  => 200,
                    'data'  => $result
                ];
            } catch (\Exception $e) {
                $response = [
                    'error' => $e->getMessage(),
                    'code'  => $e->getCode(),
                    'data'  => []
                ];
            }

            $f3->response->json($response);
        }

        /**
         * DELETE /api/lxd/containers/@name/snapshots/@snapshot
         */
        if ($verb === 'DELETE') {
            //
            try {
                $result = $client->lxd->containers->snapshots->delete('local', $params['name'], $params['snapshot']);

                $response = [
                    'error' => null,
                    'code'  => 200,
                    'data'  => $result
                ];
            } catch (\Exception $e) {
                $response = [
                    'error' => $e->getMessage(),
                    'code'  => $e->getCode(),
                    'data'  => []
                ];
            }

            $f3->response->json($response);
        }
    }

}
