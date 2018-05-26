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
        
        // list of system tasks
        $this->system_tasks = [
            'iptables.setup',
            'iptables.build',
            'iptables.auto_update',
            'nginx.build',
            'nginx.auto_update',
            'nginx.reconcile',
            'nginx.reload',
            'nginx.setup',
            'tasks.auto_update',
            'database.vacuum',
            'database.backup'
        ];
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
            $result = [];
            foreach ($client->tasks->getTaskSources() as $row) {
                // decode params
                $row->params = !empty($row->params) ? json_decode($row->params, JSON_FORCE_OBJECT) : [];
                
                // grouping [user/system_tasks]
                if (in_array($row->name, $this->system_tasks)) {
                    $row->system = true;
                    $result['system'][] = $row;
                } else {
                    $row->system = false;
                    $result['user'][] = $row;
                }
            }

            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => [
                    'system_tasks' => $this->system_tasks,
                    'tasks' => $result
                ]
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

            // new
            if ($item['id'] == -1) {
                $response = $client->tasks->create(
                    $item['name'],
                    $item['source'],
                    strtolower($item['type']),
                    $item['description'],
                    (array) $item['params']
                );
            } 
            // update
            else {
                $response = $client->tasks->update(
                    $item['id'],
                    $item['name'],
                    $item['source'],
                    strtolower($item['type']),
                    $item['description'],
                    (array) $item['params']
                );
            }

            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => $response
            ]);
        }
        
        // run task
        if ($verb === 'PUT') {
            $item = json_decode($f3->get('BODY'), true);
            
            if (empty($item) || !is_numeric($item['id'])) {
               $f3->response->json([
                    'error' => 'Invalid PUT body, expecting item',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            $client->tasks->run($item['name'], [], 0);

            $f3->response->json([
                'error' => '',
                'code'  => 204,
                'data'  => []
            ]);
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
            
            // dont allow system task delete
            if (!empty($item['system']) || in_array($item['name'], $this->system_tasks)) {
               $f3->response->json([
                    'error' => 'System tasks should not be removed.',
                    'code'  => 400,
                    'data'  => []
                ]); 
            }
            
            $client->tasks->removeById($item['id']);

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

            // update run next
            $task->run_next = date_create($task->run_last)->modify("+".$item['sleep']." seconds")->format('Y-m-d H:i:s');
            // update repeats
            $task->repeats = !empty($item['sleep']);
            // update
            $task->sleep = $item['sleep'];
            
            $this->tasks->store($task);

            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => $task->fresh()
            ]);
        }
        
        if ($verb === 'PUT') {
            $item = json_decode($f3->get('BODY'), true);
           
            if (empty($item) || !is_numeric($params['id'])) {
               $f3->response->json([
                    'error' => 'Invalid PUT body, expecting item',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            // find task
            $task = $this->tasks->load($params['id']);

            // update run next
            $task->run_next = date_create($task->run_last)->modify("+".$item['sleep']." seconds")->format('Y-m-d H:i:s');
            // update repeats
            $task->repeats = !empty($item['sleep']);
            // update
            $task->sleep = $item['sleep'];
            // 
            $task->completed = 0;
            $task->run_count = 0;
            
            $this->tasks->store($task);

            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => $task->fresh()
            ]);
        }
        
        if ($verb === 'DELETE') {
            $item = json_decode($f3->get('BODY'), true);
            
            if (!is_numeric($params['id'])) {
               $f3->response->json([
                    'error' => 'Invalid DELETE id, expecting integer',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            $client->tasks->removeTasksLog($params['id']);

            $f3->response->json([
                'error' => '',
                'code'  => 204,
                'data'  => []
            ]);
        }
    }

}
