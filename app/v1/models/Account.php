<?php

namespace MyApp\V1\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;
use MongoDB\BSON\ObjectId;

class Account extends Model
{

    public function getAccountById($id = null)
    {
        if (!is_object($id)) {
            $id = new ObjectId($id);
        }
        $db = $this->di['config']->mongodb->db;
        if (!($result = $this->di['mongodb']->$db->accounts->findOne(['_id' => $id]))) {
            return false;
        }
        return $result;
    }


    // 获取缓存用户信息
    public function _getAccountDataFromCache($uid = '')
    {
        $key = '_account|' . $uid;
        $data = $this->di['cache']->get($key);
        if (!$data) {
            $account = $this->getAccountById($uid);
            $data = json_encode([
                'account' => $account->account,
                'name'    => isset($account->name) ? $account->name : '',
                'desc'    => isset($account->desc) ? $account->desc : '',
            ]);
            $this->di['cache']->set($key, $data, 86400 * 7);
        }
        return json_decode($data, true);
    }

}