<?php

namespace Controller\Api\Lxd\Containers;

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
            $body = json_decode($f3->get('BODY'), true);
            
            $errors = [];
            if (empty($body['name'])) {
                $errors['name'] = 'Container name cannot be empty'; 
            }
            
            if (empty($body['image_fingerprint'])) {
                $errors['image_fingerprint'] = 'Image fingerprint cannot be empty'; 
            }
            
            if (empty($body['profile'])) {
                $errors['profile'] = 'Container requires at least one profile'; 
            }
            
            if (empty($body['remote'])) {
                $errors['remote'] = 'Remote cannot be empty'; 
            }
            
            if (!empty($errors)) {
                $f3->response->json([
                    'error' => $errors,
                    'code'  => 400,
                    'data'  => []
                ]);
            }
            
            $body['ephemeral'] = !empty($body['ephemeral']) ? ' -e ' : '';
            
            try {
                $result = [
                    'error' => '',
                    'code'  => 200,
                    'data'  => $client->lxd->local('lxc launch '.escapeshellarg($body['remote']).':'.escapeshellarg($body['image_fingerprint']).' '.escapeshellarg($body['name']).$body['ephemeral'].' -p '.implode(' -p ', $body['profile']))
                ];
            } catch (\Exception $e) {
                $result = [
                    'error' => 'Could not create container.',
                    'code'  => 422,
                    'data'  => []
                ];
            }
            
            $f3->response->json($result);
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
            
            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => []
            ]);
        }
        
        /**
         * DELETE /api/lxd/containers
         */
        if ($verb === 'DELETE') {
            // //
            // $result = $client->lxd->containers->list('local', function ($result) {
            //     return str_replace('/1.0/containers/', '', $result);
            // });
            
            // $f3->response->json([
            //     'error' => '',
            //     'code'  => 200,
            //     'data'  => []
            // ]);
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
         * GET /api/lxd/containers/@name
         */
        if ($verb === 'GET') {
            //
            $result = $client->lxd->containers->info('local', $params['name']);

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
            //
            $result = $client->lxd->containers->delete('local', $params['name']);
            
            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => []
            ]);
        }
    }

}
