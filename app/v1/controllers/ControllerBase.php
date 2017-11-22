<?php


namespace MyApp\V1\Controllers;


use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use Firebase\JWT\JWT;
use Exception;

class ControllerBase extends Controller
{

    public $uid;


    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
        $lang = $this->request->get('lang');
        if ($lang) {
            setlocale(LC_ALL, $lang);
            $domain = $lang;
            bind_textdomain_codeset($domain, 'UTF-8');
            bindtextdomain($domain, APP_DIR . '/lang');
            textdomain($domain);
        }
    }


    public function initialize()
    {
        $this->check();
        $this->checkAccessToken();
    }


    public function indexAction()
    {
        $do = $this->dispatcher->getParam('do');
        $this->$do();
    }


    public function afterExecuteRoute(Dispatcher $dispatcher)
    {
    }


    private function check()
    {
        $timestamp = $this->request->getHeader('time');
        $signature = $this->request->getHeader('sign');

        // check parameter
        if (!$timestamp || !$signature) {
            $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_ARGV')])->send();
            exit();
        }

        // check time
        if (abs(time() - $timestamp) > 300) {
            $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_TIME')])->send();
            exit();
        }

        // check signature
        $header = array_filter([
            'app'     => $this->request->getHeader('app'),
            'version' => $this->request->getHeader('version'),
            'lang'    => $this->request->getHeader('lang'),
            'time'    => $this->request->getHeader('time'),
            'token'   => $this->request->getHeader('token')
        ]);
        ksort($header);
        $authData = http_build_query($header) . $_SERVER['REQUEST_METHOD'] . $_SERVER['REQUEST_URI'] . md5(file_get_contents("php://input"));
        $authorization = base64_encode(hash_hmac('sha1', $authData, $this->config->setting->signKey, true));
        unset($header, $authData);
        if ($signature != $authorization) {
            $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_SIGN')])->send();
            exit();
        }
    }


    protected function checkAccessToken()
    {
        try {
            JWT::$leeway = 300;
            $decoded = JWT::decode(
                $this->request->getHeader('token'),
                $this->config->setting->signKey,
                array('HS256')
            );
            $this->uid = $decoded->id;
        } catch (Exception $e) {
            $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_TOKEN')])->send();
            exit();
        }
    }

}