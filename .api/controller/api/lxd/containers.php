<?php

namespace Controller\Api\Lxd;

/**
 * 
 */
class Containers extends \Base\Controller
{
    public function beforeRoute(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $f3->set('AUTH.server', $server);
            });
        } catch (\Exception $e) {
            $f3->response->json([
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
                'data'  => []
            ]);
        }

        $this->user = new \Model\User($f3);
    }

    /**
     *
     */
    public function index(\Base $f3, $params)
    {
        $client = new \Plinker\Core\Client($f3->get('AUTH.server'), [
            'secret' => $f3->get('AUTH.secret'),
            'database' => $f3->get('db')
        ]);
         
        try {
            $json = $client->lxd->exec('sudo /usr/bin/lxc list --format="json"');
            $json = $f3->get('helper')->json_validate($json, true);
            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $json
            ]);
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
    public function action(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $client = new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db')
                ]);

                $action = escapeshellarg($f3->get('PARAMS.action'));
                $container = escapeshellarg($f3->get('PARAMS.container'));

                $client->system->system->exec('sudo /usr/bin/lxc '.$action.' '.$container);

                $f3->response->json([
                    'error' => null,
                    'code'  => 200,
                    'data'  => true
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

    /**
     *
     */
    public function console(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $client = new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db')
                ]);

                //$action = escapeshellarg($f3->get('PARAMS.action'));
                $container = $f3->get('PARAMS.container');

                $data = json_decode($client->system->system->exec('sudo /usr/bin/lxc query -X POST -d \''.$f3->get('BODY').'\' local:/1.0/containers/'.$container.'/exec'), true);

                $f3->response->json([
                    'error' => null,
                    'code'  => 200,
                    'data'  => $data
                    //'data'  => 'sudo /usr/bin/lxc query -X POST -d \''.$f3->get('BODY').'\' local:/1.0/containers/'.$container.'/exec --wait'
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
