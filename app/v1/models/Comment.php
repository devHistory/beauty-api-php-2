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
                '$inc'  => ['comment' => 1],
                '$push' => [
                    'commentList' => [
                        'cmtId'      => $this->createComment($postId, $content, $uid),
                        'uid'        => $uid,
                        'content'    => $content,
                        'createTime' => time(),
                    ]
                ],
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
            '_id'     => $id,
            'uid'     => $uid,
            'postId'  => $postId,
            'content' => $content
        ]);
        return $id->__toString();
    }


    public function deleteComment($cmtId = '', $uid = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        // find comment
        $comment = $mongodb->$db->comment->findOne([
            '_id' => new ObjectId($cmtId)
        ]);

        // check
        if (!$comment) {
            return false;
        }
        if ($comment->uid != $uid) {
            return false;
        }

        // update post
        $mongodb->$db->post->updateOne(
            ['_id' => new ObjectId($comment->postId)],
            [
                '$inc'  => ['comment' => -1],
                '$pull' => ['commentList' => ['cmtId' => $cmtId]]
            ]
        );

        // delete comment
        $mongodb->$db->comment->deleteOne(['_id' => new ObjectId($cmtId)]);

        return true;
    }

}