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


    // 列表
    protected function get()
    {
        $type = $this->request->get('type', 'alphanum');

        if (!in_array($type, ['posts'])) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_ARGV')])->send();
        }

        $data = $this->collectionModel->get($type, $this->uid);
        $data = $this->component->fillUserByKey($data[$type], 'uid', ['name']);

        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('SUCCESS'),
            'data' => [
                'count' => count($data),
                'list'  => $data
            ]
        ])->send();
    }


    // 收藏
    protected function create()
    {
        $id = $this->request->get('id', 'alphanum');
        $type = $this->request->get('type', 'alphanum');

        // check
        if (!$id || !$type || !in_array($type, ['posts'])) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_ARGV')])->send();
        }

        // add
        if (!$this->collectionModel->create($type, $id, $this->uid)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('FAI')])->send();
        }

        // return
        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('SUCCESS'),
        ])->send();
    }


    // 取消收藏
    protected function delete()
    {
        $id = $this->dispatcher->getParam('argv');
        $type = $this->request->getPut('type', 'alphanum');

        // check
        if (!$id || !$type || !in_array($type, ['posts'])) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('ERR_ARGV')])->send();
        }

        // delete
        if (!$this->collectionModel->delete($type, $id, $this->uid)) {
            return $this->response->setJsonContent(['code' => 1, 'msg' => _('FAIL')])->send();
        }

        // return
        return $this->response->setJsonContent([
            'code' => 0,
            'msg'  => _('SUCCESS'),
        ])->send();
    }

}