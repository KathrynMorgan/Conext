<?php

namespace Controller\Api\Lxd\Devices;

/**
 *
 */
class Nic extends \Base\Controller
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
         * GET /api/lxd/devices/nic
         */
        if ($verb === 'GET') {
            try {
                $result = $this->devices->findAll('type = "nic"');

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
         * POST /api/lxd/devices/nic
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
            if (empty($body['dict']['parent'])) {
                $errors['dict'] = [
                    'parent' => 'Device parent cannot be empty'
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
                    'type' => 'nic',
                    'name' => $body['name'],
                    'dict' => json_encode([
                        "nictype" => $body['dict']['nictype'],
                        "limits.ingress" => $body['dict']['limits.ingress'],
                        "limits.egress" => $body['dict']['limits.egress'],
                        "limits.max" => $body['dict']['limits.max'],
                        "name" => $body['dict']['name'],
                        "host_name" => $body['dict']['host_name'],
                        "hwaddr" => $body['dict']['hwaddr'],
                        "mtu" => $body['dict']['mtu'],
                        "vlan" => $body['dict']['vlan'],
                        "ipv4.address" => $body['dict']['ipv4.address'],
                        "ipv6.address" => $body['dict']['ipv6.address'],
                        "security.mac_filtering" => $body['dict']['security.mac_filtering'],
                        "maas.subnet.ipv4" => $body['dict']['maas.subnet.ipv4'],
                        "maas.subnet.ipv6" => $body['dict']['maas.subnet.ipv6'],
                        "parent" => $body['dict']['parent']
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
         * GET /api/lxd/devices/nic/@id
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
         * POST /api/lxd/devices/nic/@id
         */
        if ($verb === 'POST') {

        }
        
        /**
         * PUT /api/lxd/devices/nic/@id
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
            if (empty($body['dict']['parent'])) {
                $errors['dict'] = [
                    'parent' => 'Device parent cannot be empty'
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
                        'type' => 'nic',
                        'name' => $body['name'],
                        'dict' => json_encode([
                            "nictype" => $body['dict']['nictype'],
                            "limits.ingress" => $body['dict']['limits.ingress'],
                            "limits.egress" => $body['dict']['limits.egress'],
                            "limits.max" => $body['dict']['limits.max'],
                            "name" => $body['dict']['name'],
                            "host_name" => $body['dict']['host_name'],
                            "hwaddr" => $body['dict']['hwaddr'],
                            "mtu" => $body['dict']['mtu'],
                            "vlan" => $body['dict']['vlan'],
                            "ipv4.address" => $body['dict']['ipv4.address'],
                            "ipv6.address" => $body['dict']['ipv6.address'],
                            "security.mac_filtering" => $body['dict']['security.mac_filtering'],
                            "maas.subnet.ipv4" => $body['dict']['maas.subnet.ipv4'],
                            "maas.subnet.ipv6" => $body['dict']['maas.subnet.ipv6'],
                            "parent" => $body['dict']['parent']
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
         * DELETE /api/lxd/devices/nic/@id
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
