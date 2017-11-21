<?php

namespace MyApp\V1\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;
use MongoDB\BSON\ObjectId;

class Comments extends Model
{

    public function create($postId = '', $content = '', $uid = '')
    {
        if (!$postId || !$content || !$uid) {
            return false;
        }

        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;
        $mongodb->$db->posts->updateOne(
            ['_id' => new ObjectId($postId)],
            [
                '$inc'  => ['commentNum' => 1],
                '$push' => [
                    'commentList' => [
                        'cid'     => $this->createComment($postId, $content, $uid),
                        'uid'     => $uid,
                        'content' => $content,
                        'create'  => time(),
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
        $mongodb->$db->comments->insertOne([
            '_id'     => $id,
            'uid'     => $uid,
            'pid'     => $postId,
            'content' => $content
        ]);
        return $id->__toString();
    }


    public function deleteComment($commentId = '', $uid = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        // find comment
        $comment = $mongodb->$db->comments->findOne([
            '_id' => new ObjectId($commentId)
        ]);

        // check
        if (!$comment) {
            return false;
        }
        if ($comment->uid != $uid) {
            return false;
        }

        // update post
        $mongodb->$db->posts->updateOne(
            ['_id' => new ObjectId($comment->pid)],
            [
                '$inc'  => ['commentNum' => -1],
                '$pull' => ['commentList' => ['cid' => $commentId]]
            ]
        );

        // delete comment
        $mongodb->$db->comments->deleteOne(['_id' => new ObjectId($commentId)]);

        return true;
    }

}