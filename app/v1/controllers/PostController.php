<?php


namespace MyApp\V1\Controllers;


use MyApp\V1\Models\Post;

class PostController extends ControllerBase
{

    private $postModel;


    public function initialize()
    {
        parent::initialize();
        $this->postModel = new Post();
    }


    // 发表
    public function indexAction()
    {
        $type = $this->request->get('type', 'alphanum', 'text');
        $content = $this->request->get('content', 'string', '');
        $file = $this->request->get('file');
        $location = $this->request->get('location', 'string', '');
        $locationShow = (int)$this->request->get('locationShow');
        $nobody = (int)$this->request->get('nobody');

        // check
        if ($type == 'text' && (!$content || $file)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }
        if ($type != 'text' && !$file) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }
        if (!in_array($type, ['text', 'picture', 'voice', 'video'])) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }

        // attach
        $attach = [];
        if ($location) {
            $attach['location'] = $location;
            $attach['locationShow'] = $locationShow ? 1 : 0;
        }
        if ($file) {
            $attach += [$type => $file];
        }
        if ($nobody) {
            $attach += ['nobody' => 1];
        }

        // post
        if (!$result = $this->postModel->post($this->uid, $content, $attach)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('post error')])->send();
        }

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success'),
            'data' => $result
        ])->send();
    }


    // 查看
    public function viewAction()
    {
        $postId = $this->request->get('postId', 'alphanum');
        if (!$postId) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }

        // get data
        if (!$post = $this->postModel->getPost($postId)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('no data')])->send();
        }

        // add viewer
        if ($post['uid'] != $this->uid) {
            $this->postModel->addViewer($postId, $this->uid);
        }

        // 合并数据
        $data = $this->component->fillUserFromCache($post->uid, ['name', 'gender', 'level', 'avatar']);
        $data['postId'] = $postId;
        foreach ($post as $k => $info) {
            if (isset($data[$k])) {
                continue;
            }
            $data[$k] = $info;
        }
        unset($post, $data['_id']);

        // 匿名隐藏
        if (!empty($data['nobody'])) {
            $data['uid'] = '';
            $data['name'] = '匿名用户';
            $data['avatar'] = '';
        }

        // 评论列表
        if (isset($data['commentList'])) {
            $data['commentList'] = $this->component->fillUserByKey(
                $data['commentList'], 'uid', ['name', 'gender', 'level', 'avatar']
            );
        }
        // 查看列表
        if (isset($data['viewList'])) {
            $data['viewList'] = $this->component->fillUserFromCache(
                $data['viewList'], ['name', 'gender', 'level', 'avatar']
            );
        }
        // 返回
        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success'),
            'data' => $data
        ])->send();
    }


    // 删除
    public function deleteAction()
    {
        $postId = $this->request->get('postId', 'alphanum');
        if (!$postId) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }

        if (!$this->postModel->deletePost($this->uid, $postId)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('fail')])->send();
        }

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success')
        ])->send();
    }

}