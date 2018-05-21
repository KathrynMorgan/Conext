<?php

namespace Controller\Api\Lxd;

/**
 *
 */
class Containers extends \Base\Controller
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
        
        // tasksource's are the parent to tasks
        //$this->tasksource = new \Base\Model('tasksource');
        // tasks are the child to tasksource's
        //$this->tasks = new \Base\Model('tasks');
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
         * GET /api/lxd/containers
         */
        if ($verb === 'GET') {
            // get containers
            $containers = $client->lxd->containers->list('local', function ($result) {
                return str_replace('/1.0/containers/', '', $result);
            });
            
            // get state
            $result = [];
            foreach ($containers as $i => $container) {
            	$result[$i] = $client->lxd->containers->getState('local', $container);
            	$result[$i]['name'] = $container;
            }
            
            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => array_values($result)
            ]);
        }
        
        /**
         * POST /api/lxd/containers
         */
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
                    (array) json_decode($item['params'])
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
                    (array) json_decode($item['params'])
                );
            }

            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => $response
            ]);
        }
        
        /**
         * PUT /api/lxd/containers
         */
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
        
        /**
         * DELETE /api/lxd/containers
         */
        if ($verb === 'DELETE') {
            $item = json_decode($f3->get('BODY'), true);
            
            if (empty($item) || !is_numeric($item['id'])) {
               $f3->response->json([
                    'error' => 'Invalid DELETE body, expecting item',
                    'code'  => 422,
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
        
        /**
         * GET /api/lxd/containers/@container
         */
        if ($verb === 'GET') {
            //
            $result = $client->lxd->containers->info('local', $params['container']);

            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => $result
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


// namespace Controller\Api\Lxd;

// /**
//  * 
//  */
// class Containers extends \Base\Controller
// {
//     public function beforeRoute(\Base $f3, $params)
//     {
//         try {
//             \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
//                 $f3->set('AUTH.server', $server);
//             });
//         } catch (\Exception $e) {
//             $f3->response->json([
//                 'error' => $e->getMessage(),
//                 'code'  => $e->getCode(),
//                 'data'  => []
//             ]);
//         }

//         $this->user = new \Model\User($f3);
//     }

//     /**
//      *
//      */
//     public function index(\Base $f3, $params)
//     {
//         $client = new \Plinker\Core\Client($f3->get('AUTH.server'), [
//             'secret' => $f3->get('AUTH.secret'),
//             'database' => $f3->get('db')
//         ]);
         
//         try {
//             $result = $client->lxd->containers->list('local', function ($result) {
//                 return str_replace('/1.0/containers/', '', $result);    
//             });
//             $f3->response->json([
//                 'error' => null,
//                 'code'  => 200,
//                 'data'  => $result
//             ]);
//         } catch (\Exception $e) {
//             $f3->response->json([
//                 'error' => $e->getMessage(),
//                 'code'  => $e->getCode(),
//                 'data'  => []
//             ]);
//         }
//     }

//     /**
//      *
//      */
//     public function action(\Base $f3, $params)
//     {
//         try {
//             \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
//                 $client = new \Plinker\Core\Client($server, [
//                     'secret' => $f3->get('AUTH.secret'),
//                     'database' => $f3->get('db')
//                 ]);

//                 $action = escapeshellarg($f3->get('PARAMS.action'));
//                 $container = escapeshellarg($f3->get('PARAMS.container'));

//                 $client->system->system->exec('sudo /usr/bin/lxc '.$action.' '.$container);

//                 $f3->response->json([
//                     'error' => null,
//                     'code'  => 200,
//                     'data'  => true
//                 ]);
//             });
//         } catch (\Exception $e) {
//             $f3->response->json([
//                 'error' => $e->getMessage(),
//                 'code'  => $e->getCode(),
//                 'data'  => []
//             ]);
//         }
//     }

//     /**
//      *
//      */
//     public function console(\Base $f3, $params)
//     {
//         try {
//             \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
//                 $client = new \Plinker\Core\Client($server, [
//                     'secret' => $f3->get('AUTH.secret'),
//                     'database' => $f3->get('db')
//                 ]);

//                 //$action = escapeshellarg($f3->get('PARAMS.action'));
//                 $container = $f3->get('PARAMS.container');

//                 $data = json_decode($client->system->system->exec('sudo /usr/bin/lxc query -X POST -d \''.$f3->get('BODY').'\' local:/1.0/containers/'.$container.'/exec'), true);

//                 $f3->response->json([
//                     'error' => null,
//                     'code'  => 200,
//                     'data'  => $data
//                     //'data'  => 'sudo /usr/bin/lxc query -X POST -d \''.$f3->get('BODY').'\' local:/1.0/containers/'.$container.'/exec --wait'
//                 ]);
//             });
//         } catch (\Exception $e) {
//             $f3->response->json([
//                 'error' => $e->getMessage(),
//                 'code'  => $e->getCode(),
//                 'data'  => []
//             ]);
//         }
//     }

// }
