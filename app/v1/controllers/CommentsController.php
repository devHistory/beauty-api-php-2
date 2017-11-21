<?php


namespace MyApp\V1\Controllers;


use MyApp\V1\Models\Comments;

class CommentsController extends ControllerBase
{

    private $commentsModel;


    public function initialize()
    {
        parent::initialize();
        $this->commentsModel = new Comments();
    }


    // 发表
    public function createAction()
    {
        $postId = $this->request->get('pid', 'alphanum');
        $content = $this->request->get('content', 'string');
        if (!$postId || !$content) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('MIS_ARGV')])->send();
        }

        $this->commentsModel->create($postId, $content, $this->uid);

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('SUCCESS'),
        ])->send();
    }


    // 回复
    public function replyAction()
    {
    }


    // 删除
    public function deleteAction()
    {
        if (!$this->dispatcher->getParams()) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('MIS_ARGV')])->send();
        }
        $commentId = $this->dispatcher->getParams()['0'];

        if (!$this->commentsModel->deleteComment($commentId, $this->uid)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('FAI_DELETE')])->send();
        }

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('SUCCESS'),
        ])->send();
    }

}