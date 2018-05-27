<?php

namespace Controller\Api\Lxd\Images;

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
        
        $this->cache = \Cache::instance();
        $this->cache_ttl = 5;
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
         * GET /api/lxd/images?remote=local
         */
        if ($verb === 'GET') {
            // expect ?remote=local
            if ($f3->devoid('GET.remote')) {
                $f3->response->json([
                    'error' => 'Missing remote parameter',
                    'code'  => 400,
                    'data'  => []
                ]);
            }
            
            // cache remote images if not local
            if ($f3->get('GET.remote') === 'local' || !$this->cache->exists('images.'.$f3->get('GET.remote'), $result)) {
                // get images filter by architecture (may add as a parameter if ever needed)
                $result = $client->lxd->images->list($f3->get('GET.remote'), 'architecture="'.implode('|', ['x86_64', 'i686', 'amd64']).'"');
                //
                $this->cache->set('images.'.$f3->get('GET.remote'), $result, 3600);
            }

            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $result
            ]);
        }
        
        /**
         * POST /api/lxd/images
         */
        if ($verb === 'POST') {
            $options = json_decode($f3->get('BODY'), true);
            
            try {
                $response = [
                    'error' => null,
                    'code'  => 200,
                    'data'  => $client->lxd->images->create('local', $options)
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
         * PUT /api/lxd/images
         */
        if ($verb === 'PUT') {
            
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
         * GET /api/lxd/images/@fingerprint
         */
        if ($verb === 'GET') {
            /*
            // expect ?remote=local
            if ($f3->devoid('GET.remote')) {
                $f3->response->json([
                    'error' => 'Missing remote parameter',
                    'code'  => 400,
                    'data'  => []
                ]);
            }
            
            // cache remote images if not local
            if ($f3->get('GET.remote') === 'local' || !$this->cache->exists('images.'.$f3->get('GET.remote'), $result)) {
                // get images filter by architecture (may add as a parameter if ever needed)
                $result = $client->lxd->images->list($f3->get('GET.remote'), 'architecture="'.implode('|', ['x86_64', 'i686', 'amd64']).'"');
                //
                $this->cache->set('images.'.$f3->get('GET.remote'), $result, 3600);
            }

            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $result
            ]);
            */
        }
        
        /**
         * POST /api/lxd/images/@fingerprint
         */
        if ($verb === 'POST') {
            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => []
            ]);
        }
        
        /**
         * PUT /api/lxd/images/@fingerprint
         */
        if ($verb === 'PUT') {
            $options = json_decode($f3->get('BODY'), true);
            
            try {
                $response = [
                    'error' => null,
                    'code'  => 200,
                    'data'  => $client->lxd->images->replace($f3->get('GET.remote'), $params['fingerprint'], $options)
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
         * DELETE /api/lxd/images/@fingerprint?remote=local
         */
        if ($verb === 'DELETE') {
            // expect ?remote=local
            if ($f3->devoid('GET.remote')) {
                $f3->response->json([
                    'error' => 'Missing remote parameter',
                    'code'  => 400,
                    'data'  => []
                ]);
            }
            
            //
            try {
                $result = $client->lxd->images->delete($f3->get('GET.remote'), $params['fingerprint']);

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
