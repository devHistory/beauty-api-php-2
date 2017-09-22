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
    public function deletePost($postId = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        return $mongodb->$db->post->deleteOne([
            '_id' => new ObjectId($postId)
        ]);
    }


    public function post($uid = '', $text = '', $attach = [])
    {
        // check
        if (!$uid || !in_array(array_keys($attach)['0'], ['text', 'picture', 'voice', 'video'])) {
            return false;
        }

        // insert into database
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;
        $id = new ObjectId();
        try {
            $postData = array_filter([
                    '_id'  => $id,
                    'uid'  => $uid,
                    'text' => $text,
                ] + $attach);
            $mongodb->$db->post->insertOne($postData);

            // push to timeline
            $this->pushToTimeline($uid, $postData);

            // TODO :: push to feed queue

        } catch (\Exception $e) {
            return false;
        }

        return $id->__toString();
    }


    public function pushToTimeline($uid = '', $postData = [])
    {
        $postId = $postData['_id']->__toString();
        unset($postData['_id'], $postData['uid']);

        // insert into database
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;
        return $mongodb->$db->timeline->updateOne(
            ['_id' => new ObjectId($uid)],
            [
                '$set'         => ['post.' . $postId => $postData],
                '$currentDate' => ['lastModified' => true],
            ],
            ['upsert' => true]
        );
    }


    public function pushToFeedQueue($uid = '', $postId = '')
    {
    }

}