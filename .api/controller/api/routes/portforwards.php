<?php

namespace Controller\Api\Routes;

/**
 *
 */
class Portforwards extends \Base\Controller
{
    public function beforeRoute(\Base $f3, $params)
    {
        //$this->user = new \Model\User($f3);
        
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
        
        $this->ams = new \Base\Model('ams');
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
                'data'  => $client->iptables->fetch('type = ?', ['forward'])
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

            // route
            $route = [
                'label' => $item['label'],
                'name' => $item['name'],
                'ip' => $item['ip'],
                'port' => $item['port'],
                'srv_type' => $item['srv_type'],
                'srv_port' => $item['srv_port'],
                'enabled' => 1
            ];
            
            // new
            if ($item['id'] == -1) {
                $response = $client->iptables->addForward($route);
            } 
            // update
            else {
                $response = $client->iptables->updateForward('id = ? AND name = ?', [$item['id'], $item['name']], $route);
            }

            $f3->response->json([
                'error' => $response['status'],
                'code'  => 200,
                'data'  => $response['values']
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
            
            $client->iptables->remove('id = ? AND name = ? AND type = ?', [$item['id'], $item['name'], 'forward']);

            $f3->response->json([
                'error' => '',
                'code'  => 204,
                'data'  => []
            ]);
        }
    }

}