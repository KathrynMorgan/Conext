<?php

namespace Controller\Api\Lxd\Operations;

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
                    'database' => $f3->get('db')
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
         * GET /api/lxd/operations
         */
        if ($verb === 'GET') {
            try {
                //
                $result = $client->lxd->operations->list('local', function ($result) use ($client) {
                    $return = [];
                    foreach ($result as $type => $operations) {
                        $return[$type] = [];
                        foreach ($operations as $operation) {
                            $row = str_replace('/1.0/operations/', '', $operation);  
                            $return[$type][] = $client->lxd->operations->info('local', $row);
                        }
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
        
    }
    
    /**
     * /api/lxd/operations/@uuid
     */
    public function item(\Base $f3, $params)
    {
        // GET | POST | PUT | DELETE
        $verb = $f3->get('VERB');
        
        // plinker client
        $client = $f3->get('plinker');
        
        /**
         * GET /api/lxd/operations/@uuid
         */
        if ($verb === 'GET') {
            // get containers
            $result = $client->lxd->operations->info('local',  $params['uuid']);

            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $result
            ]);
        }

        /**
         * DELETE /api/lxd/operations/@uuid
         */
        if ($verb === 'DELETE') {
            //
            try {
                $result = $client->lxd->operations->delete('local',  $params['uuid']);
            } catch (\Exception $e) {
                $result = $e->getMessage();
            }

            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => $result
            ]);
        }
    }

}
