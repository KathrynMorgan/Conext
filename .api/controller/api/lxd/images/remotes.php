<?php

namespace Controller\Api\Lxd\Images;

/**
 *
 */
class Remotes extends \Base\Controller
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
         * GET /api/lxd/images/remotes
         */
        if ($verb === 'GET') {
            //
            $result = $client->lxd->images->remotes(function ($result) {
                return explode(PHP_EOL, $result);
            });

            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $result
            ]);
        }
        
    }

}
