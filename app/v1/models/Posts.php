<?php

namespace MyApp\V1\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;
use MongoDB\BSON\ObjectId;

class Posts extends Model
{

    // 获取
    public function getPost($postId = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        return $mongodb->$db->posts->findOne([
            '_id' => new ObjectId($postId)
        ]);
    }


    // 发表
    public function create($uid = '', $content = '', $attach = [])
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
            $mongodb->$db->posts->insertOne($postData);

            // 非匿名内容则推送
            if (empty($postData['anonymous'])) {
                $this->pushToTimeline('add', $uid, $postData);
                $this->pushToFeed('add', $uid, $id->__toString());
            }
        } catch (\Exception $e) {
            return false;
        }

        return $id->__toString();
    }


    // TODO :: trash软删除
    public function delete($uid = '', $postId = '')
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

        // 删评论
        if (isset($post->comment)) {
            foreach ($post->comment as $cmt) {
                $mongodb->$db->comment->deleteOne(['_id' => new ObjectId($cmt->cmtId)]);
            }
        }

        // 删主题
        $mongodb->$db->posts->deleteOne([
            '_id' => new ObjectId($postId)
        ]);

        // push
        $this->pushToTimeline('delete', $uid, $postId);
        $this->pushToFeed('delete', $uid, $postId);

        return true;
    }


    public function addViewer($postId = '', $uid = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;
        $id = new ObjectId($postId);

        // update mongodb
        $mongodb->$db->posts->updateOne(
            ['_id' => $id],
            [
                '$inc'      => ['view' => 1],
                '$addToSet' => ['viewList' => $uid]
            ]
        );
        $mongodb->$db->posts->updateOne(
            ['_id' => $id],
            [
                '$push' => ['viewList' => ['$each' => [], '$slice' => -20]]
            ]
        );

        // rank
        $this->di['component']->rank('rankPostView', $postId);
    }


    /**
     * 操作 TimeLine
     * @param string $do = add | delete
     * @param string $uid
     * @param array|string $postData
     * @return mixed
     */
    private function pushToTimeline($do = '', $uid = '', $postData = [])
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        switch ($do) {
            case 'add':
                $pushData['postId'] = $postData['_id']->__toString();
                unset($postData['_id'], $postData['uid']);
                $pushData += $postData;
                return $mongodb->$db->timeline->updateOne(
                    ['_id' => new ObjectId($uid)],
                    [
                        '$push'        => ['post' => $pushData],
                        '$currentDate' => ['modifyTime' => true],
                    ],
                    ['upsert' => true]
                );

            case 'delete':
                return $mongodb->$db->timeline->updateOne(
                    ['_id' => new ObjectId($uid)],
                    [
                        '$pull'        => ['post' => ['postId' => $postData]],
                        '$currentDate' => ['modifyTime' => true],
                    ]
                );
        }
    }


    /**
     * @param string $do = add | delete
     * @param string $uid
     * @param string $postId
     */
    private function pushToFeed($do = '', $uid = '', $postId = '')
    {
        $this->di->get('component')->mq('feed', [
            'method' => $do,
            'uid'    => $uid,
            'postId' => $postId,
        ]);
    }


}