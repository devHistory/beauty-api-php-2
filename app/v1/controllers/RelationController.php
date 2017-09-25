<?php


namespace MyApp\V1\Controllers;


use MyApp\V1\Models\Relation;

class RelationController extends ControllerBase
{

    private $relationModel;


    public function initialize()
    {
        parent::initialize();
        $this->relationModel = new Relation();
    }


    // 关注
    public function followAction()
    {
        $uid = $this->request->get('uid', 'alphanum', '');

        $this->relationModel->follow($uid, $this->uid);

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success')
        ])->send();
    }


    // 取消关注
    public function unfollowAction()
    {
        $uid = $this->request->get('uid', 'alphanum', '');

        $this->relationModel->unfollow($uid, $this->uid);

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success')
        ])->send();
    }


    // 粉丝列表
    public function followersAction()
    {
        $data = $this->relationModel->listFollowers($this->uid);
        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success'),
            'data' => [
                'count' => count($data),
                'list'  => $data
            ]
        ])->send();
    }


    // 关注列表
    public function followingAction()
    {
        $data = $this->relationModel->listFollowing($this->uid);
        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success'),
            'data' => [
                'count' => count($data),
                'list'  => $data
            ]
        ])->send();
    }

}