<?php

namespace Controller\Api\Ams;

/**
 *
 */
class Email extends \Base\Controller
{
    public function beforeRoute(\Base $f3, $params)
    {
        // check auth
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {

            });
        } catch (\Exception $e) {
            $f3->response->json([
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
                'data'  => []
            ]);
        }
        
        $this->email_provider = new \Base\Model('amsemailprovider');
        $this->email_template = new \Base\Model('amsemailtemplate');
    }

    /**
     *
     */
    public function template(\Base $f3, $params)
    {
        // GET | POST | PUT | DELETE
        $verb = $f3->get('VERB');
        
        if ($verb === 'GET') {
            $result = $this->email_template->findAll();

            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => array_values($result)
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
                $row = $this->email_template->create([
                    'name' => $item['name'],
                    'slug' => \Web::instance()->slug($item['name']),
                    'type' => ($item['type'] === 'HTML' ? 'HTML' : 'Plain'),
                    'subject' => $item['subject'],
                    'from' => $item['from'],
                    'replyto' => $item['replyto'],
                    'key' => $item['key'],
                    'source' => $item['source']
                ]);
            } 
            // update
            else {
                $row = $this->email_template->load($item['id']);
                $row->import([
                    'name' => $item['name'],
                    'slug' => \Web::instance()->slug($item['name']),
                    'type' => ($item['type'] === 'HTML' ? 'HTML' : 'Plain'),
                    'subject' => $item['subject'],
                    'from' => $item['from'],
                    'replyto' => $item['replyto'],
                    'key' => $item['key'],
                    'source' => $item['source']
                ]);
            }

            $this->email_template->store($row);
            
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
            
            $row = $this->email_template->load($item['id']);
            
            $this->email_template->trash($row);
            
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
    public function provider(\Base $f3, $params)
    {
        // GET | POST | PUT | DELETE
        $verb = $f3->get('VERB');
        
        if ($verb === 'GET') {
            $result = $this->email_provider->findAll();
            
            $email_provider = $this->email_provider;
            //
            array_walk($result, function (&$value, $key) use ($email_provider) {
                $value['debug'] = $value['debug'] == '0' ? 'No' : 'Yes';
                
                $value = $email_provider->export($value, true);
            });

            $f3->response->json([
                'error' => '',
                'code'  => 200,
                'data'  => array_values($result)
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
                $row = $this->email_provider->create([
                    'host' => $item['host'],
                    'username' => $item['username'],
                    'password' => $item['password'],
                    'encryption' => $item['encryption'],
                    'port' => $item['port'],
                    'limit' => $item['limit'],
                    'limit_sent' => 0,
                    'limit_reset' => (new \DateTime())->modify('first day of next month'),
                    'debug' => ($item['debug'] === 'Yes')
                ]);
            } 
            // update
            else {
                $row = $this->email_provider->load($item['id']);
                // if host & username changes, reset counters/error
                if ($item['host'] != $row->host && $item['username'] != $row->username) {
                    $row->limit_sent = 0;
                    $row->limit_reset = (new \DateTime())->modify('first day of next month');
                    $row->last_error = '';
                }
                // remove all debug items if no on
                if ($item['debug'] !== 'Yes') {
                    $row->xownAmsemaildebugList = [];
                }
                $row->import([
                    'host' => $item['host'],
                    'username' => $item['username'],
                    'password' => $item['password'],
                    'encryption' => $item['encryption'],
                    'port' => $item['port'],
                    'limit' => $item['limit'],
                    'debug' => ($item['debug'] === 'Yes')
                ]);
            }

            $this->email_provider->store($row);
            
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
            
            $row = $this->email_provider->load($item['id']);
            
            $this->email_provider->trash($row);
            
            $f3->response->json([
                'error' => '',
                'code'  => 204,
                'data'  => []
            ]);
        }
        
    }

}
