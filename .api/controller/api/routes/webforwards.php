<?php

namespace Controller\Api\Routes;

/**
 *
 */
class Webforwards extends \Base\Controller
{
    public function beforeRoute(\Base $f3, $params)
    {
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
        
        if ($verb === 'GET') {
            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $client->nginx->fetch()
            ]);
        }
        
        if ($verb === 'POST') {
            $item = json_decode($f3->get('BODY'), true);
           
            if (empty($item) || !is_numeric($item['id'])) {
               $f3->response->json([
                    'error' => 'Invalid POST body, expecting item',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            // domains
            $domains = [];
            foreach ((array) $item['ownDomain'] as $domain) {
                if (empty($domain['name'])) {
                    continue;
                }
                $domains[] = $domain['name'];
            }
            // upstreams
            $upstreams = [];
            foreach ((array) $item['ownUpstream'] as $upstream) {
                if (empty($upstream['ip']) || empty($upstream['port'])) {
                    continue;
                }
                $upstreams[] = ['ip' => $upstream['ip'], 'port' => $upstream['port']];
            }
            
            // route
            $route = [
                'label' => $item['label'],
                'ownDomain' => $domains,
                'ownUpstream' => $upstreams,
                 'letsencrypt' => $item['letsencrypt'],
                'enabled' => 1
            ];
            
            // new
            if ($item['id'] == -1) {
                $client->nginx->add($route);
            } 
            // update
            else {
                $client->nginx->update('id = ? AND name = ?', [$item['id'], $item['name']], $route);
            }

            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => []
            ]);
        }
        
        if ($verb === 'PUT') {
            
        }
        
        if ($verb === 'DELETE') {
            $item = json_decode($f3->get('BODY'), true);
            
            if (empty($item) || !is_numeric($item['id'])) {
               $f3->response->json([
                    'error' => 'Invalid DELETE body, expecting item',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            $client->nginx->remove('id = ? AND name = ?', [$item['id'], $item['name']]);

            $f3->response->json([
                'error' => '',
                'code'  => 204,
                'data'  => []
            ]);
        }
    }

}
