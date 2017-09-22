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


    /**
     * 发表
     * $type = ['text', 'picture', 'voice', 'video'];
     * @return mixed
     */
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

        return $this->response->setJsonContent(['code' => 0, 'msg' => _('success'), 'data' => $result])->send();
    }


    // 查看
    public function viewAction()
    {
    }


    // 删除
    public function deleteAction()
    {
    }

}