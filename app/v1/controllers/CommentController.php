<?php


namespace MyApp\V1\Controllers;


use MyApp\V1\Models\Comment;

class CommentController extends ControllerBase
{

    private $commentModel;


    public function initialize()
    {
        parent::initialize();
        $this->commentModel = new Comment();
    }


    // 发表
    public function indexAction()
    {
        $postId = $this->request->get('postId', 'alphanum');
        $content = $this->request->get('content', 'string');
        if (!$postId || !$content) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }

        $this->commentModel->postComment($postId, $content, $this->uid);

        return $this->response->setJsonContent([
            'code' => 0,
            'msg' => _('success'),
        ])->send();
    }


    // 回复
    public function replyAction()
    {
    }


    // 删除
    public function deleteAction()
    {
    }

}