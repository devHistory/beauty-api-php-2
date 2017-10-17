<?php

namespace MyApp\V1\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;
use MongoDB\BSON\ObjectId;
use Exception;

class Account extends Model
{


    // 设置昵称
    public function setName($uid = '', $name = '')
    {
        if (!$name) {
            return false;
        }

        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        // find account
        $account = $mongodb->$db->accounts->findOne(
            ['_id' => new ObjectId($uid)]
        );
        if (!$account) {
            return false;
        }
        if (isset($account->name) && $name == $account->name) {
            return true;
        }

        try {
            $mongodb->$db->nickname->insertOne([
                '_id' => md5($name),
                'uid' => $uid
            ]);
            $mongodb->$db->accounts->updateOne(
                ['_id' => new ObjectId($uid)],
                ['$set' => ['name' => $name]]
            );
            if (isset($account->name)) {
                $mongodb->$db->nickname->deleteOne(['_id' => md5($account->name)]);
            }

            // delete cache
            $this->di['cache']->del('_account|' . $uid);

        } catch (Exception $e) {
            return false;
        }

        return true;
    }


    /**
     * 设置密码
     * @param string $uid
     * @param string $oldPass
     * @param string $newPass
     * @return bool
     */
    public function setPass($uid = '', $oldPass = '', $newPass = '')
    {
        if (!($account = $this->getAccountById($uid, ['account']))) {
            return false;
        }
        $_id = $account['account'];
        $db = $this->di['config']->mongodb->db;
        $loginData = $this->di['mongodb']->$db->login->findOne(['_id' => $_id]);

        // 首次设置密码
        if (empty($oldPass) && empty($loginData['password'])) {
            $this->di['mongodb']->$db->login->updateOne(
                ['_id' => $_id],
                [
                    '$set' => ['password' => password_hash($newPass, PASSWORD_DEFAULT)]
                ]
            );
            return true;
        }

        // 修改密码
        if ($oldPass && !empty($loginData['password'])) {
            if (!password_verify($oldPass, $loginData['password'])) {
                return false;
            }
            $this->di['mongodb']->$db->login->updateOne(
                ['_id' => $_id],
                [
                    '$set' => ['password' => password_hash($newPass, PASSWORD_DEFAULT)]
                ]
            );
            return true;
        }

        return false;
    }


    /**
     * 获取账号
     * @param null $id
     * @param array $keys
     * @return bool|array
     */
    public function getAccountById($id = null, $keys = [])
    {
        if (!is_object($id)) {
            $id = new ObjectId($id);
        }

        // 返回字段
        $keyReturn = [];
        if ($keys) {
            foreach ($keys as $v) {
                $keyReturn[$v] = 1;
            }
        }

        $db = $this->di['config']->mongodb->db;
        if (!($result = $this->di['mongodb']->$db->accounts->findOne(['_id' => $id], ['projection' => $keyReturn]))) {
            return false;
        }
        return $result;
    }


    /**
     * 修改账号信息
     * @param null $uid
     * @param array $data
     * @return bool
     */
    public function setAccount($uid = null, $data = [])
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;
        $mongodb->$db->accounts->updateOne(
            ['_id' => new ObjectId($uid)],
            ['$set' => $data]
        );
        return true;
    }

}