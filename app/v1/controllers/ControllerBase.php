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


    public function afterExecuteRoute(Dispatcher $dispatcher)
    {
    }


    private function check()
    {
        $timestamp = $this->request->get('time');
        $signature = $this->request->get('sign');

        // check parameter
        if (!$timestamp || !$signature) {
            $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
            exit();
        }

        // check time
        if (abs(time() - $timestamp) > 300) {
            $this->response->setJsonContent(['code' => 1, 'msg' => _('timeout')])->send();
            exit();
        }

        // check signature
        $data = $this->request->get();
        unset($data['sign'], $data['_url']);
        if ($signature != $this->utilsService->createSign($data, $this->config->setting->signKey)) {
            $this->response->setJsonContent(['code' => 1, 'msg' => _('sign error')])->send();
            exit();
        }
    }


    protected function checkAccessToken()
    {
        try {
            JWT::$leeway = 300;
            $decoded = JWT::decode(
                $this->request->get('token'),
                $this->config->setting->signKey,
                array('HS256')
            );
            $this->uid = $decoded->accountId;
        } catch (Exception $e) {
            $this->response->setJsonContent(['code' => 1, 'msg' => _('token error')])->send();
            exit();
        }
    }

}