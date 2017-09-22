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
        $text = $this->request->get('text', 'string', '');
        $file = $this->request->get('file');

        // check
        if ($type == 'text' && (!$text || $file)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }
        if ($type != 'text' && !$file) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }

        // post
        if (!$result = $this->postModel->post($this->uid, $text, $file ? [$type => $file] : null)) {
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
        if (!$data = $this->postModel->getPost($postId)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('no data')])->send();
        }

        // add viewer
        $this->postModel->addView($postId, $this->uid);

        // return
        unset($data->_id);
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