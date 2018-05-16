<?php

namespace Controller\Api;

/**
 * Index Controller.
 */
class Index extends \Base\Controller
{
    public function beforeRoute(\Base $f3, $params)
    {
        $this->user = new \Model\User($f3);
    }
    
    public function get(\Base $f3, $params)
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
                    //'data' => json_decode($client->system->system->exec('sudo /usr/bin/lxc query -X GET /1.0/containers'))
                    //'data' => $client->system->system->exec('sudo lxc list local: --format=json')
                    'data' => $client->system->system->enumerate([
                        //'disk_space',
                        //'total_disk_space',
                        //'memory_stats',
                        //'memory_total',
                        //'server_cpu_usage',
                        //'machine_id',
                        //'netstat',
                        //'arch',
                        //'hostname',
                        //'logins',
                        'pstree',
                        //'top',
                        //'uname',
                        //'cpuinfo',
                        //'load',
                        //'disks',
                        //'distro'
                    ])
                ]);

                //echo '<pre>'.print_r($client->info(), true).'</pre>';
                //echo '<pre>'.print_r($client->nginx->manager->status(), true).'</pre>';

                //echo '<pre>'.print_r($client->tasks->manager->getTasks(), true).'</pre>';
                //echo '<pre>'.print_r($client->system->system->disk_space(), true).'</pre>';
                //echo '<pre>'.print_r($client->system->system->cpuinfo(), true).'</pre>';
            });
        } catch (\Exception $e) {
            $f3->response->json([
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
            ]);
        }
    }

    public function post(\Base $f3, $params)
    {
    }

    public function put(\Base $f3, $params)
    {
    }

    public function delete(\Base $f3, $params)
    {
    }
}
