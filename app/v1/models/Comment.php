<?php

namespace MyApp\V1\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;
use MongoDB\BSON\ObjectId;

class Comment extends Model
{

    public function postComment($postId = '', $content = '', $uid = '')
    {
        if (!$postId || !$content || !$uid) {
            return false;
        }

        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;
        $mongodb->$db->post->updateOne(
            ['_id' => new ObjectId($postId)],
            [
                '$push' => ['comment' => [
                    'cmtId' => $this->createComment($postId, $content, $uid),
                    'uid' => $uid,
                    'content' => $content,
                    'createTime' => time(),
                ]],
            ]
        );
        return true;
    }


    public function createComment($postId = '', $content = '', $uid = '')
    {
        $id = new ObjectId();
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;
        $mongodb->$db->comment->insertOne([
            '_id' => $id,
            'uid' => $uid,
            'postId' => $postId,
            'content' => $content
        ]);
        return $id->__toString();
    }

}