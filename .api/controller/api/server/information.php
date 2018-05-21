<?php

namespace Controller\Api\Server;

/**
 * 
 */
class Information extends \Base\Controller
{
    public function beforeRoute(\Base $f3, $params)
    {
        $this->user = new \Model\User($f3);
    }

    /**
     *
     */
    public function networkConnections(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $client = new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db')
                ]);

                $f3->response->json([
                    'error' => null,
                    'code'  => 200,
                    'data'  => array_values((array) $client->system->netstat('-pant'))
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
    public function cpu(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $client = new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db')
                ]);

                $f3->response->json([
                    'error' => null,
                    'code'  => 200,
                    'data'  => $client->system->enumerate(['cpu_usage', 'cpu_info', 'load'])
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
    public function disks(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $client = new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db')
                ]);

                $data = $client->system->enumerate(['disk_space', 'total_disk_space', 'disks']);
                $data['disks'] = array_values($data['disks']);

                $f3->response->json([
                    'error' => null,
                    'code'  => 200,
                    'data'  => $data
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
    public function logins(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $client = new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db')
                ]);

                $data = $client->system->enumerate(['logins']);

                $f3->response->json([
                    'error' => null,
                    'code'  => 200,
                    'data'  => $data
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
    public function memory(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $client = new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db')
                ]);

                $data = $client->system->enumerate(['memory_stats', 'memory_total']);

                $f3->response->json([
                    'error' => null,
                    'code'  => 200,
                    'data'  => $data
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
    public function processTree(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $client = new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db')
                ]);

                $data = $client->system->enumerate(['pstree']);

                $f3->response->json([
                    'error' => null,
                    'code'  => 200,
                    'data'  => $data
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
    public function top(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $client = new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db')
                ]);

                $data['top'] = array_values($client->system->top());

                $f3->response->json([
                    'error' => null,
                    'code'  => 200,
                    'data'  => $data
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
