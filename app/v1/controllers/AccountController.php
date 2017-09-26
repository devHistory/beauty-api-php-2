<?php


namespace MyApp\V1\Controllers;


use MyApp\V1\Models\Account;

class AccountController extends ControllerBase
{
    private $accountModel;

    public function initialize()
    {
        parent::initialize();
        $this->accountModel = new Account();
    }


    public function indexAction()
    {
    }


    // 设置昵称
    public function setNameAction()
    {
        $name = $this->request->get('name', 'string');
        if (!$name) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }
        if (strlen($name) < 3 || strlen($name) > 30) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('length error')])->send();
        }

        if (!$this->accountModel->setName($this->uid, $name)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('name exist')])->send();
        }

        return $this->response->setJsonContent(['code' => 0, 'msg' => _('success')])->send();
    }

}