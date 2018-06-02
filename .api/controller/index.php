<?php

namespace Controller;

/**
 * Index Controller
 */
class Index extends \Base\Controller
{
    /**
     * Handles sending the SPA entry page
     */
    public function index(\Base $f3, $params)
    {
        // load spa if exists, and enabled
        if (file_exists('public/ui/index.html') && !$f3->devoid('PANEL.enabled')) {
            exit(\View::instance()->render('public/ui/index.html'));
        } 
        // not enabled, with redirect
        elseif(!$f3->devoid('PANEL.redirect')) {
            exit(header("Location: ".$f3->get('PANEL.redirect')));
        } 
        // no content
        else {
            exit(header("HTTP/1.1 204 No Content"));
        }
    }
    
    /**
     * Handles favicon
     */
    public function favicon(\Base $f3, $params)
    {
        // load spa if exists
        if (file_exists('ui/favicon.ico')) {
            \Web::instance()->send(
                'ui/favicon.ico', 'image/x-icon', 1024, false
            );
        }
    }

    /**
     * Handles pong'ing back, for status check
     */
    public function ping(\Base $f3, $params)
    {
        die('pong');
    }
    
    /**
     * Handles upon login adding all known servers to db
     */
    public function sync(\Base $f3, $params)
    {
        try {
            \Lib\JWT::checkAuthThen(function ($server) use ($f3) {
                $model = new \Base\Model('servers');
                
                $items = json_decode($f3->get('BODY'), true);
                
                // drop current server list
                $model->exec('DELETE FROM servers');

                // add all items back
                foreach ($items as $item) {
                    // dont add self or error status
                    if ($server === $item['host'] || empty($item['status'])) {
                        continue;
                    }
                    
                    $row = $model->create([
                        'host' => $item['host']
                    ]);
                    
                    $row->secret = $item['secret'];
                    $row->label = $item['label'];
                    
                    $model->store($row);
                }
            });
        } catch (\Exception $e) {
            $f3->response->json([
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
                'data'  => []
            ]);
        }
        
    }
    
}
