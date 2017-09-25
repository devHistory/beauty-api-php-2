<?php

namespace MyApp\V1\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Relation extends Model
{


    /**
     * @param string $following 关注对象
     * @param string $uid 粉丝
     * @return bool
     */
    public function follow($following = '', $uid = '')
    {
        $key = 'followers|' . $following;
        $this->di['redis']->sAdd($key, $uid);

        $key = 'following|' . $uid;
        $this->di['redis']->sAdd($key, $following);

        return true;
    }


    /**
     * @param string $following 关注对象
     * @param string $uid 粉丝
     * @return bool
     */
    public function unFollow($following = '', $uid = '')
    {
        $key = 'followers|' . $following;
        $this->di['redis']->sRem($key, $uid);

        $key = 'following|' . $uid;
        $this->di['redis']->sRem($key, $following);

        return true;
    }


    // 粉丝列表
    public function listFollowers($uid = '')
    {
        $key = 'followers|' . $uid;
        $result = $this->di['redis']->sMembers($key);
        return $this->getMoreInfo($result);
    }


    // 关注列表
    public function listFollowing($uid = '')
    {
        $key = 'following|' . $uid;
        $result = $this->di['redis']->sMembers($key);
        return $this->getMoreInfo($result);
    }


    private function getMoreInfo($uidList = [])
    {
        if (!$uidList) {
            return [];
        }

        $accountModel = new Account();
        $list = [];
        foreach ($uidList as $uid) {
            if (!$account = $accountModel->_getAccountDataFromCache($uid)) {
                continue;
            }
            $list[] = [
                'uid'  => $uid,
                'name' => $account['name'],
                'desc' => $account['desc'],
            ];
        }
        return $list;
    }

}