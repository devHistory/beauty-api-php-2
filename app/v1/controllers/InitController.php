<?php


namespace MyApp\V1\Controllers;


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

        $this->component->fillUserFromCache([$this->uid]);
    }

}