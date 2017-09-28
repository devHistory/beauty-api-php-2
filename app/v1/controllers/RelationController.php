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


    // 添加好友
    public function addAction()
    {
        $uid = $this->request->get('uid', 'alphanum', '');
        $msg = $this->request->get('msg', 'alphanum', '');
        if (!$uid || ($uid == $this->uid)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('fail')])->send();
        }

        $this->relationModel->addFriend($uid, $this->uid);

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success')
        ])->send();
    }


    // 删除好友
    public function deleteAction()
    {
        $uid = $this->request->get('uid', 'alphanum', '');
        if (!$uid) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('fail')])->send();
        }

        $this->relationModel->deleteFriend($uid, $this->uid);

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success')
        ])->send();
    }


    // 好友列表
    public function friendsAction()
    {
        $data = $this->relationModel->listFriends($this->uid);
        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success'),
            'data' => [
                'count' => count($data),
                'list'  => $data
            ]
        ])->send();
    }


    // 关注
    public function followAction()
    {
        $uid = $this->request->get('uid', 'alphanum', '');
        if (!$uid || ($uid == $this->uid)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('fail')])->send();
        }

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
        if (!$uid) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('fail')])->send();
        }

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