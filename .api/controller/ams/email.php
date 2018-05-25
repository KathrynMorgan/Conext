<?php
namespace Controller\Ams;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * AMS Email Controller
 * @route: /api/email
 */
class Email extends \Base\Controller
{
    /**
     * 
     */
    public function beforeRoute(\Base $f3, $params)
    {
        /*
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
        */
        
        $this->email_provider = new \Base\Model('amsemailprovider');
        $this->email_template = new \Base\Model('amsemailtemplate');
        $this->email_debug = new \Base\Model('amsemaildebug');
    }
    
    /**
     *
     */
    public function index(\Base $f3, $params)
    {
        // get provider with least sent and within quota
        $provider = $this->email_provider->findOne('`limit` > `limit_sent` ORDER BY `limit_sent` DESC LIMIT 1');

        if (empty($provider)) {
            $f3->response->json([
                'error' => 'No available providers',
                'code'  => 404,
                'data'  => []
            ]);
        }

        // get template
        $template = $this->email_template->findOne('slug = ?', [$params['slug']]);
        
        // get POST params
        $vars = $f3->get('REQUEST');
        
        if (empty($vars['to'])) {
            $f3->response->json([
                'error' => 'You must provide at least one recipient with the [to] parameter',
                'code'  => 400,
                'data'  => []
            ]);
        }
        
        // replace source vars with posted values
        $subject = preg_replace_callback("/\{\{([\w\_]{1,})\}\}/", function ($match) use ($vars) {
            return array_key_exists($match[1], $vars) ? $vars[$match[1]] : '';
        }, $template->subject);

        // replace source vars with posted values
        $source = preg_replace_callback("/\{\{([\w\_]{1,})\}\}/", function ($match) use ($vars) {
            return array_key_exists($match[1], $vars) ? $vars[$match[1]] : '';
        }, $template->source);
        
        // send email
        if (isset($vars['preview'])) {
            exit($parsed);
        }

        $mail = new PHPMailer(true);                            // Passing `true` enables exceptions
        try {
            // Server settings
            $mail->SMTPDebug = !empty($provider->debug) ? 2 : 0;// Enable verbose debug output
            $mail->isSMTP();                                    // Set mailer to use SMTP
            $mail->Host = $provider->host;                      // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                             // Enable SMTP authentication
            $mail->Username = $provider->username;              // SMTP username
            $mail->Password = $provider->password;              // SMTP password
            $mail->SMTPSecure = ($provider->encryption === 'None' ? null : strtolower($provider->encryption)); // Enable TLS encryption, `ssl` also accepted
            $mail->Port = (int) $provider->port;                // TCP port to connect to
        
            // from & reply to
            $mail->setFrom($template->from);
            if (!empty($template->replyto)) {
                $mail->addReplyTo($template->replyto);
            }

            // to
            if (is_array($vars['to'])) {
                foreach ($vars['to'] as $to) {
                    $mail->addAddress($to);
                }
            } else {
                $mail->addAddress($vars['to']);
            }

            // Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');     // Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');// Optional name
        
            //Content
            $mail->isHTML($template->type === 'HTML');           // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $source;
            $mail->AltBody = $source;

            if (!empty($provider->debug)) {
                ob_start();
            }

            $mail->send();
            
            if (!empty($provider->debug)) {
                $provider->xownAmsemaildebugList[] = $this->email_debug->create([
                    'date' => date_create(),
                    'log' => ob_get_clean(),
                ]);
            }

            // update sent
            $provider->limit_sent++;
            $this->email_provider->store($provider);
            

            $f3->response->json([
                'error' => null,
                'code'  => 200,
                'data'  => 'Message sent'
            ]);
        } catch (\Exception $e) {
            // update sent
            $provider->last_error = $mail->ErrorInfo;
            $this->email_provider->store($provider);
            
            $f3->response->json([
                'error' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo,
                'code'  => 500,
                'data'  => null
            ]);
        }
    }

}