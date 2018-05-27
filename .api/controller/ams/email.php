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
        $vars = $f3->get('POST');
        
        if (empty($vars)) {
            $vars = json_decode($f3->get('BODY'), true);
        }
        
        if (empty($vars['to'])) {
            $f3->response->json([
                'error' => 'You must provide at least one recipient with the [to] parameter',
                'code'  => 400,
                'data'  => []
            ]);
        }
        
        if (!empty($template->key)) {
            if (empty($vars['key'])) {
                $f3->response->json([
                    'error' => 'This template requires the [key] parameter',
                    'code'  => 400,
                    'data'  => []
                ]);
            } elseif ($vars['key'] != $template->key) {
                $f3->response->json([
                    'error' => 'Incorrect [key] parameter, unable to send email',
                    'code'  => 401,
                    'data'  => []
                ]);
            }
        }
        
        // match any single word with _ or -, with spaces either side or not
        // e.g: {{key}} or {{ key }} or {{key-foo}} or {{ key-foo }}
        // not {{ a b c }}
        $placeholder_regex = "/\{\{[ ]{0,}([\w\_-]{1,})[ ]{0,1}\}\}/";
        
        // replace source vars with posted values
        $subject = preg_replace_callback($placeholder_regex, function ($match) use ($vars) {
            return array_key_exists($match[1], $vars) ? $vars[$match[1]] : '';
        }, $template->subject);

        // replace source vars with posted values
        $source = preg_replace_callback($placeholder_regex, function ($match) use ($vars) {
            return array_key_exists($match[1], $vars) ? $vars[$match[1]] : '';
        }, $template->source);
        
        // send email
        if (isset($vars['preview'])) {
            exit($parsed);
        }

        $mail = new PHPMailer(true);                            // Passing `true` enables exceptions
        try {
            // Server settings
            $mail->SMTPDebug = !empty($provider->debug) ? 3 : 0;// Enable verbose debug output
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
                $debug_to = $vars['to'][0];
                foreach ($vars['to'] as $to) {
                    $mail->addAddress($to);
                }
            } else {
                $debug_to = $vars['to'];
                $mail->addAddress($vars['to']);
            }

            // Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');     // Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');// Optional name
        
            //Content
            $mail->isHTML($template->type === 'HTML');           // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $source;
            
            // strip out template so only the content is left
            $dom = new \DOMDocument();
            $dom->loadHTML('<?xml encoding="utf-8" ?>'.mb_convert_encoding($source, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            // remove style tags
            for ($list = $dom->getElementsByTagName('style'), $i = $list->length; --$i >=0;) {
                $node = $list->item($i);
                $node->parentNode->removeChild($node);
            }
            // tidy, leave br tags
            $mail->AltBody = strip_tags($dom->saveHTML(), '<br>');
            // tidy, switch br for new line, &nbsp; for space
            $mail->AltBody = str_replace(['<br>', '&nbsp;'], ["\n", ' '], $mail->AltBody);
            // tidy, remove multiple spaces
            $mail->AltBody = preg_replace('/\s+/', ' ', $mail->AltBody);
            $mail->AltBody = trim(str_replace(chr(194), ' ', $mail->AltBody));

            // is debug, start output buffering
            if (!empty($provider->debug)) {
                ob_start();
            }

            $mail->send();
            
            // is debug, write buffer to log
            if (!empty($provider->debug)) {
                $provider->xownAmsemaildebugList[] = $this->email_debug->create([
                    'from' => $template->from,
                    'to' => $debug_to,
                    'subject' => $subject,
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
            if (!empty($provider->debug)) {
                $provider->xownAmsemaildebugList[] = $this->email_debug->create([
                    'from' => $template->from,
                    'to' => $debug_to,
                    'subject' => $subject,
                    'date' => date_create(),
                    'log' => 'Mailer Error: '.$mail->ErrorInfo.PHP_EOL.'Exception: '.$e->getMessage(),
                ]);
            }
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