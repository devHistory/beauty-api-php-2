<?php


namespace MyApp\V1\Controllers;


use MyApp\V1\Models\Collection;

class CollectionController extends ControllerBase
{

    private $collectionModel;


    public function initialize()
    {
        parent::initialize();
        $this->collectionModel = new Collection();
    }


    public function indexAction()
    {
        $type = $this->request->get('type', 'alphanum');

        if (!in_array($type, ['post'])) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }

        $data = $this->collectionModel->listCollection($type, $this->uid);
        $data = $this->component->fillUserByKey($data[$type], 'uid', ['name']);

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success'),
            'data' => [
                'count' => count($data),
                'list'  => $data
            ]
        ])->send();
    }


    // 收藏
    public function addAction()
    {
        $id = $this->request->get('id', 'alphanum');
        $type = $this->request->get('type', 'alphanum');

        // check
        if (!$id || !$type || !in_array($type, ['post'])) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }

        // add
        if (!$this->collectionModel->addCollection($type, $id, $this->uid)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('fail')])->send();
        }

        // return
        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success'),
        ])->send();
    }


    // 取消收藏
    public function deleteAction()
    {
        $id = $this->request->get('id', 'alphanum');
        $type = $this->request->get('type', 'alphanum');

        // check
        if (!$id || !$type || !in_array($type, ['post'])) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('parameter error')])->send();
        }

        // delete
        if (!$this->collectionModel->deleteCollection($type, $id, $this->uid)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('fail')])->send();
        }

        // return
        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('success'),
        ])->send();
    }

}