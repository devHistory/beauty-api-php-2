<?php


namespace MyApp\V1\Controllers;


use MyApp\V1\Models\Post;

class PostsController extends ControllerBase
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
        $files = $this->request->get('files');
        $locale = $this->request->get('locale', 'string', '');
        $hidden = (int)$this->request->get('hidden');
        $anonymous = (int)$this->request->get('anonymous');

        // 检查
        if ($type == 'text' && !$content) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_ARGV')])->send();
        }
        if ($type != 'text' && !$files) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_ARGV')])->send();
        }
        if (!in_array($type, ['text', 'picture', 'voice', 'video'])) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_ARGV')])->send();
        }

        // 属性
        $attach = [];
        if ($locale) {
            $attach['locale'] = $locale;
            $attach['hidden'] = $hidden ? 1 : 0;
        }
        if ($files && $type != 'text') {
            $attach += [$type => $files];
        }
        if ($anonymous) {
            $attach += ['anonymous' => 1];
        }


        // 发布
        if (!$postId = $this->postModel->post($this->uid, $content, $attach)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('FAI_POST')])->send();
        }

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('SUCCESS'),
            'data' => $postId
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