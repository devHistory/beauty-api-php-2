<?php

namespace MyApp\V1\Models;


use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Db;
use MongoDB\BSON\ObjectId;

class Favorites extends Model
{

    public function create($type = '', $id = '', $uid = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        if ($type == 'posts') {
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
        $insertData = ['id' => $id] + (array)$data;

        return $mongodb->$db->favorites->updateOne(
            ['_id' => new ObjectId($uid)],
            [
                '$addToSet'    => [$type => $insertData],
                '$currentDate' => ['modify' => true],
            ],
            ['upsert' => true]
        );
    }


    public function delete($type = '', $id = '', $uid = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        return $mongodb->$db->favorites->updateOne(
            ['_id' => new ObjectId($uid)],
            [
                '$pull'        => [$type => ['id' => $id]],
                '$currentDate' => ['modify' => true],
            ]
        );
    }


    // TODO :: 分页
    public function get($type = '', $uid = '')
    {
        $mongodb = $this->di['mongodb'];
        $db = $this->di['config']->mongodb->db;

        $data = $mongodb->$db->favorites->findOne(
            ['_id' => new ObjectId($uid)],
            [
                'projection' => [$type => 1, '_id' => 0],
            ]
        );
        return $data;
    }

}