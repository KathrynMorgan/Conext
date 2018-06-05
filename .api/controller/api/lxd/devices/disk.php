<?php

namespace Controller\Api\Lxd\Devices;

/**
 *
 */
class Disk extends \Base\Controller
{
    public function beforeRoute(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $f3->set('plinker', new \Plinker\Core\Client($server, [
                    'secret' => $f3->get('AUTH.secret'),
                    'database' => $f3->get('db'),
                    'lxc_path' => $f3->get('LXC.path')
                ]));
            });
        } catch (\Exception $e) {
            $f3->response->json([
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
                'data'  => []
            ]);
        }
        
        $this->devices = new \Base\Model('devices');
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
         * GET /api/lxd/devices/disk
         */
        if ($verb === 'GET') {
            try {
                $result = $this->devices->findAll('type = "disk"');

                foreach ($result as &$row) {
                    $row['dict'] = json_decode($row['dict']);
                }
            } catch (\Exception $e) {
                $result = [];
            }
            
            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => array_values($result)
            ]);
        }
        
        /**
         * POST /api/lxd/devices/disk
         */
        if ($verb === 'POST') {
            $body = json_decode($f3->get('BODY'), true);
            
            if (empty($body) || !is_numeric($body['id'])) {
               $f3->response->json([
                    'error' => 'Invalid PUT body',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            $body = $f3->recursive($body, function($value) {
            	return trim($value);
            });

            $errors = [];
            //
            if (empty($body['name'])) {
                $errors['name'] = 'Device name cannot be empty';
            }

            //
            if (empty($body['dict']['path'])) {
                $errors['dict'] = [
                    'path' => 'Device path cannot be empty'
                ];
            }
            
            //
            if (empty($body['dict']['source'])) {
                $errors['dict'] = [
                    'source' => 'Device source cannot be empty'
                ];
            }

            if (!empty($errors)) {
               $f3->response->json([
                    'error' => $errors,
                    'code'  => 422,
                    'data'  => []
                ]); 
            }

            try {
                $result = $this->devices->create(['name' => $body['name']]);
 
                $result->import([
                    'type' => 'disk',
                    'name' => $body['name'],
                    'dict' => json_encode([
                        "limits.read" => $body['dict']['limits.read'],
                        "limits.write" => $body['dict']['limits.write'],
                        "path" => $body['dict']['path'],
                        "source" => $body['dict']['source'],
                        "readonly" => $body['dict']['readonly'],
                        "optional" => $body['dict']['optional'],
                        "size" => $body['dict']['size'],
                        "recursive" => $body['dict']['recursive'],
                        "pool" => $body['dict']['pool'],
                        "propagation" => $body['dict']['propagation']
                    ], JSON_PRETTY_PRINT)
                ]);
                    
                $this->devices->store($result);
            } catch (\Exception $e) {
                $result = [];
            }
            
            $f3->response->json([
                'error' => null,
                'code'  => 200,
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
         * GET /api/lxd/devices/disk/@id
         */
        if ($verb === 'GET') {
            try {
                $result = $this->devices->load($params['id']);
                
                if (!empty($result->id)) {
                    $result['dict'] = json_decode($result['dict']);
                } else {
                    throw \Exception('Not found', 404);
                }
            } catch (\Exception $e) {
                $result = [];
            }
            
            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => array_values($result)
            ]);
        }
        
        /**
         * POST /api/lxd/devices/disk/@id
         */
        if ($verb === 'POST') {

        }
        
        /**
         * PUT /api/lxd/devices/disk/@id
         */
        if ($verb === 'PUT') {
            $body = json_decode($f3->get('BODY'), true);
            
            if (empty($body) || !is_numeric($body['id'])) {
               $f3->response->json([
                    'error' => 'Invalid PUT body',
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            $body = $f3->recursive($body, function($value) {
            	return trim($value);
            });
            
            $errors = [];
            //
            if (empty($body['name'])) {
                $errors['name'] = 'Device name cannot be empty';
            }

            //
            if (empty($body['dict']['path'])) {
                $errors['dict'] = [
                    'path' => 'Device path cannot be empty'
                ];
            }
            
            //
            if (empty($body['dict']['source'])) {
                $errors['dict'] = [
                    'source' => 'Device source cannot be empty'
                ];
            }

            if (!empty($errors)) {
               $f3->response->json([
                    'error' => $errors,
                    'code'  => 422,
                    'data'  => []
                ]); 
            }
            
            try {
                $result = $this->devices->load($params['id']);
                
                if (!empty($result->id)) {
                    
                    $result->import([
                        'type' => 'disk',
                        'name' => $body['name'],
                        'dict' => json_encode([
                            "limits.read" => $body['dict']['limits.read'],
                            "limits.write" => $body['dict']['limits.write'],
                            "path" => $body['dict']['path'],
                            "source" => $body['dict']['source'],
                            "readonly" => $body['dict']['readonly'],
                            "optional" => $body['dict']['optional'],
                            "size" => $body['dict']['size'],
                            "recursive" => $body['dict']['recursive'],
                            "pool" => $body['dict']['pool'],
                            "propagation" => $body['dict']['propagation']
                        ], JSON_PRETTY_PRINT)
                    ]);
                    
                    $this->devices->store($result);
                    
                    $result['dict'] = json_decode($result['dict']);
                } else {
                    throw new \Exception('Not found', 404);
                }
            } catch (\Exception $e) {
                $result = [];
            }
            
            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => []
            ]);
        }

        /**
         * DELETE /api/lxd/devices/disk/@id
         */
        if ($verb === 'DELETE') {
            try {
                $result = $this->devices->load($params['id']);
                
                if (!empty($result->id)) {
                    $this->devices->trash($result);
                } else {
                    throw \Exception('Not found', 404);
                }
            } catch (\Exception $e) {
                $result = [];
            }

            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => []
            ]);
        }
    }
    
}
