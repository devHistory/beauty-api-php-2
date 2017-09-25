<?php


namespace MyApp\V1\Controllers;


use MyApp\V1\Models\Account;

class InitController extends ControllerBase
{

    // 初始化
    public function indexAction()
    {
    }


    protected function checkAccessToken()
    {
        if (!$this->request->get('token')) {
            return true;
        }

        parent::checkAccessToken();

        $accountModel = new Account();
        $accountModel->_getAccountDataFromCache($this->uid);
    }

}