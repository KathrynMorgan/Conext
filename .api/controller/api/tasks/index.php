<?php

namespace Controller\Api\Tasks;

/**
 *
 */
class Index extends \Base\Controller
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
        
        // tasksource's are the parent to tasks
        $this->tasksource = new \Base\Model('tasksource');
        // tasks are the child to tasksource's
        $this->tasks = new \Base\Model('tasks');
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
                'data'  => array_values($client->tasks->getTaskSources())
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
    
    /**
     *
     */
    public function item(\Base $f3, $params)
    {
        // GET | POST | PUT | DELETE
        $verb = $f3->get('VERB');
        
        // plinker client
        $client = $f3->get('plinker');
        
        if ($verb === 'GET') {
            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => array_values($client->tasks->getTasksLog((int) $params['id']))
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
            
            // find task
            $task = $this->tasks->load($item['id']);
            
            // validate sleep value, return current value on failure
            if (!is_numeric($item['sleep']) && !is_int($item['sleep'])) {
                $f3->response->json([
                    'error' => 'Invalid sleep value, expected integer',
                    'code'  => 422,
                    'data'  => ['sleep' => $task->sleep]
                ]); 
            } elseif ($item['sleep'] > 31557600) {
                $f3->response->json([
                    'error' => 'Invalid sleep value, must be less than 31557600',
                    'code'  => 422,
                    'data'  => ['sleep' => $task->sleep]
                ]); 
            }
            
            // find task
            $task = $this->tasks->load($item['id']);
            
            // update run next
            $task->run_next = date_create($task->run_last)->modify("+".$item['sleep']." seconds")->format('Y-m-d H:i:s');

            // update
            $task->sleep = $item['sleep'];
            
            $this->tasks->store($task);

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
            
            $client->iptables->remove('id = ? AND name = ? AND type = ?', [$item['id'], $item['name'], 'forward']);

            $f3->response->json([
                'error' => '',
                'code'  => 204,
                'data'  => []
            ]);
        }
    }

}
