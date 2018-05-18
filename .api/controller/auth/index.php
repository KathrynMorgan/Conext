<?php
namespace Controller\Auth;

/**
 * Auth Class
 * 
 */
class Index
{
    /**
     *
     */
    public function beforeRoute(\Base $f3, $params)
    {
        $this->user = new \Model\User($f3);

        $this->jwt_secret = hash('sha512', $f3->get('JWT.secret'));
        $this->jwt_ttl = 604800;
    }    

    /**
     *
     */
    public function index(\Base $f3, $params)
    {
        $post = json_decode($f3->get('BODY'), true);

        //
        if (isset($post['secret'])) {

            //$user = $this->user->findOne('username = ?', [$post['username']]);

            // check password
            $authenticated = false;
            if ($post['secret'] === $f3->get('AUTH.secret')) {
                $authenticated = true;
            }

            if ($authenticated) {
                $token = [];
                // Standard JWT Claims
                $token['iss'] = $_SERVER['HTTP_HOST'];  // issuer
                $token['iat'] = time();                 // issued at
                $token['exp'] = time() + $this->jwt_ttl;// expiration
                $token['aud'] = $_SERVER['HTTP_HOST'];  // audience
                $token['sub'] = $post['server'];        // subject
                $token['mod'] = $f3->get('modules');    // enabled modules

                // Additional Claims
                $token['role'] = 'user';

                $f3->response->json([
                    'exp' => $token['exp'],
                    'token' => \Lib\JWT::encode($token, $this->jwt_secret, 'HS512')
                ]);
            } else {
                http_response_code(401);
                $f3->response->json([
                    'error' => 'Unauthorized',
                    'code' => 401
                ]);
            }
        } else {
            $headers = \getallheaders();

            if (array_key_exists('authorization', $headers)) {
                $jwt = $headers['authorization'];

                // check/strip bearer prefix
                if (substr($jwt, 0, strlen('Bearer')) == 'Bearer') {
                    $jwt = trim(substr_replace($jwt, '', 0, strlen('Bearer')));
                } else {
                    http_response_code(401);
                    $f3->response->json([
                        'error' => 'Unauthorized',
                        'code' => 401
                    ]);
                }

                try {
                    $token = \Lib\JWT::decode($jwt, $this->jwt_secret);
                } catch (\Exception $e) {
                    http_response_code(401);
                    $f3->response->json([
                        'error' => 'Unauthorized',
                        'code' => 401
                    ]);
                }

                if ($token->exp >= time()) {
                    //loggedin
                    $f3->response->json(true);
                } else {
                    http_response_code(401);
                }
            } else {
                http_response_code(401);
            }

            $f3->response->json([
                'error' => 'Unauthorized',
                'code' => 401
            ]);
        }
    }

    /**
     * Extends/Renewal JWT
     */
    public function extend(\Base $f3, $params)
    {
        $post = json_decode($f3->get('BODY'), true);

        $headers = getallheaders();

        if (array_key_exists('authorization', $headers)) {
            $jwt = $headers['authorization'];

            // check/strip bearer prefix
            if (substr($jwt, 0, strlen('Bearer')) == 'Bearer') {
                $jwt = trim(substr_replace($jwt, '', 0, strlen('Bearer')));
            } else {
                http_response_code(401);
                $f3->response->json([
                    'error' => 'Unauthorized',
                    'code' => 401
                ]);
            }

            try {
                $token = \Lib\JWT::decode($jwt, $this->jwt_secret);
            } catch (\Exception $e) {
                http_response_code(401);
                $f3->response->json([
                    'error' => 'Unauthorized',
                    'code' => 401
                ]);
            }

            if ($post['exp'] === $token->exp) {
                $token = (array) $token;
                $token['iat'] = time();                 // issued at
                $token['exp'] = time() + $this->jwt_ttl;// expiration

                $f3->response->json([
                    'exp' => $token['exp'],
                    'token' => \Lib\JWT::encode($token, $this->jwt_secret, 'HS512')
                ]);
            } else {
                http_response_code(401);
            }
        } else {
            http_response_code(401);
        }

        $f3->response->json([
            'error' => 'Unauthorized',
            'code' => 401
        ]);
    }

}
