<?php


namespace MyApp\V1\Controllers;


use MyApp\Services\TLSSigAPI;
use MyApp\V1\Models\Account;

class SyncController extends ControllerBase
{


    public function indexAction()
    {
        $accountModel = new Account();
        $syncData = [
            'uuid'    => $this->request->get('uuid'),
            'adid'    => $this->request->get('adid'),
            'lat'     => $this->request->get('lat'),
            'lng'     => $this->request->get('lng'),
            'os'      => $this->request->get('os'),
            'model'   => $this->request->get('model'),
            'channel' => $this->request->get('channel'),
            'ip'      => $this->request->getClientAddress(),
            'login'   => time(),
        ];

        // 检查缓存
        $key = '_account|' . $this->uid;
        $projection = ['name', 'gender', 'age', 'certify', 'level', 'avatar', 'desc', 'uuid'];

        if (!$this->cache->exists($key)) {
            // 查找用户数据
            $info = $accountModel->getAccountById($this->uid, $projection);
            if ($info) {
                $this->cache->set('_account|' . $this->uid, json_encode($info), 86400 * 14);
            }
            // 创建用户数据
            else {
                if (!$accountModel->createAccount($this->uid, $syncData)) {
                    return $this->response->setJsonContent(['code' => 0, 'msg' => _('FAI_LOGIN'),])->send();
                }
            }
        }

        // 信息同步
        $accountModel->setAccount($this->uid, $syncData);


        // IMToken
        $TLS = new TLSSigAPI();
        $TLS->setAppid($this->config->setting->IMAppId);
        $TLS->setPrivateKey(file_get_contents(BASE_DIR . $this->config->setting->IMPrivateKey));
        $IMToken = $TLS->genSig($this->uid);


        // 响应
        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('SUCCESS'),
            'data' => [
                'id'      => $this->uid,
                'IMToken' => $IMToken
            ]
        ])->send();
    }

}