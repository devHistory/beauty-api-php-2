<?php

namespace MyApp\V1\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;

class Relation extends Model
{


    // 添加好友
    public function addFriend($friendId = '', $uid = '')
    {
        $key = 'friends|' . $uid;
        $this->di['redis']->sAdd($key, $friendId);

        // delete from cache
        $this->di['cache']->del('_' . $key);

        return true;
    }


    // 删除好友
    public function deleteFriend($friendId = '', $uid = '')
    {
        $key = 'friends|' . $uid;
        $this->di['redis']->sRem($key, $friendId);

        // delete from cache
        $this->di['cache']->del('_' . $key);

        return true;
    }


    // 好友列表
    public function listFriends($uid = '')
    {
        $key = 'friends|' . $uid;

        // get from cache
        $data = $this->di['cache']->get('_' . $key);
        if ($data) {
            return json_decode($data, true);
        }

        $result = $this->di['redis']->sMembers($key);
        $data = $this->di['component']->fillUserFromCache($result, ['name', 'level', 'desc']);
        $this->di['cache']->set('_' . $key, json_encode($data), 86400 * 1);
        return $data;
    }


    /**
     * @param string $following 关注对象
     * @param string $uid 粉丝
     * @return bool
     */
    public function follow($following = '', $uid = '')
    {
        $key1 = 'followers|' . $following;
        $this->di['redis']->sAdd($key1, $uid);

        $key2 = 'following|' . $uid;
        $this->di['redis']->sAdd($key2, $following);

        // delete from cache
        $this->di['cache']->del('_' . $key1);
        $this->di['cache']->del('_' . $key2);

        return true;
    }


    /**
     * @param string $following 关注对象
     * @param string $uid 粉丝
     * @return bool
     */
    public function unFollow($following = '', $uid = '')
    {
        $key1 = 'followers|' . $following;
        $this->di['redis']->sRem($key1, $uid);

        $key2 = 'following|' . $uid;
        $this->di['redis']->sRem($key2, $following);

        // delete from cache
        $this->di['cache']->del('_' . $key1);
        $this->di['cache']->del('_' . $key2);

        return true;
    }


    // 粉丝列表
    public function listFollowers($uid = '')
    {
        $key = 'followers|' . $uid;

        // get from cache
        $data = $this->di['cache']->get('_' . $key);
        if ($data) {
            return json_decode($data, true);
        }

        $result = $this->di['redis']->sMembers($key);
        $data = $this->di['component']->fillUserFromCache($result, ['name', 'level', 'desc']);
        $this->di['cache']->set('_' . $key, json_encode($data), 86400 * 1);
        return $data;
    }


    // 关注列表
    public function listFollowing($uid = '')
    {
        $key = 'following|' . $uid;

        // get from cache
        $data = $this->di['cache']->get('_' . $key);
        if ($data) {
            return json_decode($data, true);
        }

        $result = $this->di['redis']->sMembers($key);
        $data = $this->di['component']->fillUserFromCache($result, ['name', 'level', 'desc']);
        $this->di['cache']->set('_' . $key, json_encode($data), 86400 * 1);
        return $data;
    }

}