<?php


namespace MyApp\V1\Controllers;


use MyApp\V1\Models\Posts;

class PostsController extends ControllerBase
{

    private $postsModel;


    public function initialize()
    {
        parent::initialize();
        $this->postsModel = new Posts();
    }


    // 查看
    public function indexAction()
    {
        $argv = $this->dispatcher->getParams();
        if (!$argv) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_URI')])->send();
        }
        $postId = $argv['0'];

        // 获取
        if (!$posts = $this->postsModel->get($postId)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_NO_DATA')])->send();
        }
        // 预览记录
        if ($posts['uid'] != $this->uid) {
            $this->postsModel->addViewer($postId, $this->uid);
        }

        // 合并用户数据
        $data = $this->component->fillUserFromCache($posts->uid, ['name', 'gender', 'level', 'avatar']);
        $data['postId'] = $postId;
        foreach ($posts as $k => $info) {
            if (isset($data[$k])) {
                continue;
            }
            $data[$k] = $info;
        }
        unset($posts, $data['_id']);

        // 匿名隐藏
        if (!empty($data['anonymous'])) {
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
            'msg'  => _('SUCCESS'),
            'data' => $data
        ])->send();
    }


    // 发表
    public function createAction()
    {
        $type = $this->request->getPost('type', 'alphanum', 'text');
        $content = $this->request->getPost('content', 'string', '');
        $files = $this->request->getPost('files');
        $locale = $this->request->getPost('locale', 'string', '');
        $hidden = (int)$this->request->getPost('hidden');
        $anonymous = (int)$this->request->getPost('anonymous');

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
        if (!$postId = $this->postsModel->create($this->uid, $content, $attach)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('FAI_POST')])->send();
        }

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('SUCCESS'),
            'data' => $postId
        ])->send();
    }


    // 更新
    public function updateAction()
    {
    }


    // 删除
    public function deleteAction()
    {
        if (!$this->dispatcher->getParams()) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_ARGV')])->send();
        }
        $postId = $this->dispatcher->getParams()['0'];


        if (!$this->postsModel->delete($this->uid, $postId)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('FAI_DELETE')])->send();
        }

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('SUCCESS')
        ])->send();
    }

}