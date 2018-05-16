<?php

namespace Controller\Api\Routes;

/**
 * 
 */
class Webforwards extends \Base\Controller
{
    public function beforeRoute(\Base $f3, $params)
    {
        $this->user = new \Model\User($f3);
    }

    /**
     *
     */
    public function index(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $client = new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db')
                ]);
                
                $route = [
                    'label' => 'Example',
                    'ownDomain' => [
                        'example.com',
                        'www.example.com'
                    ],
                    'ownUpstream' => [
                        ['ip' => '127.0.0.1', 'port' => '80']
                    ],
                    'letsencrypt' => 0,
                    'enabled' => 1
                ];
                //
                $client->nginx->manager->reset();
                $client->nginx->manager->add([$route]);

                $f3->response->json([
                    'error' => null,
                    'code'  => 200,
                    //'data'  => $client->nginx->manager->fetch(['domain'])
                    'data'  => $client->nginx->manager->fetch(['route'])
                    //'data'  => $client->nginx->manager->add([$route])
                ]);
            });
        } catch (\Exception $e) {
            $f3->response->json([
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
                'data'  => []
            ]);
        }
    }

}
