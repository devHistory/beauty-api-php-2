<?php

namespace MyApp\V1\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;
use MongoDB\BSON\ObjectId;

class Post extends Model
{

    public function getPost($postId = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        return $mongodb->$db->post->findOne([
            '_id' => new ObjectId($postId)
        ]);
    }


    // TODO :: 删除附件
    public function deletePost($uid = '', $postId = '')
    {
        if (!$post = $this->getPost($postId)) {
            return false;
        }

        // 检查权限
        if ($post->uid != $uid) {
            return false;
        }

        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        $mongodb->$db->post->deleteOne([
            '_id' => new ObjectId($postId)
        ]);

        // push
        $this->pushToTimeLineDelete($uid, $postId);
        $this->pushToFeedDelete($uid, $postId);

        return true;
    }


    public function post($uid = '', $content = '', $attach = [])
    {
        if (!$uid) {
            return false;
        }

        // insert into database
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;
        $id = new ObjectId();
        try {
            $postData = [
                '_id'     => $id,
                'uid'     => $uid,
                'content' => $content,
            ];
            $postData = $postData + $attach;
            $mongodb->$db->post->insertOne($postData);

            // push
            $this->pushToTimeLineAdd($uid, $postData);
            $this->pushToFeedAdd($uid, $id->__toString());

        } catch (\Exception $e) {
            return false;
        }

        return $id->__toString();
    }


    public function addView($postId = '', $uid = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;
        $mongodb->$db->post->updateOne(
            ['_id' => new ObjectId($postId)],
            [
                '$inc'      => ['view' => 1],
                '$addToSet' => ['viewList' => $uid]
            ]
        );
    }


    private function pushToTimeLineAdd($uid = '', $postData = [])
    {
        $insertData['postId'] = $postData['_id']->__toString();
        unset($postData['_id'], $postData['uid']);
        $insertData += $postData;

        // insert into database
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;
        return $mongodb->$db->timeLine->updateOne(
            ['_id' => new ObjectId($uid)],
            [
                '$push'        => ['post' => $insertData],
                '$currentDate' => ['lastModified' => true],
            ],
            ['upsert' => true]
        );
    }


    private function pushToTimeLineDelete($uid = '', $postId = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;
        return $mongodb->$db->timeLine->updateOne(
            ['_id' => new ObjectId($uid)],
            [
                '$pull'        => ['post' => ['postId' => $postId]],
                '$currentDate' => ['lastModified' => true],
            ]
        );
    }


    private function pushToFeedAdd($uid = '', $postId = '')
    {
    }


    private function pushToFeedDelete($uid, $postId)
    {
    }

}