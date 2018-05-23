<?php

namespace Controller\Api\Server;

/**
 * 
 */
class Information extends \Base\Controller
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

        $this->cache = \Cache::instance();
        $this->cache_ttl = 5;
    }

    /**
     *
     */
    public function networkConnections(\Base $f3, $params)
    {
        if (!$this->cache->exists(__FUNCTION__, $data)) {
            // plinker client
            $client = $f3->get('plinker');
            //
            $data = array_values((array) $client->system->netstat('-pant'));
            //
            $this->cache->set(__FUNCTION__, $data, $this->cache_ttl);
        }
        
        $f3->response->json([
            'error' => null,
            'code'  => 200,
            'data'  => $data
        ]);
    }

    /**
     *
     */
    public function cpu(\Base $f3, $params)
    {
        if (!$this->cache->exists(__FUNCTION__, $data)) {
            // plinker client
            $client = $f3->get('plinker');
            //
            $data = $client->system->enumerate(['cpu_usage', 'cpu_info', 'load']);
            //
            $this->cache->set(__FUNCTION__, $data, $this->cache_ttl);
        }
        
        $f3->response->json([
            'error' => null,
            'code'  => 200,
            'data'  => $data
        ]);
    }

    /**
     *
     */
    public function disks(\Base $f3, $params)
    {
        if (!$this->cache->exists(__FUNCTION__, $data)) {
            // plinker client
            $client = $f3->get('plinker');
            //
            $data = $client->system->enumerate(['disk_space', 'total_disk_space', 'disks']);
            $data['disks'] = array_values((array) $data['disks']);
            //
            $this->cache->set(__FUNCTION__, $data, $this->cache_ttl);
        }
        
        $f3->response->json([
            'error' => null,
            'code'  => 200,
            'data'  => $data
        ]);
    }

    /**
     *
     */
    public function logins(\Base $f3, $params)
    {
        if (!$this->cache->exists(__FUNCTION__, $data)) {
            // plinker client
            $client = $f3->get('plinker');
            //
            $data = $client->system->enumerate(['logins']);
            //
            $this->cache->set(__FUNCTION__, $data, $this->cache_ttl);
        }
        
        $f3->response->json([
            'error' => null,
            'code'  => 200,
            'data'  => $data
        ]);
    }  

    /**
     *
     */
    public function memory(\Base $f3, $params)
    {
        if (!$this->cache->exists(__FUNCTION__, $data)) {
            // plinker client
            $client = $f3->get('plinker');
            //
            $data = $client->system->enumerate(['memory_stats', 'memory_total']);
            //
            $this->cache->set(__FUNCTION__, $data, $this->cache_ttl);
        }
        
        $f3->response->json([
            'error' => null,
            'code'  => 200,
            'data'  => $data
        ]);
    }
    
    /**
     *
     */
    public function processTree(\Base $f3, $params)
    {
        if (!$this->cache->exists(__FUNCTION__, $data)) {
            // plinker client
            $client = $f3->get('plinker');
            //
            $data = $client->system->enumerate(['pstree']);
            //
            $this->cache->set(__FUNCTION__, $data, $this->cache_ttl);
        }
        
        $f3->response->json([
            'error' => null,
            'code'  => 200,
            'data'  => $data
        ]);
    }
    
    /**
     *
     */
    public function top(\Base $f3, $params)
    {
        if (!$this->cache->exists(__FUNCTION__, $data)) {
            // plinker client
            $client = $f3->get('plinker');
            //
            $data = array_values($client->system->top());
            //
            $this->cache->set(__FUNCTION__, $data, $this->cache_ttl);
        }
        
        $f3->response->json([
            'error' => null,
            'code'  => 200,
            'data'  => $data
        ]);
    }
}
