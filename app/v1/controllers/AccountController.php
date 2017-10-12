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


    public function setAction()
    {
        $data = array_filter([
            'birthday' => $this->request->get('birthday', 'string'),    // 出生
            'gender'   => (int)$this->request->get('gender'),           // 性别   [1:男 2:女] (不可改)
            'location' => $this->request->get('location', 'string'),    // 所在地
            'hometown' => $this->request->get('hometown', 'string'),    // 家乡
            'desc'     => $this->request->get('desc', 'string'),        // 个人签名
            'avatar'   => $this->request->get('avatar', 'string'),      // 头像
            'height'   => (int)$this->request->get('height'),           // 身高
            'purpose'  => (int)$this->request->get('purpose'),          // 交友意向 [1:随缘 2:求撩 3:谈恋爱 4:交朋友 5:勿扰]
            'relation' => (int)$this->request->get('relation'),         // 情感状态 [1:单身 2:恋爱中 3:已婚 4:离异/丧偶]
            'sexual'   => (int)$this->request->get('sexual'),           // 性取向   [1:喜欢男 2:喜欢女 3:双性恋 4:无性恋]
        ]);

        // check
        if (!$data) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }
        if (isset($data['height']) && ($data['height'] > 240 || $data['height'] < 135)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }

        if (!$this->accountModel->setAccount($this->uid, $data)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('fail')])->send();
        }

        return $this->response->setJsonContent(['code' => 0, 'msg' => _('success')])->send();
    }

}