<?php

namespace MyApp\V1\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;
use MongoDB\BSON\ObjectId;

class Collection extends Model
{

    public function addCollection($type = '', $id = '', $uid = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        if ($type == 'post') {
            $data = $mongodb->$db->posts->findOne(
                ['_id' => new ObjectId($id)],
                [
                    'projection' => [
                        '_id'     => 0,
                        'uid'     => 1,
                        'content' => 1,
                        'picture' => 1,
                        'voice'   => 1,
                        'video'   => 1,
                    ]
                ]
            );
        }
        if (!$data) {
            return false;
        }
        $data['id'] = $id;

        return $mongodb->$db->collection->updateOne(
            ['_id' => new ObjectId($uid)],
            [
                '$addToSet'    => [$type => $data],
                '$currentDate' => ['modifyTime' => true],
            ],
            ['upsert' => true]
        );
    }


    public function deleteCollection($type = '', $id = '', $uid = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        return $mongodb->$db->collection->updateOne(
            ['_id' => new ObjectId($uid)],
            [
                '$pull'        => [$type => ['id' => $id]],
                '$currentDate' => ['modifyTime' => true],
            ]
        );
    }


    // TODO :: 分页
    public function listCollection($type = '', $uid = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        $data = $mongodb->$db->collection->findOne(
            ['_id' => new ObjectId($uid)],
            [
                'projection' => [$type => 1, '_id' => 0],
            ]
        );
        return $data;
    }

}